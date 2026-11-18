<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFoundException;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private FindCategoryBySlugHandler $findCategoryBySlug,
        private FindChildCategoryHandler $findChildCategory,
    ){}


    #[Route('/catalog', name: 'catalog', options: ['methods' => ['GET']])]
    public function getCatalog(): Response
    {
        $page = ($this->findPageBySlug)(new Slug('catalog'));

        return $this->render('catalog.html.twig', compact('page'));
    }

    #[Route('/catalog/{slug}', name: 'level1', options: ['methods' => ['GET']])]
    public function getLevel1(string $slug): Response
    {
        $page = ($this->findCategoryBySlug)(new Slug($slug));

        return $this->render('catalog.html.twig', compact('page'));
    }

    #[Route('/catalog/{parentSlug}/{slug}', name: 'level2', options: ['methods' => ['GET']])]
    public function getLevel2(string $parentSlug, string $slug): Response
    {
        $page = ($this->findChildCategory)(new Slug($parentSlug), new Slug($slug));

        return $this->render('catalog.html.twig', compact('page'));
    }
}
