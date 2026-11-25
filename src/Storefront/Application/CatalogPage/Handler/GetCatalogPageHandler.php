<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Handler;

use App\Catalog\Application\CatalogBrowse\CatalogBrowseQueryInterface;
use App\Catalog\Application\CatalogBrowse\Input\CatalogBrowseCriteria;
use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Output\ViewCatalogPageDTO;

final readonly class GetCatalogPageHandler
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private FindCategoryBySlugHandler $findCategoryBySlug,
        private FindChildCategoryHandler $findChildCategory,
        private ListContentSettingHandler $listContentSetting,
        private CatalogBrowseQueryInterface $catalogBrowse,
    ) {
    }

    public function __invoke(
        ?string $slug,
        ?string $parentSlug,
        CatalogPageFilters $filters,
        int $page,
    ): ViewCatalogPageDTO {
        if ($page < 1) {
            throw new \InvalidArgumentException('Catalog page numbers must be positive.');
        }

        if (null === $slug && null !== $parentSlug) {
            throw new \InvalidArgumentException('A parent category slug requires a category slug.');
        }

        if (null === $slug) {
            $pageView = ($this->findPageBySlug)(new Slug('catalog'));
            $categorySlug = null;
            $criteria = new CatalogBrowseCriteria(
                categoryIds: $filters->categoryIds,
                brandIds: $filters->brandIds,
            );
        } else {
            $category = new Slug($slug);
            $pageView = null === $parentSlug
                ? ($this->findCategoryBySlug)($category)
                : ($this->findChildCategory)(new Slug($parentSlug), $category);
            $categorySlug = $category->toString();
            $criteria = new CatalogBrowseCriteria(
                categorySlug: $categorySlug,
                brandIds: $filters->brandIds,
                characteristicValues: $filters->characteristicValues,
            );
        }

        return new ViewCatalogPageDTO(
            page: $pageView,
            categorySlug: $categorySlug,
            categories: $this->catalogBrowse->findActiveCategories(),
            brands: $this->catalogBrowse->findActiveBrands($categorySlug),
            characteristics: null === $categorySlug
                ? []
                : $this->catalogBrowse->findFilterableCharacteristics($categorySlug),
            productPage: $this->catalogBrowse->findProducts(
                $criteria,
                $page,
                ($this->listContentSetting)()->productsLimit(),
            ),
            filters: $filters,
        );
    }
}
