<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductType;
use App\Backoffice\Infrastructure\Money\CurrencyAmountConverter;
use App\Backoffice\Infrastructure\Storage\ProductImageChanges;
use App\Backoffice\Infrastructure\Storage\ProductImageSetManager;
use App\Catalog\Application\Product\AdminUseCases\CreateProduct\CreateProductHandler;
use App\Catalog\Application\Product\AdminUseCases\DeleteProduct\DeleteProductHandler;
use App\Catalog\Application\Product\AdminUseCases\Exception\ProductNotFoundException;
use App\Catalog\Application\Product\AdminUseCases\Exception\ProductSlugAlreadyInUseException;
use App\Catalog\Application\Product\AdminUseCases\FindProduct\FindProductHandler;
use App\Catalog\Application\Product\AdminUseCases\Input\ProductCharacteristicDataDTO;
use App\Catalog\Application\Product\AdminUseCases\Input\ProductDataDTO;
use App\Catalog\Application\Product\AdminUseCases\ListProducts\ListProductsHandler;
use App\Catalog\Application\Product\AdminUseCases\Output\ProductViewDTO;
use App\Catalog\Application\Product\AdminUseCases\UpdateProduct\UpdateProductHandler;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\ValueObject\Balance;
use App\Catalog\Domain\ValueObject\OldPrice;
use App\Catalog\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\Slug;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/products', name: 'products')]
final class ProductCrudController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CurrencyAmountConverter $amountConverter,
        private readonly ProductImageSetManager $productImageSetManager,
    )
    {
    }

    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListProductsHandler $listProducts): Response
    {
        return $this->render('backoffice/admin/products/index.html.twig', [
            'products' => $listProducts(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateProductHandler $createProduct): Response
    {
        $data = $this->formDataFromProduct(null, null, false);
        $form = $this->createForm(ProductType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            $imageChanges = null;
            try {
                $data = $form->getData();
                $imageChanges = $this->productImageSetManager->prepare(
                    null,
                    null,
                    false,
                    [],
                    $data->imageUpload,
                    $data->additionalImageUploads,
                );
                $id = $createProduct($this->dataFromForm($data, $imageChanges->image, $imageChanges->additionalImages));
            } catch (ProductSlugAlreadyInUseException $exception) {
                $this->discardProductImageUploads($imageChanges);
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Create product');
            } catch (\InvalidArgumentException $exception) {
                $this->discardProductImageUploads($imageChanges);
                $this->addProductDomainError($form, $exception);

                return $this->renderForm($form, 'Create product');
            } catch (\Throwable $exception) {
                $this->discardProductImageUploads($imageChanges);

                throw $exception;
            }

            $this->addFlash('success', 'Product created.');

            return $this->redirectToRoute('admin_products_edit', ['id' => $id]);
        }

        return $this->renderForm($form, 'Create product');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindProductHandler $findProduct,
        UpdateProductHandler $updateProduct,
    ): Response {
        try {
            $product = $findProduct($id);
        } catch (ProductNotFoundException) {
            throw $this->createNotFoundException();
        }

        $data = $this->formDataFromProduct($product, $this->submittedCategoryId($request, $product->categoryId));
        $form = $this->createEditForm($data);
        $form->handleRequest($request);

        $selectedCategoryMarker = null === $data->categoryId ? null : (string) $data->categoryId;
        $characteristicsNeedRefresh = $form->isSubmitted()
            && $data->characteristicsCategoryId !== $selectedCategoryMarker;
        if ($form->isSubmitted() && $characteristicsNeedRefresh) {
            $data = $form->getData();
            if ($characteristicsNeedRefresh) {
                $data->characteristicAssignments = [];
            }
            $this->refreshCharacteristicFields(
                $data,
                $data->categoryId,
                $data->categoryId === $product->categoryId ? $product : null,
            );
            $data->characteristicsCategoryId = null === $data->categoryId ? null : (string) $data->categoryId;
            $form = $this->createEditForm($data);
            $form->get('categoryId')->addError(new FormError('Review the characteristics for this category, then save again.'));
            if (null !== $data->imageUpload || [] !== $data->additionalImageUploads) {
                $this->addFlash('warning', 'Please reselect image files after refreshing the characteristics.');
            }

            return $this->renderForm($form, 'Edit product');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            $data = $form->getData();
            $imageChanges = null;
            try {
                $imageChanges = $this->productImageSetManager->prepare(
                    $product->image,
                    $product->additionalImages,
                    $data->removeImage,
                    $data->additionalImagesToRemove,
                    $data->imageUpload,
                    $data->additionalImageUploads,
                );
                $updateProduct($id, $this->dataFromForm(
                    $data,
                    $imageChanges->image,
                    $imageChanges->additionalImages,
                ));
            } catch (ProductSlugAlreadyInUseException $exception) {
                $this->discardProductImageUploads($imageChanges);
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Edit product');
            } catch (\InvalidArgumentException $exception) {
                $this->discardProductImageUploads($imageChanges);
                $this->addProductDomainError($form, $exception);

                return $this->renderForm($form, 'Edit product');
            } catch (ProductNotFoundException) {
                $this->discardProductImageUploads($imageChanges);
                throw $this->createNotFoundException();
            } catch (\Throwable $exception) {
                $this->discardProductImageUploads($imageChanges);

                throw $exception;
            }

            $this->productImageSetManager->removeObsolete($imageChanges);
            $this->addFlash('success', 'Product updated.');

            return $this->redirectToRoute('admin_products_index');
        }

        return $this->renderForm($form, 'Edit product');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteProductHandler $deleteProduct): Response
    {
        if (!$this->isCsrfTokenValid('delete-product-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deleteProduct($id);
        } catch (ProductNotFoundException) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Product deleted.');

        return $this->redirectToRoute('admin_products_index');
    }

    private function dataFromForm(
        ProductFormData $data,
        ?string $image,
        ?array $additionalImages,
    ): ProductDataDTO {
        $currencyCode = strtoupper(trim($data->currencyCode));
        $price = new Price($this->amountConverter->toMinorUnits($data->price, $currencyCode), $currencyCode);
        $oldPrice = null === $data->oldPrice || '' === trim($data->oldPrice)
            ? null
            : new OldPrice($this->amountConverter->toMinorUnits($data->oldPrice, $currencyCode));
        return new ProductDataDTO(
            trim($data->title),
            new Slug($data->slug),
            $data->h1,
            $data->description,
            $price,
            $oldPrice,
            new Balance($data->balance),
            $data->categoryId ?? throw new \InvalidArgumentException('A category is required.'),
            $data->brandId ?? throw new \InvalidArgumentException('A brand is required.'),
            $image,
            [] === $additionalImages ? null : $additionalImages,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isNew,
            $data->isHit,
            $data->isActive,
            array_values(array_map(
                static fn (ProductCharacteristicFormData $assignment): ProductCharacteristicDataDTO => new ProductCharacteristicDataDTO(
                    $assignment->characteristicId,
                    $assignment->value,
                ),
                $data->characteristicAssignments,
            )),
        );
    }

    private function formDataFromProduct(
        ?ProductViewDTO $product,
        ?int $categoryId,
        bool $showCharacteristics = true,
    ): ProductFormData {
        $data = new ProductFormData();
        $data->categoryId = $categoryId;
        $data->characteristicsCategoryId = null === $categoryId ? null : (string) $categoryId;
        if (null !== $product) {
            $data->title = $product->title;
            $data->slug = $product->slug;
            $data->h1 = $product->h1;
            $data->description = $product->description;
            $data->price = $this->amountConverter->fromMinorUnits($product->priceMinorUnits, $product->currencyCode);
            $data->oldPrice = null === $product->oldPriceMinorUnits
                ? null
                : $this->amountConverter->fromMinorUnits($product->oldPriceMinorUnits, $product->currencyCode);
            $data->currencyCode = $product->currencyCode;
            $data->balance = $product->balance;
            $data->brandId = $product->brandId;
            $data->image = $product->image;
            $data->additionalImages = $product->additionalImages;
            $data->seoTitle = $product->seoTitle;
            $data->seoDescription = $product->seoDescription;
            $data->position = $product->position;
            $data->isNew = $product->isNew;
            $data->isHit = $product->isHit;
            $data->isActive = $product->isActive;
        }

        if ($showCharacteristics) {
            $this->refreshCharacteristicFields($data, $categoryId, $product);
        }

        return $data;
    }

    private function createEditForm(ProductFormData $data): FormInterface
    {
        $choices = [];
        foreach ($data->additionalImages ?? [] as $index => $path) {
            $choices[sprintf('Image %d (%s)', $index + 1, basename($path))] = $path;
        }

        return $this->createForm(ProductType::class, $data, [
            'show_characteristics' => true,
            'existing_additional_images' => $choices,
            'has_existing_image' => null !== $data->image,
        ]);
    }

    private function discardProductImageUploads(?ProductImageChanges $changes): void
    {
        if (null !== $changes) {
            $this->productImageSetManager->discardUploads($changes);
        }
    }

    private function submittedCategoryId(Request $request, ?int $fallback = null): ?int
    {
        if (!$request->isMethod('POST')) {
            return $fallback;
        }

        $value = $request->request->all('product_form')['categoryId'] ?? null;
        if (null === $value || '' === $value) {
            return null;
        }

        $categoryId = filter_var($value, FILTER_VALIDATE_INT);

        return false === $categoryId || $categoryId <= 0 ? $fallback : $categoryId;
    }

    private function refreshCharacteristicFields(
        ProductFormData $data,
        ?int $categoryId,
        ?ProductViewDTO $product = null,
    ): void {
        $existingValues = [];
        if (null !== $product && $product->categoryId === $categoryId) {
            foreach ($product->characteristics as $assignment) {
                $existingValues[$assignment->characteristicId] = $assignment->value;
            }
        }
        foreach ($data->characteristicAssignments as $assignment) {
            $existingValues[$assignment->characteristicId] = $assignment->value;
        }

        $data->characteristicAssignments = [];
        $category = null === $categoryId ? null : $this->categoryRepository->findById($categoryId);
        if (null === $category) {
            return;
        }

        foreach ($category->getCharacteristicAssignments() as $assignment) {
            $characteristic = $assignment->getCharacteristic();
            $id = $characteristic->getId();
            if (!$assignment->isActive() || !$characteristic->getIsActive() || null === $id) {
                continue;
            }

            $formData = new ProductCharacteristicFormData();
            $formData->characteristicId = $id;
            $formData->title = $characteristic->getTitle();
            $formData->measure = $characteristic->getMeasure();
            $formData->value = $existingValues[$id] ?? '';
            $data->characteristicAssignments[] = $formData;
        }
    }

    private function addProductDomainError(FormInterface $form, \InvalidArgumentException $exception): void
    {
        $message = strtolower($exception->getMessage());
        $field = str_contains($message, 'slug')
            ? 'slug'
            : (str_contains($message, 'currency') ? 'currencyCode'
                : (str_contains($message, 'characteristic') ? 'characteristicAssignments'
                : (str_contains($message, 'old price') ? 'oldPrice'
                    : (str_contains($message, 'price') ? 'price'
                    : (str_contains($message, 'brand') ? 'brandId'
                        : (str_contains($message, 'category') ? 'categoryId' : 'title'))))));
        $form->get($field)->addError(new FormError($exception->getMessage()));
    }

    private function renderForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/products/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
}
