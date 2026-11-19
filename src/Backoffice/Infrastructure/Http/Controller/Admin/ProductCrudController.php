<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductType;
use App\Backoffice\Infrastructure\Money\CurrencyAmountConverter;
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
        $data = $this->formDataFromProduct(null, $this->submittedCategoryId($request));
        $form = $this->createForm(ProductType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->get('loadCharacteristics')->isClicked()) {
            $data = $form->getData();
            $data->characteristicAssignments = [];
            $this->refreshCharacteristicFields($data, $data->categoryId);
            $form = $this->createForm(ProductType::class, $data);

            return $this->renderForm($form, 'Create product');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            try {
                $createProduct($this->dataFromForm($form->getData()));
            } catch (ProductSlugAlreadyInUseException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Create product');
            } catch (\InvalidArgumentException $exception) {
                $this->addProductDomainError($form, $exception);

                return $this->renderForm($form, 'Create product');
            }

            $this->addFlash('success', 'Product created.');

            return $this->redirectToRoute('admin_products_index');
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
        $form = $this->createForm(ProductType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->get('loadCharacteristics')->isClicked()) {
            $data = $form->getData();
            if ($product->categoryId !== $data->categoryId) {
                $data->characteristicAssignments = [];
            }
            $this->refreshCharacteristicFields(
                $data,
                $data->categoryId,
                $product->categoryId === $data->categoryId ? $product : null,
            );
            $form = $this->createForm(ProductType::class, $data);

            return $this->renderForm($form, 'Edit product');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            try {
                $updateProduct($id, $this->dataFromForm($form->getData()));
            } catch (ProductSlugAlreadyInUseException $exception) {
                $form->get('slug')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Edit product');
            } catch (\InvalidArgumentException $exception) {
                $this->addProductDomainError($form, $exception);

                return $this->renderForm($form, 'Edit product');
            } catch (ProductNotFoundException) {
                throw $this->createNotFoundException();
            }

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

    private function dataFromForm(ProductFormData $data): ProductDataDTO
    {
        $currencyCode = strtoupper(trim($data->currencyCode));
        $price = new Price($this->amountConverter->toMinorUnits($data->price, $currencyCode), $currencyCode);
        $oldPrice = null === $data->oldPrice || '' === trim($data->oldPrice)
            ? null
            : new OldPrice($this->amountConverter->toMinorUnits($data->oldPrice, $currencyCode));
        $additionalImages = null === $data->additionalImages
            ? []
            : array_values(array_filter(array_map('trim', preg_split('/\R/', $data->additionalImages) ?: [])));

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
            $data->image,
            [] === $additionalImages ? null : $additionalImages,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
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

    private function formDataFromProduct(?ProductViewDTO $product, ?int $categoryId): ProductFormData
    {
        $data = new ProductFormData();
        $data->categoryId = $categoryId;
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
            $data->additionalImages = null === $product->additionalImages ? null : implode("\n", $product->additionalImages);
            $data->seoTitle = $product->seoTitle;
            $data->seoDescription = $product->seoDescription;
            $data->position = $product->position;
            $data->isActive = $product->isActive;
        }

        $this->refreshCharacteristicFields($data, $categoryId, $product);

        return $data;
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
