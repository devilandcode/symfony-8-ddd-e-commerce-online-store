<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\ValueObject;

use App\StoreSettings\Domain\Enum\SettingSectionKey;

final readonly class ContactsSettings implements SettingsData
{
    public function __construct(

        public string $email = '',
        public string $email_callback = '',
        public string $companyInfo = '',
        public string $footerShop1 = '',
        public string $footerShop2 = '',
        public string $instagram = '',
        public string $facebook = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
          
            self::stringValue($data, 'email'),
            self::stringValue($data, 'email_callback'),
            self::stringValue($data, 'company_info'),
            self::stringValue($data, 'footer_shop_1'),
            self::stringValue($data, 'footer_shop_2'),
            self::stringValue($data, 'instagram'),
            self::stringValue($data, 'facebook'),
        );
    }

    public function sectionKey(): SettingSectionKey
    {
        return SettingSectionKey::CONTACTS;
    }

    public function toArray(): array
    {
        return [

            'email' => $this->email,
            'email_callback' => $this->email_callback,
            'company_info' => $this->companyInfo,
            'footer_shop_1' => $this->footerShop1,
            'footer_shop_2' => $this->footerShop2,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
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
