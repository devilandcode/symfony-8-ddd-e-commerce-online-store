<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\MenuFormData;
use App\Backoffice\Infrastructure\Http\Form\MenuType;
use App\ContentManagement\Application\SiteMenu\CreateMenu\CreateMenuHandler;
use App\ContentManagement\Application\SiteMenu\Exception\MenuSlugAlreadyInUse;
use App\ContentManagement\Application\SiteMenu\Input\SiteMenuDataDTO;
use App\ContentManagement\Application\SiteMenu\ListSiteMenus\ListSiteMenuHandler;
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
    public function index(ListSiteMenuHandler $listPages): Response
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
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderMenuForm($form, 'Create menu');
            }

            $this->addFlash('success', 'Menu created.');

            return $this->redirectToRoute('admin_site_menus_index');
        }

        return $this->renderMenuForm($form, 'Create menu');
    }

    private function menuDataFromForm(MenuFormData $data): SiteMenuDataDTO
    {
        return new SiteMenuDataDTO(
            trim($data->title),
            new Slug($data->slug),
            $data->header,
            $data->footer,
            $data->position,
            $data->isActive,
        );
    }

    private function renderMenuForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/site_menus/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
}
