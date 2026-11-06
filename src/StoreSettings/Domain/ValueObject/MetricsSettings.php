<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\ValueObject;

use App\StoreSettings\Domain\Enum\SettingSectionKey;

final readonly class MetricsSettings implements SettingsData
{
    public function __construct(
        public string $head = '',
        public string $bodyStart = '',
        public string $bodyEnd = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::stringValue($data, 'head'),
            self::stringValue($data, 'body_start'),
            self::stringValue($data, 'body_end'),
        );
    }

    public function sectionKey(): SettingSectionKey
    {
        return SettingSectionKey::METRICS;
    }

    public function toArray(): array
    {
        return [
            'head' => $this->head,
            'body_start' => $this->bodyStart,
            'body_end' => $this->bodyEnd,
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
