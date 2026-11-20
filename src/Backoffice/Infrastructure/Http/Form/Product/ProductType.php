<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Product;

use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ProductType extends AbstractType
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly BrandRepositoryInterface $brandRepository,
    ) {
    }

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
            ->add('categoryId', ChoiceType::class, [
                'label' => 'Category',
                'placeholder' => 'Choose a category',
                'choices' => $this->categoryChoices(),
                'choice_translation_domain' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('brandId', ChoiceType::class, [
                'label' => 'Brand',
                'placeholder' => 'Choose a brand',
                'choices' => $this->brandChoices(),
                'choice_translation_domain' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('h1', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('price', TextType::class, [
                'constraints' => [new NotBlank(), new Length(max: 24)],
                'help' => 'Enter a non-negative amount using the decimal precision for the selected currency.',
            ])
            ->add('oldPrice', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 24)],
                'help' => 'Optional. Must be greater than the current price.',
            ])
            ->add('currencyCode', TextType::class, [
                'constraints' => [new NotBlank(), new Length(exactly: 3)],
                'help' => 'Three-letter currency code, for example USD.',
            ])
            ->add('balance', IntegerType::class, ['constraints' => [new NotBlank(), new \Symfony\Component\Validator\Constraints\PositiveOrZero()]])
            ->add('imageUpload', FileType::class, [
                'required' => false,
                'label' => 'Product image',
                'constraints' => [new Image(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    detectCorrupted: true,
                )],
            ])
            ->add('removeImage', CheckboxType::class, [
                'required' => false,
                'label' => 'Remove current product image',
            ])
            ->add('additionalImageUploads', FileType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'Additional product images',
                'constraints' => [new All(new Image(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    detectCorrupted: true,
                ))],
            ])
            ->add('additionalImagesToRemove', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Remove existing additional images',
                'choices' => $options['existing_additional_images'],
            ])
            ->add('seoTitle', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('seoDescription', TextareaType::class, ['required' => false])
            ->add('position', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false]);
        if ($options['show_characteristics']) {
            $builder
                ->add('characteristicsCategoryId', HiddenType::class)
                ->add('characteristicAssignments', CollectionType::class, [
                    'entry_type' => ProductCharacteristicType::class,
                    'label' => 'Product characteristics',
                    'allow_add' => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                ]);
        }

        $builder->add('save', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductFormData::class,
            'show_characteristics' => false,
            'existing_additional_images' => [],
        ]);
        $resolver->setAllowedTypes('show_characteristics', 'bool');
        $resolver->setAllowedTypes('existing_additional_images', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'product_form';
    }

    /** @return array<string, int> */
    private function categoryChoices(): array
    {
        $choices = [];
        foreach ($this->categoryRepository->findAll() as $category) {
            $id = $category->getId();
            if (null !== $id) {
                $choices[sprintf('%s (#%d)', $category->getTitle(), $id)] = $id;
            }
        }

        return $choices;
    }

    /** @return array<string, int> */
    private function brandChoices(): array
    {
        $choices = [];
        foreach ($this->brandRepository->findAll() as $brand) {
            $id = $brand->getId();
            if (null !== $id) {
                $choices[sprintf('%s (#%d)', $brand->getTitle(), $id)] = $id;
            }
        }

        return $choices;
    }
}
