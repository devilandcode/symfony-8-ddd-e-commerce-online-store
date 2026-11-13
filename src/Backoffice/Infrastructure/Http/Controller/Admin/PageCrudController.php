<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Page\PageFormData;
use App\Backoffice\Infrastructure\Http\Form\Page\PageType;
use App\ContentManagement\Application\Page\AdminUseCases\Input\PageDataDTO;
use App\ContentManagement\Application\Page\AdminUseCases\CreatePage\CreatePageHandler;
use App\ContentManagement\Application\Page\AdminUseCases\DeletePage\DeletePageHandler;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\Page\AdminUseCases\FindPage\FindPageHandler;
use App\ContentManagement\Application\Page\AdminUseCases\ListPages\ListPagesHandler;
use App\ContentManagement\Application\Page\AdminUseCases\Output\PageViewDTO;
use App\ContentManagement\Application\Page\AdminUseCases\UpdatePage\UpdatePageHandler;
use App\Shared\Domain\ValueObject\Slug;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/pages', name: 'pages')]
final class PageCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListPagesHandler $listPages): Response
    {
        return $this->render('backoffice/admin/pages/index.html.twig', [
            'pages' => $listPages(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreatePageHandler $createPage): Response
    {
        $data = new PageFormData();
        $form = $this->createForm(PageType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createPage($this->pageDataFromForm($data));
            } catch (PageSlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderPageForm($form, 'Create page');
            } catch (\InvalidArgumentException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderPageForm($form, 'Create page');
            }

            $this->addFlash('success', 'Page created.');

            return $this->redirectToRoute('admin_pages_index');
        }

        return $this->renderPageForm($form, 'Create page');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindPageHandler $findPage,
        UpdatePageHandler $updatePage,
    ): Response {
        try {
            $page = $findPage($id);
        } catch (PageNotFound) {
            throw $this->createNotFoundException();
        }

        $data = $this->formDataFromPage($page);
        $form = $this->createForm(PageType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updatePage($id, $this->pageDataFromForm($data));
            } catch (PageSlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderPageForm($form, 'Edit page');
            } catch (\InvalidArgumentException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderPageForm($form, 'Edit page');
            } catch (PageNotFound) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('success', 'Page updated.');

            return $this->redirectToRoute('admin_pages_index');
        }

        return $this->renderPageForm($form, 'Edit page');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeletePageHandler $deletePage): Response
    {
        if (!$this->isCsrfTokenValid('delete-page-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deletePage($id);
        } catch (PageNotFound) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Page deleted.');

        return $this->redirectToRoute('admin_pages_index');
    }

    private function renderPageForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/pages/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    private function pageDataFromForm(PageFormData $data): PageDataDTO
    {
        return new PageDataDTO(
            trim($data->title),
            new Slug($data->slug),
            $this->nullableTrimmed($data->h1),
            $this->nullableTrimmed($data->description),
            $this->nullableTrimmed($data->content),
            $this->nullableTrimmed($data->seoTitle),
            $this->nullableTrimmed($data->seoDescription),
            $data->position,
            $data->isActive,
        );
    }

    private function formDataFromPage(PageViewDTO $page): PageFormData
    {
        $data = new PageFormData();
        $data->title = $page->title;
        $data->slug = $page->slug;
        $data->h1 = $page->h1;
        $data->description = $page->description;
        $data->content = $page->content;
        $data->seoTitle = $page->seoTitle;
        $data->seoDescription = $page->seoDescription;
        $data->position = $page->position;
        $data->isActive = $page->isActive;

        return $data;
    }

    private function nullableTrimmed(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
