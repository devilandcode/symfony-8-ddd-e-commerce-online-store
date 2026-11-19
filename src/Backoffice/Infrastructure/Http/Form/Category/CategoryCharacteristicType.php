<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryCharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('characteristicId', HiddenType::class)
            ->add('assigned', CheckboxType::class, ['required' => false, 'label' => 'Assigned'])
            ->add('isActive', CheckboxType::class, ['required' => false, 'label' => 'Active'])
            ->add('inFilter', CheckboxType::class, ['required' => false, 'label' => 'In Filter']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CategoryCharacteristicFormData::class]);
    }
}
