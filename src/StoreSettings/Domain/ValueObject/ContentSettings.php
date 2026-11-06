<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\ValueObject;

use App\StoreSettings\Domain\Enum\SettingSectionKey;

final readonly class ContentSettings implements SettingsData
{
    public function __construct(
        public string $privacy = '',
        public string $productsCount = '',
        public string $brandsCount = '',
        public string $newsCount = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
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

    public function toArray(): array
    {
        return [
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
}
