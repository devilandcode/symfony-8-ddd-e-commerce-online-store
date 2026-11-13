<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use App\Backoffice\Infrastructure\Http\Form\PageContent\Constraint\SafeLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class HomeSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heading', TextType::class, [
                'label' => 'Heading',
                'constraints' => [new NotBlank(), new Length(max: 255)],
            ])
            ->add('buttonLabel', TextType::class, ['label' => 'Button text', 'required' => false])
            ->add('buttonUrl', TextType::class, [
                'label' => 'Button URL or path',
                'required' => false,
                'help' => 'Use a local path such as /catalog or an http(s) URL.',
                'constraints' => [new SafeLink()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
