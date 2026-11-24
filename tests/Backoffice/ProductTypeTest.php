<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductCharacteristicType;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductFormData;
use App\Backoffice\Infrastructure\Http\Form\Product\ProductType;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use Imagick;
use ImagickPixel;
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
    private string $projectDirectory = '';
    /** @var list<string> */
    private array $sourceFiles = [];
    /** @var list<string> */
    private array $existingVariantFiles = [];

    protected function tearDown(): void
    {
        if ('' !== $this->projectDirectory) {
            foreach ($this->sourceFiles as $sourceFile) {
                if (is_file($sourceFile)) {
                    unlink($sourceFile);
                }
            }

            $newVariantFiles = array_diff($this->variantFiles(), $this->existingVariantFiles);
            foreach ($newVariantFiles as $variantFile) {
                if (is_file($variantFile)) {
                    unlink($variantFile);
                }
            }
            $this->removeEmptyVariantDirectories($newVariantFiles);
        }

        parent::tearDown();
    }

    public function testCreationFormOmitsCharacteristicsAndSupportsImageUploads(): void
    {
        $form = $this->formFactory()->create(ProductType::class, new ProductFormData());

        self::assertFalse($form->has('characteristicAssignments'));
        self::assertFalse($form->has('loadCharacteristics'));
        self::assertFalse($form->has('removeImage'));
        self::assertFalse($form->has('additionalImagesToRemove'));
        self::assertTrue($form->has('imageUpload'));
        self::assertTrue($form->has('additionalImageUploads'));
        self::assertTrue($form->has('isNew'));
        self::assertTrue($form->has('isHit'));
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

    public function testProductFlagsAreSubmittedAsBooleans(): void
    {
        $data = new ProductFormData();
        $form = $this->formFactory()->create(ProductType::class, $data);
        $form->submit([
            'isNew' => '1',
            'isHit' => '1',
            'isActive' => null,
        ], false);

        self::assertTrue($data->isNew);
        self::assertTrue($data->isHit);
        self::assertFalse($data->isActive);
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
        $this->projectDirectory = static::getContainer()->getParameter('kernel.project_dir');
        $this->existingVariantFiles = $this->variantFiles();
        $request = Request::create('/admin/products/1/edit');
        $request->setSession(new Session(new MockArraySessionStorage()));
        static::getContainer()->get('request_stack')->push($request);

        $data = new ProductFormData();
        $data->image = $this->createUploadedImage();
        $data->additionalImages = [
            $this->createUploadedImage(),
            $this->createUploadedImage(),
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
        self::assertGreaterThanOrEqual(3, substr_count($html, '/uploads/variants/'));
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

    private function createUploadedImage(): string
    {
        $relativePath = sprintf('uploads/products/test-%s.png', bin2hex(random_bytes(8)));
        $absolutePath = $this->projectDirectory.'/public/'.$relativePath;
        if (!is_dir(dirname($absolutePath)) && !mkdir(dirname($absolutePath), 0755, true) && !is_dir(dirname($absolutePath))) {
            throw new \RuntimeException('Unable to create a product image fixture directory.');
        }

        $image = new Imagick();
        $image->newImage(80, 40, new ImagickPixel('blue'), 'PNG');
        $image->writeImage($absolutePath);
        $image->clear();
        $image->destroy();
        $this->sourceFiles[] = $absolutePath;

        return $relativePath;
    }

    /** @return list<string> */
    private function variantFiles(): array
    {
        $files = glob($this->projectDirectory.'/public/uploads/variants/*/*');

        return false === $files ? [] : $files;
    }

    /** @param list<string> $newVariantFiles */
    private function removeEmptyVariantDirectories(array $newVariantFiles): void
    {
        foreach (array_unique(array_map(dirname(...), $newVariantFiles)) as $directory) {
            $items = is_dir($directory) ? scandir($directory) : false;
            if (false !== $items && 2 === count($items)) {
                rmdir($directory);
            }
        }
    }
}
