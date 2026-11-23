<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\ValueObject;

use App\StoreSettings\Domain\Enum\SettingSectionKey;

final readonly class ContentSettings implements SettingsData
{
    public function __construct(
        public string $copyright = '',
        public string $privacy = '',
        public string $productsCount = '',
        public string $brandsCount = '',
        public string $newsCount = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::stringValue($data, 'copyright'),
            self::stringValue($data, 'privacy'),
            self::stringValue($data, 'products_count'),
            self::stringValue($data, 'brands_count'),
            self::stringValue($data, 'news_count'),
        );
    }

    public function sectionKey(): SettingSectionKey
    {
        return SettingSectionKey::CONTENT;
    }

    public function productsLimit(): int
    {
        return self::nonNegativeInteger($this->productsCount, 'products_count');
    }

    public function brandsLimit(): int
    {
        return self::nonNegativeInteger($this->brandsCount, 'brands_count');
    }

    public function toArray(): array
    {
        return [
            'copyright' => $this->copyright,
            'privacy' => $this->privacy,
            'products_count' => $this->productsCount,
            'brands_count' => $this->brandsCount,
            'news_count' => $this->newsCount,
        ];
    }

    private static function stringValue(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('The "%s" setting must be a string.', $key));
        }

        return $value;
    }

    private static function nonNegativeInteger(string $value, string $key): int
    {
        $value = trim($value);
        if ('' === $value) {
            return 0;
        }

        if (1 !== preg_match('/\A[0-9]+\z/', $value)) {
            throw new \InvalidArgumentException(sprintf('The "%s" setting must be a non-negative integer.', $key));
        }

        $normalizedValue = ltrim($value, '0');
        if ('' === $normalizedValue) {
            return 0;
        }

        $limit = filter_var($normalizedValue, FILTER_VALIDATE_INT);
        if (false === $limit) {
            throw new \InvalidArgumentException(sprintf('The "%s" setting exceeds the supported integer range.', $key));
        }

        return $limit;
    }
}
