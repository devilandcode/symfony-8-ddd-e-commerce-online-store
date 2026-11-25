<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Storefront\Application\CatalogPage\Handler\GetCatalogPageHandler;
use App\Storefront\Infrastructure\Http\Request\CatalogPageQueryParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(private readonly GetCatalogPageHandler $getCatalogPage)
    {
    }

    #[Route('/catalog', name: 'catalog', methods: ['GET'])]
    public function getCatalog(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CatalogPageQueryParameters $query = new CatalogPageQueryParameters(),
    ): Response
    {
        return $this->renderCatalog($query);
    }

    #[Route('/catalog/{slug}', name: 'level1', methods: ['GET'])]
    public function getLevel1(
        string $slug,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CatalogPageQueryParameters $query = new CatalogPageQueryParameters(),
    ): Response
    {
        return $this->renderCatalog($query, $slug);
    }

    #[Route('/catalog/{parentSlug}/{slug}', name: 'level2', methods: ['GET'])]
    public function getLevel2(
        string $parentSlug,
        string $slug,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CatalogPageQueryParameters $query = new CatalogPageQueryParameters(),
    ): Response
    {
        return $this->renderCatalog($query, $slug, $parentSlug);
    }

    private function renderCatalog(
        CatalogPageQueryParameters $query,
        ?string $slug = null,
        ?string $parentSlug = null,
    ): Response
    {
        $catalog = ($this->getCatalogPage)(
            $slug,
            $parentSlug,
            $query->toFilters(),
            $query->page,
        );

        return $this->render('catalog.html.twig', ['catalog' => $catalog]);
    }
}
