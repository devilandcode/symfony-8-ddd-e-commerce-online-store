<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\SiteMenu;

use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MenuType extends AbstractType
{
    public function __construct(private SiteMenuRepositoryInterface $siteMenus)
    {
    }

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
                'choices' => $this->parentChoices($options['exclude_menu_id']),
                'choice_translation_domain' => false,
            ])
            ->add('hasSlug', CheckboxType::class, [
                'label' => 'Link',
                'required' => false,
                'help' => 'Uncheck to keep the slug stored but not render this item as a link.',
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
                'help' => 'The slug is converted to lowercase words separated by hyphens.',
            ])
            ->add('position', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuFormData::class,
            'exclude_menu_id' => null,
        ]);
        $resolver->setAllowedTypes('exclude_menu_id', ['int', 'null']);
    }

    /**
     * @return array<string, int>
     */
    private function parentChoices(?int $excludeMenuId): array
    {
        $choices = [];

        foreach ($this->siteMenus->findAll() as $menu) {
            $id = $menu->getId();
            if (null === $id || $id === $excludeMenuId) {
                continue;
            }

            $choices[sprintf('%s (#%d)', $menu->getTitle(), $id)] = $id;
        }

        return $choices;
    }
}
