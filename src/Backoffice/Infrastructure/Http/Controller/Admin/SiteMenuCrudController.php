<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\ContentManagement\Application\SiteMenu\ListSiteMenus\ListSiteMenuHandler;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/site-menus', name: 'site_menus')]
class SiteMenuCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListSiteMenuHandler $listPages): Response
    {
        return $this->render('backoffice/admin/site_menus/index.html.twig', [
            'site_menus' => $listPages(),
        ]);
    }
}
