<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Storefront\Application\HomePage\GetHomePageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly GetHomePageHandler $getHomePage,
    ) {
    }

    #[Route('/', name: 'index')]
    public function getIndex(): Response
    {
        return $this->render('index.html.twig', [
            'homePage' => ($this->getHomePage)(),
        ]);
    }
}