<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Brand;
use App\Catalog\Domain\Entity\Product;
use App\Storefront\Application\HomePage\Interface\HomePageCatalogQueryInterface;
use App\Storefront\Application\HomePage\Output\HomePageBrandView;
use App\Storefront\Application\HomePage\Output\HomePageProductView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class HomePageCatalogRepository implements HomePageCatalogQueryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findNewProducts(int $limit): array
    {
        return $this->findProducts($limit, 'product.isNew = true');
    }

    public function findHitProducts(int $limit): array
    {
        return $this->findProducts($limit, 'product.isHit = true');
    }

    public function findPopularBrands(int $limit): array
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Homepage result limits cannot be negative.');
        }

        if (0 === $limit) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select('brand.title AS title', 'brand.image AS image')
            ->from(Brand::class, 'brand')
            ->where('brand.is_active = true')
            ->orderBy('brand.pos', \SortDirection::Ascending)
            ->addOrderBy('brand.title', \SortDirection::Ascending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): HomePageBrandView => new HomePageBrandView(
                title: (string) $row['title'],
                image: null === $row['image'] ? null : (string) $row['image'],
            ),
            $rows,
        );
    }

    /** @return list<HomePageProductView> */
    private function findProducts(int $limit, string $flagCondition): array
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Homepage result limits cannot be negative.');
        }

        if (0 === $limit) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
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
            ->from(Product::class, 'product')
            ->join('product.brand', 'brand')
            ->where('product.isActive = true')
            ->andWhere($flagCondition)
            ->orderBy('product.pos', \SortDirection::Ascending)
            ->addOrderBy('product.title', \SortDirection::Ascending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): HomePageProductView => new HomePageProductView(
                title: (string) $row['title'],
                brandTitle: (string) $row['brandTitle'],
                image: null === $row['image'] ? null : (string) $row['image'],
                price: self::formatPrice((int) $row['priceMinorUnits'], (string) $row['currencyCode']),
                oldPrice: null === $row['oldPriceMinorUnits']
                    ? null
                    : self::formatPrice((int) $row['oldPriceMinorUnits'], (string) $row['currencyCode']),
                isNew: (bool) $row['isNew'],
                isHit: (bool) $row['isHit'],
            ),
            $rows,
        );
    }

    private static function formatPrice(int $minorUnits, string $currencyCode): string
    {
        return sprintf('%d.%02d %s', intdiv($minorUnits, 100), $minorUnits % 100, $currencyCode);
    }
}
