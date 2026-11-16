<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Category\CategoryFormData;
use App\Catalog\Application\Category\AdminUseCases\CreateCategory\CreateCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\ListCategory\ListCategoryHandler;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/categories', name: 'categories')]
final class CategoryCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListCategoryHandler $listCategories): Response
    {
        return $this->render('backoffice/admin/categories/index.html.twig', [
            'categories' => $listCategories(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateCategoryHandler $createCategory): Response
    {
        $data = new CategoryFormData();
        $form = $this->createForm(MenuType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createMenu($this->menuDataFromForm($data));
            } catch (MenuSlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderMenuForm($form, 'Create menu');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderMenuForm($form, 'Create menu');
            }

            $this->addFlash('success', 'Menu created.');

            return $this->redirectToRoute('admin_site_menus_index');
        }

        return $this->renderMenuForm($form, 'Create menu');
    }
}
