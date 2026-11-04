<?php

namespace App\Storefront\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function getIndex()
    {
        return $this->render('index.html.twig');
    }
}
