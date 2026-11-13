<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Puts a human collection-entry title on the form view.
 *
 * `entry_title` is either a list prefix ("Slide") or a map of entry name to
 * label ("new_arrivals" => "New arrivals"). Indexed entries are numbered from
 * 1. A non-empty `heading` value is appended for list entries. Associative
 * keys that are not in the map are humanized, so a later CollectionType does
 * not fall back to EasyAdmin's "Array (N items)" accordion title.
 */
final class CollectionEntryTitleExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['entry_title' => null]);
        $resolver->setAllowedTypes('entry_title', ['null', 'string', 'array']);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $spec = $options['entry_title'];
        if (!\is_array($spec) && !\is_string($spec)) {
            $spec = null;
        }

        $prototypeName = (string) $options['prototype_name'];
        foreach ($view->children as $child) {
            $this->apply($child, $form->getName(), $spec, $prototypeName);
        }

        $prototype = $view->vars['prototype'] ?? null;
        if ($prototype instanceof FormView) {
            $this->apply($prototype, $form->getName(), $spec, $prototypeName);
        }
    }

    /**
     * @param array<string, string>|string|null $spec
     */
    private function apply(FormView $view, string $collectionName, array|string|null $spec, string $prototypeName): void
    {
        $title = CollectionEntryTitle::resolve(
            $collectionName,
            (string) $view->vars['name'],
            $view->vars['value'] ?? null,
            $spec,
            $prototypeName,
        );

        $view->vars['collection_entry_title'] = $title->text;
        $view->vars['collection_entry_title_prefix'] = $title->prefix;
        $view->vars['collection_entry_prototype'] = $title->prototype;
        $view->vars['collection_entry_heading'] = $title->heading;
        $view->vars['collection_entry_index_placeholder'] = $title->indexPlaceholder;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CollectionType::class];
    }
}
