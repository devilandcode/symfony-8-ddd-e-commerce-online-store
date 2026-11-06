<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactsSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('copyright', TextType::class, ['label' => 'Copyright', 'required' => false])
            ->add('email', TextType::class, ['label' => 'Order email addresses', 'required' => false])
            ->add('company_info', TextareaType::class, ['label' => 'Company information (site footer)', 'required' => false])
            ->add('footer_shop_1', TextareaType::class, ['label' => 'Footer location 1', 'required' => false])
            ->add('footer_shop_2', TextareaType::class, ['label' => 'Footer location 2', 'required' => false])
            ->add('instagram', TextType::class, ['label' => 'Instagram', 'required' => false])
            ->add('facebook', TextType::class, ['label' => 'Facebook', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
