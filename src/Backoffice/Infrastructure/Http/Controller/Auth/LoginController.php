<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{

    #[Route('/admin/login', name: 'admin_login')]
    public function adminLogin(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->renderLoginForm($authenticationUtils, 'admin_login');
    }
    
    #[Route('/admin/logout', name: 'admin_logout')]
    public function adminLogout(): never
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
