<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('privacy', TextType::class, ['label' => 'Personal data policy URL', 'required' => false])
            ->add('products_count', TextType::class, ['label' => 'Products per page', 'required' => false])
            ->add('brands_count', TextType::class, ['label' => 'Brands per page', 'required' => false])
            ->add('news_count', TextType::class, ['label' => 'News items per page', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
