<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http;

use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly FindPageBySlugHandler $findPageBySlugHandler,
    ){}

    #[Route('/', name: 'index')]
    public function getIndex(): Response
    {
        try {
            $page = ($this->findPageBySlugHandler)(new Slug('home'));
        }catch (PageNotFound) {
            throw $this->createNotFoundException('Page not found.');
        }

        return $this->render('index.html.twig', compact('page'));
    }
} 