<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Brand\BrandFormData;
use App\Backoffice\Infrastructure\Http\Form\Brand\BrandType;
use App\Catalog\Application\Brand\AdminUseCases\CreateBrand\CreateBrandHandler;
use App\Catalog\Application\Brand\AdminUseCases\DeleteBrand\DeleteBrandHandler;
use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandHasProductsException;
use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandNotFoundException;
use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandSlugAlreadyInUseException;
use App\Catalog\Application\Brand\AdminUseCases\FindBrand\FindBrandHandler;
use App\Catalog\Application\Brand\AdminUseCases\Input\BrandDataDTO;
use App\Catalog\Application\Brand\AdminUseCases\ListBrands\ListBrandsHandler;
use App\Catalog\Application\Brand\AdminUseCases\Output\BrandViewDTO;
use App\Catalog\Application\Brand\AdminUseCases\UpdateBrand\UpdateBrandHandler;
use App\Shared\Domain\ValueObject\Slug;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/brands', name: 'brands')]
final class BrandCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListBrandsHandler $listBrands): Response
    {
        return $this->render('backoffice/admin/brands/index.html.twig', [
            'brands' => $listBrands(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateBrandHandler $createBrand): Response
    {
        $form = $this->createForm(BrandType::class, new BrandFormData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createBrand($this->dataFromForm($form->getData()));
            } catch (BrandSlugAlreadyInUseException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Create brand');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderForm($form, 'Create brand');
            }

            $this->addFlash('success', 'Brand created.');

            return $this->redirectToRoute('admin_brands_index');
        }

        return $this->renderForm($form, 'Create brand');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindBrandHandler $findBrand,
        UpdateBrandHandler $updateBrand,
    ): Response {
        try {
            $brand = $findBrand($id);
        } catch (BrandNotFoundException) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BrandType::class, $this->formDataFromBrand($brand));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updateBrand($id, $this->dataFromForm($form->getData()));
            } catch (BrandSlugAlreadyInUseException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Edit brand');
            } catch (\InvalidArgumentException $exception) {
                $this->addDomainError($form, $exception);

                return $this->renderForm($form, 'Edit brand');
            } catch (BrandNotFoundException) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('success', 'Brand updated.');

            return $this->redirectToRoute('admin_brands_index');
        }

        return $this->renderForm($form, 'Edit brand');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteBrandHandler $deleteBrand): Response
    {
        if (!$this->isCsrfTokenValid('delete-brand-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deleteBrand($id);
        } catch (BrandNotFoundException) {
            throw $this->createNotFoundException();
        } catch (BrandHasProductsException $exception) {
            $this->addFlash('danger', $exception->getMessage());

            return $this->redirectToRoute('admin_brands_index');
        }

        $this->addFlash('success', 'Brand deleted.');

        return $this->redirectToRoute('admin_brands_index');
    }

    private function dataFromForm(BrandFormData $data): BrandDataDTO
    {
        return new BrandDataDTO(
            trim($data->title),
            new Slug($data->slug),
            $data->h1,
            $data->description,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
        );
    }

    private function formDataFromBrand(BrandViewDTO $brand): BrandFormData
    {
        $data = new BrandFormData();
        $data->title = $brand->title;
        $data->slug = $brand->slug;
        $data->h1 = $brand->h1;
        $data->description = $brand->description;
        $data->seoTitle = $brand->seoTitle;
        $data->seoDescription = $brand->seoDescription;
        $data->position = $brand->position;
        $data->isActive = $brand->isActive;

        return $data;
    }

    private function addDomainError(FormInterface $form, \InvalidArgumentException $exception): void
    {
        $field = str_contains(strtolower($exception->getMessage()), 'slug') ? 'slug' : 'title';
        $form->get($field)->addError(new FormError($exception->getMessage()));
    }

    private function renderForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/brands/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
}
