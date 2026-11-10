<?php

namespace App\Customer\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->renderLoginForm($authenticationUtils, 'app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('The logout route is handled by the security firewall.');
    }

    private function renderLoginForm(AuthenticationUtils $authenticationUtils, string $loginRoute): Response
    {
        return $this->render('backoffice/auth/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'login_route' => $loginRoute,
        ]);
    }
}
