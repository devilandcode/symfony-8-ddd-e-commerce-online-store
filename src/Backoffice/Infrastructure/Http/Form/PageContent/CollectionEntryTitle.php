<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

/**
 * Collapsed title for one CollectionType entry.
 *
 * Symfony 8.1 does not call a callable form `label`. EasyAdmin's accordion
 * otherwise prints array data as "Array (N items)".
 */
final readonly class CollectionEntryTitle
{
    private const HEADING_SEPARATOR = " \u{2014} ";

    private function __construct(
        public string $text,
        public string $prefix,
        public bool $prototype,
        public string $heading,
        public string $indexPlaceholder,
    ) {
    }

    /**
     * @param array<string, string>|string|null $spec indexed prefix, or a map of entry name to label
     */
    public static function resolve(string $collectionName, string $entryName, mixed $data, array|string|null $spec, string $prototypeName = '__name__'): self
    {
        $mapped = self::mappedLabel($spec, $entryName);
        if (null !== $mapped) {
            return new self($mapped, '', false, '', '');
        }

        $prototype = $entryName === $prototypeName;
        if ($prototype || 1 === preg_match('/^\d+$/', $entryName)) {
            $prefix = self::prefix($collectionName, $spec);
            $heading = self::heading($data);
            if ($prototype) {
                return new self($prefix, $prefix, true, $heading, $prototypeName);
            }

            $text = $prefix.' '.((int) $entryName + 1);
            if ('' !== $heading) {
                $text .= self::HEADING_SEPARATOR.$heading;
            }

            return new self($text, $prefix, false, $heading, '');
        }

        return new self(self::humanize($entryName), '', false, '', '');
    }

    /**
     * @param array<string, string>|string|null $spec
     */
    private static function mappedLabel(array|string|null $spec, string $entryName): ?string
    {
        if (!\is_array($spec) || !isset($spec[$entryName]) || !\is_string($spec[$entryName])) {
            return null;
        }

        $label = trim($spec[$entryName]);

        return '' === $label ? null : $label;
    }

    /**
     * @param array<string, string>|string|null $spec
     */
    private static function prefix(string $collectionName, array|string|null $spec): string
    {
        if (\is_string($spec)) {
            $spec = trim($spec);
            if ('' !== $spec) {
                return $spec;
            }
        }

        $name = '' === $collectionName ? 'item' : $collectionName;

        return self::humanize(self::singularize($name));
    }

    private static function heading(mixed $data): string
    {
        if (!\is_array($data) || !isset($data['heading']) || !\is_string($data['heading'])) {
            return '';
        }

        return trim($data['heading']);
    }

    private static function singularize(string $value): string
    {
        if (str_ends_with($value, 'ies') && \strlen($value) > 3) {
            return substr($value, 0, -3).'y';
        }

        if (str_ends_with($value, 's') && !str_ends_with($value, 'ss') && \strlen($value) > 1) {
            return substr($value, 0, -1);
        }

        return $value;
    }

    private static function humanize(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        if ('' === $value) {
            return 'Item';
        }

        return ucfirst($value);
    }
}
