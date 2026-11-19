<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Characteristic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new NotBlank(normalizer: 'trim'), new Length(max: 255)],
            ])
            ->add('measure', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 255)],
            ])
            ->add('position', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CharacteristicFormData::class]);
    }
}
