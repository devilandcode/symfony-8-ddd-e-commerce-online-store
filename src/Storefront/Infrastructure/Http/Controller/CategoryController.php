<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Storefront\Application\CatalogPage\Handler\GetCategoryCatalogPageHandler;
use App\Storefront\Application\CatalogPage\Handler\GetRootCatalogPageHandler;
use App\Storefront\Infrastructure\Http\Request\CatalogPageQueryParameters;
use App\Storefront\Infrastructure\Http\Request\MapCatalogQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly GetRootCatalogPageHandler $getRootCatalogPage,
        private readonly GetCategoryCatalogPageHandler $getCategoryCatalogPage,
    ) {
    }

    #[Route('/catalog', name: 'catalog', methods: ['GET'])]
    public function getCatalog(#[MapCatalogQuery] CatalogPageQueryParameters $query = new CatalogPageQueryParameters()): Response
    {
        $catalog = ($this->getRootCatalogPage)($query->toFilters(), $query->toPageNumber());

        return $this->render('catalog.html.twig', compact('catalog'));
    }

    #[Route('/catalog/{slug}', name: 'level1', methods: ['GET'])]
    public function getLevel1(string $slug, #[MapCatalogQuery] CatalogPageQueryParameters $query = new CatalogPageQueryParameters()): Response
    {
        $catalog = ($this->getCategoryCatalogPage)(null, $slug, $query->toFilters(), $query->toPageNumber());

        return $this->render('catalog.html.twig', compact('catalog'));
    }

    #[Route('/catalog/{parentSlug}/{slug}', name: 'level2', methods: ['GET'])]
    public function getLevel2(string $parentSlug, string $slug, #[MapCatalogQuery] CatalogPageQueryParameters $query = new CatalogPageQueryParameters()): Response
    {
        $catalog = ($this->getCategoryCatalogPage)($parentSlug, $slug, $query->toFilters(), $query->toPageNumber());

        return $this->render('catalog.html.twig', compact('catalog'));
    }
}
