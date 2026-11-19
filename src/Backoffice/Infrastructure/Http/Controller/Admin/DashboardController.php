<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Controller\Admin\CategoryCrudController;
use App\Backoffice\Infrastructure\Http\Controller\Admin\CharacteristicCrudController;
use App\Backoffice\Infrastructure\Http\Controller\Admin\PageContentController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('backoffice/admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('HV-store')
            ->setFaviconPath('/assets/i/favicon.ico')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Content');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Categories', 'fa fa-folder')->setAction('index');
        yield MenuItem::linkTo(CharacteristicCrudController::class, 'Characteristics', 'fa fa-tags')->setAction('index');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file')->setAction('index');
        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(SettingsController::class, 'Settings', 'fa fa-cog');
        yield MenuItem::linkTo(SiteMenuCrudController::class, 'Site Menus', 'fa fa-bars')->setAction('index');
        yield MenuItem::linkTo(UserManagementController::class, 'Users', 'fa fa-users')->setAction('index');
    }
}
