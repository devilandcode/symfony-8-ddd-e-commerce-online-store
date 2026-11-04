<?php

namespace App\Customer\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{

    public function getLoginPage()
    {
        return $this->render('auth/login.html.twig');
    }
}
