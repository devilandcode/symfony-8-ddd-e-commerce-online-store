<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Category\CategoryFormData;
use App\Backoffice\Infrastructure\Http\Form\Category\CategoryType;
use App\Catalog\Application\Category\AdminUseCases\CreateCategory\CreateCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\DeleteCategory\DeleteCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Exception\CategoryNotFound;
use App\Catalog\Application\Category\AdminUseCases\Exception\CategorySlugAlreadyInUse;
use App\Catalog\Application\Category\AdminUseCases\FindCategory\FindCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Input\CategoryDataDTO;
use App\Catalog\Application\Category\AdminUseCases\ListCategory\ListCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Catalog\Application\Category\AdminUseCases\UpdateCategory\UpdateCategoryHandler;
use App\Shared\Domain\ValueObject\Slug;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
        $form = $this->createForm(CategoryType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createCategory($this->categoryDataFromForm($data));
            } catch (CategorySlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderCategoryForm($form, 'Create category');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderCategoryForm($form, 'Create category');
            }

            $this->addFlash('success', 'Category created.');

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->renderCategoryForm($form, 'Create category');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindCategoryHandler $findCategory,
        UpdateCategoryHandler $updateCategory,
    ): Response {
        try {
            $category = $findCategory($id);     
        } catch (CategoryNotFound) {
            throw $this->createNotFoundException();
        }

        $data = $this->formDataFromCategory($category);
        $form = $this->createForm(CategoryType::class, $data, [
            'exclude_category_id' => $id,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updateCategory($id, $this->categoryDataFromForm($data));
            } catch (CategorySlugAlreadyInUse $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderCategoryForm($form, 'Edit categoryu');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderCategoryForm($form, 'Edit category');
            } catch (CategoryNotFound) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('success', 'Category updated.');

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->renderCategoryForm($form, 'Edit category');
    }

     #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteCategoryHandler $deleteCategory): Response
    {
        if (!$this->isCsrfTokenValid('delete-category-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deleteCategory($id);
        } catch (CategoryNotFound) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Category deleted.');

        return $this->redirectToRoute('admin_categories_index');
    }

    private function categoryDataFromForm(CategoryFormData $data): CategoryDataDTO
    {
        return new CategoryDataDTO(
            trim($data->title),
            new Slug($data->slug),
            $data->h1,
            $data->parent,
            $data->level,
            $data->description,
            $data->content,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
        );
    }

    private function addDomainError(FormInterface $form, \InvalidArgumentException $exception): void
    {
        $field = str_contains(strtolower($exception->getMessage()), 'parent') ? 'parent' : 'slug';
        $form->get($field)->addError(new FormError($exception->getMessage()));
    }

    private function renderCategoryForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/categories/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    private function formDataFromCategory(CategoryViewDTO $category): CategoryFormData
    {
        $data = new CategoryFormData();
        $data->title = $category->title;
        $data->slug = $category->slug;
        $data->h1 = $category->h1;
        $data->parent = $category->parentId;
        $data->level = $category->level;
        $data->description = $category->description;
        $data->content = $category->content;
        $data->seoTitle = $category->seoTitle;
        $data->seoDescription = $category->seoDescription;
        $data->position = $category->position;
        $data->isActive = $category->isActive;

        return $data;
    }
}
