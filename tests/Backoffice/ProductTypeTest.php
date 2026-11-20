<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicType;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductType;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

final class ProductTypeTest extends KernelTestCase
{
    public function testCreationFormOmitsCharacteristicsAndSupportsImageUploads(): void
    {
        $form = $this->formFactory()->create(ProductType::class, new ProductFormData());

        self::assertFalse($form->has('characteristicAssignments'));
        self::assertFalse($form->has('loadCharacteristics'));
        self::assertFalse($form->has('removeImage'));
        self::assertFalse($form->has('additionalImagesToRemove'));
        self::assertTrue($form->has('imageUpload'));
        self::assertTrue($form->has('additionalImageUploads'));
        self::assertTrue($form->get('additionalImageUploads')->getConfig()->getOption('multiple'));
        self::assertTrue($form->createView()->vars['multipart']);

        $imageConstraint = $form->get('imageUpload')->getConfig()->getOption('constraints')[0];
        self::assertInstanceOf(Image::class, $imageConstraint);
        self::assertSame(5_000_000, $imageConstraint->maxSize);
        self::assertSame(['image/jpeg', 'image/png', 'image/webp'], $imageConstraint->mimeTypes);

        $additionalImagesConstraint = $form->get('additionalImageUploads')->getConfig()->getOption('constraints')[0];
        self::assertInstanceOf(All::class, $additionalImagesConstraint);
        self::assertSame(5_000_000, $additionalImagesConstraint->constraints[0]->maxSize);
    }

    public function testEditFormKeepsCharacteristicIdsServerSide(): void
    {
        $assignment = new ProductCharacteristicFormData();
        $assignment->characteristicId = 42;
        $assignment->title = 'Color';
        $assignment->measure = 'shade';
        $assignment->value = 'blue';

        $data = new ProductFormData();
        $data->characteristicAssignments = [$assignment];
        $form = $this->formFactory()->create(ProductType::class, $data, ['show_characteristics' => true]);
        $collection = $form->get('characteristicAssignments');

        self::assertFalse($collection->get('0')->has('characteristicId'));
        self::assertSame('Color', $collection->get('0')->getData()->title);
        self::assertSame('shade', $collection->get('0')->getData()->measure);
        $collection->submit([['value' => 'green']]);

        self::assertSame(42, $data->characteristicAssignments[0]->characteristicId);
        self::assertSame('green', $data->characteristicAssignments[0]->value);
    }

    public function testEditFormRendersAndAcceptsImageRemovalCheckboxes(): void
    {
        $primaryImage = 'uploads/products/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $additionalImages = [
            'uploads/products/bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png',
            'uploads/products/cccccccccccccccccccccccccccccccc.jpg',
        ];
        $data = new ProductFormData();
        $data->image = $primaryImage;
        $data->additionalImages = $additionalImages;
        $choices = [
            'Image 1 (bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png)' => $additionalImages[0],
            'Image 2 (cccccccccccccccccccccccccccccccc.jpg)' => $additionalImages[1],
        ];
        $form = $this->formFactory()->create(ProductType::class, $data, [
            'show_characteristics' => true,
            'existing_additional_images' => $choices,
            'has_existing_image' => true,
        ]);
        $view = $form->createView();

        self::assertContains('checkbox', $view->children['removeImage']->vars['block_prefixes']);
        self::assertFalse($form->has('loadCharacteristics'));
        self::assertSame(2, count($view->children['additionalImagesToRemove']->children));
        self::assertSame($additionalImages[0], $view->children['additionalImagesToRemove']->children[0]->vars['value']);
        self::assertSame($additionalImages[1], $view->children['additionalImagesToRemove']->children[1]->vars['value']);

        $form->submit([
            'removeImage' => '1',
            'additionalImagesToRemove' => [$additionalImages[1]],
        ], false);

        self::assertTrue($data->removeImage);
        self::assertSame([$additionalImages[1]], $data->additionalImagesToRemove);
    }

    public function testEditTemplateHidesCharacteristicRefreshAndRendersRemovalCheckboxes(): void
    {
        self::bootKernel();
        $request = Request::create('/admin/products/1/edit');
        $request->setSession(new Session(new MockArraySessionStorage()));
        static::getContainer()->get('request_stack')->push($request);

        $data = new ProductFormData();
        $data->image = 'uploads/products/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $data->additionalImages = [
            'uploads/products/bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png',
            'uploads/products/cccccccccccccccccccccccccccccccc.jpg',
        ];
        $form = $this->formFactory()->create(ProductType::class, $data, [
            'show_characteristics' => true,
            'existing_additional_images' => [
                'Image 1' => $data->additionalImages[0],
                'Image 2' => $data->additionalImages[1],
            ],
            'has_existing_image' => true,
        ]);
        $html = static::getContainer()
            ->get('twig')
            ->load('backoffice/admin/products/form.html.twig')
            ->renderBlock('main', ['form' => $form->createView(), 'title' => 'Edit product']);

        self::assertStringNotContainsString('Load category characteristics', $html);
        self::assertStringContainsString('product_form[removeImage]', $html);
        self::assertStringContainsString('product_form[additionalImagesToRemove][]', $html);
        self::assertStringContainsString('id="product_form_additionalImagesToRemove_0"', $html);
        self::assertStringContainsString('id="product_form_additionalImagesToRemove_1"', $html);
        self::assertStringContainsString($data->additionalImages[0], $html);
        self::assertStringContainsString($data->additionalImages[1], $html);
        self::assertSame(1, substr_count($html, 'Remove existing additional images'));
        self::assertLessThan(strpos($html, 'product_form_save'), strpos($html, 'product_form_removeImage'));
        self::assertLessThan(strpos($html, 'product_form_save'), strpos($html, 'product_form_additionalImagesToRemove_0'));
        self::assertStringContainsString('gap-2 mt-4', $html);
    }

    private function formFactory(): \Symfony\Component\Form\FormFactoryInterface
    {
        $categories = $this->createStub(CategoryRepositoryInterface::class);
        $categories->method('findAll')->willReturn([]);
        $brands = $this->createStub(BrandRepositoryInterface::class);
        $brands->method('findAll')->willReturn([]);

        return Forms::createFormFactoryBuilder()
            ->addType(new ProductCharacteristicType())
            ->addType(new ProductType($categories, $brands))
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
    }
}
