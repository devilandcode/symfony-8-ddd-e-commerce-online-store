<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http;

use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private FindCategoryBySlugHandler $findCategoryBySlug,
    ){}


    #[Route('/catalog', name: 'catalog', options: ['methods' => ['GET']])]
    public function getCatalog()
    {
        try {
            $page = ($this->findPageBySlug)(new Slug('catalog'));
        }catch (PageNotFound) {
            throw $this->createNotFoundException('Page not found.');
        }

        return $this->render('catalog.html.twig', compact('page'));
    }

    #[Route('/catalog/{slug}', name: 'level1', options: ['methods' => ['GET']])]
    public function getLevel1(string $slug)
    {
        $page = ($this->findCategoryBySlug)(new Slug($slug));

        dd($page);
    }
}
