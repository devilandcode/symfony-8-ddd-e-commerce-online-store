<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Storefront\Application\CatalogPage\Output\CatalogBrandView;
use App\Storefront\Application\CatalogPage\Output\CatalogCategoryView;
use App\Storefront\Application\CatalogPage\Output\CatalogCharacteristicFilterView;
use App\Storefront\Application\CatalogPage\Output\CatalogProductPage;
use App\Storefront\Application\CatalogPage\Output\CatalogProductView;
use App\Catalog\Domain\Entity\Brand;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Entity\CategoryCharacteristic;
use App\Catalog\Domain\Entity\Product;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Interface\CatalogPageQueryInterface;
use App\Storefront\Infrastructure\Money\PriceFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class CatalogPageQuery implements CatalogPageQueryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PriceFormatter $priceFormatter,
    ) {
    }

    public function findActiveCategories(): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'category.id AS id',
                'category.title AS title',
                'category.slug.value AS slug',
                'parent.slug.value AS parentSlug',
            )
            ->from(Category::class, 'category')
            ->leftJoin('category.parent', 'parent')
            ->where('category.is_active = true')
            ->andWhere('(category.parent IS NULL OR parent.is_active = true)')
            ->orderBy('category.level', \SortDirection::Ascending)
            ->addOrderBy('category.pos', \SortDirection::Ascending)
            ->addOrderBy('category.title', \SortDirection::Ascending)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): CatalogCategoryView => new CatalogCategoryView(
                id: (int) $row['id'],
                title: (string) $row['title'],
                slug: (string) $row['slug'],
                parentSlug: null === $row['parentSlug'] ? null : (string) $row['parentSlug'],
            ),
            $rows,
        );
    }

    public function findActiveBrands(?string $categorySlug): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('brand.id AS id', 'brand.title AS title', 'brand.pos AS position')
            ->from(Brand::class, 'brand')
            ->join('brand.products', 'product')
            ->where('brand.is_active = true')
            ->andWhere('product.isActive = true')
            ->distinct();

        if (null !== $categorySlug) {
            $queryBuilder
                ->join('product.category', 'category')
                ->andWhere('category.slug.value = :categorySlug')
                ->setParameter('categorySlug', $categorySlug);
        }

        $rows = $queryBuilder
            ->orderBy('brand.pos', \SortDirection::Ascending)
            ->addOrderBy('brand.title', \SortDirection::Ascending)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): CatalogBrandView => new CatalogBrandView(
                id: (int) $row['id'],
                title: (string) $row['title'],
            ),
            $rows,
        );
    }

    public function findFilterableCharacteristics(string $categorySlug): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->where('category.slug.value = :categorySlug')
            ->andWhere('category.is_active = true')
            ->andWhere('product.isActive = true')
            ->setParameter('categorySlug', $categorySlug);

        [, $productValueAlias, $characteristicAlias] = $this->joinFilterableCharacteristic($queryBuilder, 'Options');

        $rows = $queryBuilder
            ->select(
                $characteristicAlias.'.id AS id',
                $characteristicAlias.'.title AS title',
                $characteristicAlias.'.measure AS measure',
                $characteristicAlias.'.pos AS position',
                $productValueAlias.'.value AS value',
            )
            ->andWhere(sprintf("TRIM(%s.value) <> ''", $productValueAlias))
            ->distinct()
            ->orderBy($characteristicAlias.'.pos', \SortDirection::Ascending)
            ->addOrderBy($characteristicAlias.'.title', \SortDirection::Ascending)
            ->addOrderBy($productValueAlias.'.value', \SortDirection::Ascending)
            ->getQuery()
            ->getArrayResult();

        $characteristics = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $characteristics[$id] ??= [
                'title' => (string) $row['title'],
                'measure' => null === $row['measure'] ? null : (string) $row['measure'],
                'values' => [],
            ];
            $characteristics[$id]['values'][] = trim((string) $row['value']);
        }

        $views = [];
        foreach ($characteristics as $id => $characteristic) {
            $views[] = new CatalogCharacteristicFilterView(
                id: $id,
                title: $characteristic['title'],
                measure: $characteristic['measure'],
                values: array_values(array_unique($characteristic['values'])),
            );
        }

        return $views;
    }

    public function findProducts(
        ?string $categorySlug,
        CatalogPageFilters $filters,
        int $page,
        int $pageSize,
    ): CatalogProductPage {
        if ($page < 1 || $pageSize < 0) {
            throw new \InvalidArgumentException('Catalog pagination values are invalid.');
        }

        if (0 < $pageSize && $page - 1 > intdiv(PHP_INT_MAX, $pageSize)) {
            throw new \InvalidArgumentException('The requested catalog page is too large.');
        }

        $totalProducts = (int) $this->buildProductQuery($categorySlug, $filters)
            ->select('COUNT(DISTINCT product.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $products = [];
        if (0 < $pageSize) {
            $rows = $this->buildProductQuery($categorySlug, $filters)
                ->select(
                    'product.title AS title',
                    'brand.title AS brandTitle',
                    'product.image AS image',
                    'product.price.minorUnits AS priceMinorUnits',
                    'product.price.currencyCode AS currencyCode',
                    'product.oldPrice.minorUnits AS oldPriceMinorUnits',
                    'product.isNew AS isNew',
                    'product.isHit AS isHit',
                )
                ->orderBy('product.pos', \SortDirection::Ascending)
                ->addOrderBy('product.title', \SortDirection::Ascending)
                ->setFirstResult(($page - 1) * $pageSize)
                ->setMaxResults($pageSize)
                ->getQuery()
                ->getArrayResult();

            $products = array_map(
                fn (array $row): CatalogProductView => $this->mapProduct($row),
                $rows,
            );
        }

        $totalPages = 0 === $pageSize
            ? 0
            : intdiv($totalProducts, $pageSize) + (0 === $totalProducts % $pageSize ? 0 : 1);

        return new CatalogProductPage($products, $totalProducts, $page, $pageSize, $totalPages);
    }

    private function buildProductQuery(?string $categorySlug, CatalogPageFilters $filters): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(Product::class, 'product')
            ->join('product.brand', 'brand')
            ->join('product.category', 'category')
            ->where('product.isActive = true');

        if (null !== $categorySlug) {
            $queryBuilder
                ->andWhere('category.slug.value = :categorySlug')
                ->setParameter('categorySlug', $categorySlug);
        }

        if ([] !== $filters->categoryIds) {
            $queryBuilder
                ->andWhere('category.id IN (:categoryIds)')
                ->setParameter('categoryIds', $filters->categoryIds);
        }

        if ([] !== $filters->brandIds) {
            $queryBuilder
                ->andWhere('brand.id IN (:brandIds)')
                ->setParameter('brandIds', $filters->brandIds);
        }

        $index = 0;
        foreach ($filters->characteristicValues as $characteristicId => $values) {
            [, $productValueAlias] = $this->joinFilterableCharacteristic($queryBuilder, (string) $index);
            $queryBuilder
                ->andWhere($productValueAlias.'.characteristic = :filterCharacteristicId'.$index)
                ->andWhere($productValueAlias.'.value IN (:filterValues'.$index.')')
                ->setParameter('filterCharacteristicId'.$index, $characteristicId)
                ->setParameter('filterValues'.$index, $values);

            ++$index;
        }

        return $queryBuilder;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function joinFilterableCharacteristic(QueryBuilder $queryBuilder, string $suffix): array
    {
        $assignmentAlias = 'filterAssignment'.$suffix;
        $productValueAlias = 'filterValue'.$suffix;
        $characteristicAlias = 'filterCharacteristic'.$suffix;

        $queryBuilder
            ->join('product.characteristicAssignments', $productValueAlias)
            ->join($productValueAlias.'.characteristic', $characteristicAlias)
            ->join(
                CategoryCharacteristic::class,
                $assignmentAlias,
                'WITH',
                sprintf(
                    '%s.category = category AND %s.characteristic = %s',
                    $assignmentAlias,
                    $assignmentAlias,
                    $characteristicAlias,
                ),
            )
            ->andWhere($assignmentAlias.'.is_active = true')
            ->andWhere($assignmentAlias.'.in_filter = true')
            ->andWhere($characteristicAlias.'.is_active = true');

        return [$assignmentAlias, $productValueAlias, $characteristicAlias];
    }

    /** @param array<string, mixed> $row */
    private function mapProduct(array $row): CatalogProductView
    {
        $currencyCode = (string) $row['currencyCode'];
        return new CatalogProductView(
            title: (string) $row['title'],
            brandTitle: (string) $row['brandTitle'],
            image: null === $row['image'] ? null : (string) $row['image'],
            price: $this->priceFormatter->format((int) $row['priceMinorUnits'], $currencyCode),
            oldPrice: null === $row['oldPriceMinorUnits']
                ? null
                : $this->priceFormatter->format((int) $row['oldPriceMinorUnits'], $currencyCode),
            isNew: (bool) $row['isNew'],
            isHit: (bool) $row['isHit'],
        );
    }
}
