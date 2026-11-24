<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Brand;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
            ])
            ->add('slug', TextType::class, [
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
                'help' => 'The slug is converted to lowercase words separated by hyphens.',
            ])
            ->add('h1', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('imageUpload', FileType::class, [
                'required' => false,
                'label' => 'Brand image',
                'constraints' => [new Image(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    detectCorrupted: true,
                )],
            ])
            ->add('seoTitle', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('seoDescription', TextareaType::class, ['required' => false])
            ->add('position', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false]);
            
        if ($options['has_existing_image']) {
            $builder->add('removeImage', CheckboxType::class, [
                'required' => false,
                'label' => 'Remove current brand image',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BrandFormData::class,
            'has_existing_image' => false,
        ]);
        $resolver->setAllowedTypes('has_existing_image', 'bool');
    }
}
