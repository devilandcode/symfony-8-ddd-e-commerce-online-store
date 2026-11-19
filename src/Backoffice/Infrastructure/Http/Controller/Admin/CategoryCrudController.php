<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Category\CategoryFormData;
use App\Backoffice\Infrastructure\Http\Form\Category\CategoryCharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Category\CategoryType;
use App\Catalog\Application\Category\AdminUseCases\CreateCategory\CreateCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\DeleteCategory\DeleteCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\AdminUseCases\Exception\CategorySlugAlreadyInUseException;
use App\Catalog\Application\Category\AdminUseCases\FindCategory\FindCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Input\CategoryDataDTO;
use App\Catalog\Application\Category\AdminUseCases\Input\CategoryCharacteristicDataDTO;
use App\Catalog\Application\Category\AdminUseCases\ListCategory\ListCategoryHandler;
use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Catalog\Application\Category\AdminUseCases\UpdateCategory\UpdateCategoryHandler;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
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
    public function __construct(private readonly CharacteristicRepositoryInterface $characteristicRepository)
    {
    }

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
        $data = $this->formDataFromCategory(null);
        $form = $this->createForm(CategoryType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createCategory($this->categoryDataFromForm($data));
            } catch (CategorySlugAlreadyInUseException $exception) {
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
        } catch (CategoryNotFoundException) {
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
            } catch (CategorySlugAlreadyInUseException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderCategoryForm($form, 'Edit category');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderCategoryForm($form, 'Edit category');
            } catch (CategoryNotFoundException) {
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
        } catch (CategoryNotFoundException) {
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
            array_values(array_map(
                static fn (CategoryCharacteristicFormData $assignment): CategoryCharacteristicDataDTO => new CategoryCharacteristicDataDTO(
                    $assignment->characteristicId,
                    $assignment->isActive,
                    $assignment->inFilter,
                ),
                array_filter(
                    $data->characteristicAssignments,
                    static fn (CategoryCharacteristicFormData $assignment): bool => $assignment->assigned,
                ),
            )),
        );
    }

    private function addDomainError(FormInterface $form, \InvalidArgumentException $exception): void
    {
        $message = strtolower($exception->getMessage());
        $field = str_contains($message, 'parent')
            ? 'parent'
            : (str_contains($message, 'characteristic') ? 'characteristicAssignments' : 'slug');
        $form->get($field)->addError(new FormError($exception->getMessage()));
    }

    private function renderCategoryForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/categories/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    private function formDataFromCategory(?CategoryViewDTO $category): CategoryFormData
    {
        $data = new CategoryFormData();
        if (null === $category) {
            foreach ($this->characteristicRepository->findAll() as $characteristic) {
                $this->addCharacteristicFormData($data, $characteristic, null);
            }

            return $data;
        }

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

        $assignments = [];
        foreach ($category->characteristicAssignments as $assignment) {
            $assignments[$assignment->characteristicId] = $assignment;
        }
        foreach ($this->characteristicRepository->findAll() as $characteristic) {
            $this->addCharacteristicFormData(
                $data,
                $characteristic,
                $assignments[$characteristic->getId()] ?? null,
            );
        }

        return $data;
    }

    private function addCharacteristicFormData(
        CategoryFormData $data,
        Characteristic $characteristic,
        ?\App\Catalog\Application\Category\AdminUseCases\Output\CategoryCharacteristicViewDTO $assignment,
    ): void {
        $characteristicId = $characteristic->getId();
        if (null === $characteristicId) {
            return;
        }

        $formAssignment = new CategoryCharacteristicFormData();
        $formAssignment->characteristicId = $characteristicId;
        $formAssignment->title = $characteristic->getTitle();
        $formAssignment->measure = $characteristic->getMeasure();
        $formAssignment->assigned = null !== $assignment;
        $formAssignment->isActive = $assignment?->isActive ?? true;
        $formAssignment->inFilter = $assignment?->inFilter ?? false;
        $data->characteristicAssignments[] = $formAssignment;
    }
}
