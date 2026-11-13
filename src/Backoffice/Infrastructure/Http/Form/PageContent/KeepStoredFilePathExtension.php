<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class KeepStoredFilePathExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['keep_stored_path' => false]);
        $resolver->setAllowedTypes('keep_stored_path', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['keep_stored_path']) {
            $builder->addModelTransformer(new StoredFilePathTransformer());
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$options['keep_stored_path']) {
            return;
        }

        $path = $form->getData();
        if (!\is_string($path) || '' === $path) {
            return;
        }

        $view->vars['stored_path'] = $path;
        $view->vars['help'] = 'Current image: '.$path;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FileType::class];
    }
}
