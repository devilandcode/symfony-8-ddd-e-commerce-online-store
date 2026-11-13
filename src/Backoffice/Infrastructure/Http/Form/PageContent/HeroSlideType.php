<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use App\Backoffice\Infrastructure\Http\Form\PageContent\Constraint\SafeLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class HeroSlideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heading', TextType::class, ['label' => 'Heading', 'required' => false])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('buttonLabel', TextType::class, ['label' => 'Button text', 'required' => false])
            ->add('buttonUrl', TextType::class, [
                'label' => 'Button URL or path',
                'required' => false,
                'help' => 'Use a local path such as /catalog or an http(s) URL.',
                'constraints' => [new SafeLink()],
            ])
            ->add('desktopImage', FileType::class, [
                'label' => 'Desktop image',
                'required' => false,
                'keep_stored_path' => true,
                'help' => 'Required to display an active slide.',
                'constraints' => [new Callback(self::validateUploadedImage(...))],
            ])
            ->add('mobileImage', FileType::class, [
                'label' => 'Mobile image',
                'required' => false,
                'keep_stored_path' => true,
                'help' => 'Optional. The desktop image is used on mobile when this is empty.',
                'constraints' => [new Callback(self::validateUploadedImage(...))],
            ])
            ->add('isActive', CheckboxType::class, ['label' => 'Show this slide', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'constraints' => [new Callback(self::validateActiveSlide(...))],
        ]);
    }

    public static function validateActiveSlide(mixed $data, ExecutionContextInterface $context, mixed $payload = null): void
    {
        if (!\is_array($data) || !($data['isActive'] ?? false)) {
            return;
        }

        if (self::hasDesktopImage($data['desktopImage'] ?? null)) {
            return;
        }

        $context->buildViolation('Upload a desktop image before activating this slide.')
            ->atPath('desktopImage')
            ->addViolation();
    }

    public static function validateUploadedImage(mixed $value, ExecutionContextInterface $context, mixed $payload = null): void
    {
        if (!$value instanceof UploadedFile) {
            return;
        }

        $violations = $context->getValidator()->validate($value, new Image(
            maxSize: '8M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        ));

        foreach ($violations as $violation) {
            $context->buildViolation($violation->getMessage())->addViolation();
        }
    }

    private static function hasDesktopImage(mixed $value): bool
    {
        if ($value instanceof UploadedFile) {
            return true;
        }

        return \is_string($value) && '' !== trim($value);
    }
}
