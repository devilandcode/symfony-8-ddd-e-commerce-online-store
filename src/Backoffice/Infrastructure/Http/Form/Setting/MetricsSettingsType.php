<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MetricsSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('head', TextareaType::class, ['label' => 'Insert into the head section', 'required' => false])
            ->add('body_start', TextareaType::class, ['label' => 'Insert at the start of the body', 'required' => false])
            ->add('body_end', TextareaType::class, ['label' => 'Insert at the end of the body', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
