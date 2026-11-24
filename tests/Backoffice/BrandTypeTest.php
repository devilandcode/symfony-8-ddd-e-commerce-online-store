<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\Brand\BrandFormData;
use App\Backoffice\Infrastructure\Http\Form\Brand\BrandType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BrandTypeTest extends KernelTestCase
{
    public function testBrandFormsAcceptImageUploadsAndOnlyOfferRemovalForExistingImages(): void
    {
        self::bootKernel();

        $formFactory = static::getContainer()->get('form.factory');
        $createForm = $formFactory->create(BrandType::class, new BrandFormData(), [
            'csrf_protection' => false,
        ]);
        self::assertArrayHasKey('imageUpload', $createForm->createView()->children);
        self::assertArrayNotHasKey('removeImage', $createForm->createView()->children);

        $editData = new BrandFormData();
        $editData->image = 'uploads/brands/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';
        $editForm = $formFactory->create(BrandType::class, $editData, [
            'csrf_protection' => false,
            'has_existing_image' => true,
        ]);
        $editView = $editForm->createView();

        self::assertArrayHasKey('imageUpload', $editView->children);
        self::assertArrayHasKey('removeImage', $editView->children);
    }
}
