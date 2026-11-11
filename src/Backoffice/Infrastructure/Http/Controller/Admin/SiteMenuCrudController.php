<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\MenuFormData;
use App\Backoffice\Infrastructure\Http\Form\MenuType;
use App\ContentManagement\Application\SiteMenu\CreateMenu\CreateMenuHandler;
use App\ContentManagement\Application\SiteMenu\DeleteMenu\DeleteMenuHandler;
use App\ContentManagement\Application\SiteMenu\Exception\MenuNotFound;
use App\ContentManagement\Application\SiteMenu\Exception\MenuSlugAlreadyInUse;
use App\ContentManagement\Application\SiteMenu\FindMenu\FindMenuHandler;
use App\ContentManagement\Application\SiteMenu\Input\SiteMenuDataDTO;
use App\ContentManagement\Application\SiteMenu\ListMenu\ListMenuHandler;
use App\ContentManagement\Application\SiteMenu\Output\SiteMenuViewDTO;
use App\ContentManagement\Application\SiteMenu\UpdateMenu\UpdateMenuHandler;
use App\Shared\Domain\ValueObject\Slug;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/site-menus', name: 'site_menus')]
class SiteMenuCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListMenuHandler $listPages): Response
    {
        return $this->render('backoffice/admin/site_menus/index.html.twig', [
            'site_menus' => $listPages(),
        ]);
    }
    
    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateMenuHandler $createMenu): Response
    {
        $data = new MenuFormData();
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

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindMenuHandler $findMenu,
        UpdateMenuHandler $updateMenu,
    ): Response {
        try {
            $menu = $findMenu($id);     
        } catch (MenuNotFound) {
            throw $this->createNotFoundException();
        }

        $data = $this->formDataFromMenu($menu);
        $form = $this->createForm(MenuType::class, $data, [
            'exclude_menu_id' => $id,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updateMenu($id, $this->menuDataFromForm($data));
            } catch (MenuSlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderMenuForm($form, 'Edit menu');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderMenuForm($form, 'Edit menu');
            } catch (MenuNotFound) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('success', 'Menu updated.');

            return $this->redirectToRoute('admin_site_menus_index');
        }

        return $this->renderMenuForm($form, 'Edit menu');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteMenuHandler $deleteMenu): Response
    {
        if (!$this->isCsrfTokenValid('delete-site-menu-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deleteMenu($id);
        } catch (MenuNotFound) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Menu deleted.');

        return $this->redirectToRoute('admin_site_menus_index');
    }

    private function menuDataFromForm(MenuFormData $data): SiteMenuDataDTO
    {
        return new SiteMenuDataDTO(
            trim($data->title),
            $data->parent,
            $data->hasSlug,
            new Slug($data->slug),
            $data->position,
            $data->isActive,
        );
    }

    private function addDomainError(FormInterface $form, \InvalidArgumentException $exception): void
    {
        $field = str_contains(strtolower($exception->getMessage()), 'parent') ? 'parent' : 'slug';
        $form->get($field)->addError(new FormError($exception->getMessage()));
    }

    private function renderMenuForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/site_menus/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    private function formDataFromMenu(SiteMenuViewDTO $menu): MenuFormData
    {
        $data = new MenuFormData();
        $data->title = $menu->title;
        $data->slug = $menu->slug;
        $data->parent = $menu->parentId;
        $data->hasSlug = $menu->hasSlug;
        $data->position = $menu->position;
        $data->isActive = $menu->isActive;

        return $data;
    }
}
