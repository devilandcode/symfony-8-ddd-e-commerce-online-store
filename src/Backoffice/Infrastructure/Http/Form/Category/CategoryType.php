<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Category;

use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CategoryType
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
            ])
            ->add('parent', ChoiceType::class, [
                'label' => 'Parent',
                'required' => false,
                'placeholder' => 'Root',
                'choices' => $this->parentChoices($options['exclude_category_id']),
                'choice_translation_domain' => false,
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
                'help' => 'The slug is converted to lowercase words separated by hyphens.',
            ])
            ->add('level', IntegerType::class, [
                'required' => true,
                'constraints' => [new NotBlank(normalizer: 'trim')],
                'help' => 'The level is used to determine the depth of the category in the hierarchy.',
            ])
            ->add('h1', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('content', TextareaType::class, ['required' => false])
            ->add('seoTitle', TextType::class, ['required' => false, 'constraints' => [new Length(max: 255)]])
            ->add('seoDescription', TextareaType::class, ['required' => false])
            ->add('position', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryFormData::class,
            'exclude_category_id' => null,
        ]);
        $resolver->setAllowedTypes('exclude_category_id', ['int', 'null']);
    }

    /**
     * @return array<string, int>
     */
    private function parentChoices(?int $excludeCategoryId): array
    {
        $choices = [];

        foreach ($this->categoryRepository->findAll() as $category) {
            $id = $category->getId();
            if (null === $id || $id === $excludeCategoryId) {
                continue;
            }

            $choices[sprintf('%s (#%d)', $category->getTitle(), $id)] = $id;
        }

        return $choices;
    }
}
