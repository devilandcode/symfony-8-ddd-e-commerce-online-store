<?php

declare(strict_types=1);

namespace App\StoreSettings\Application\FrontUseCases\ListContentSetting;

use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;

final readonly class ListContentSettingHandler
{
    public function __construct(private SettingSectionRepositoryInterface $contentSettingRepository)
    {}

    public function __invoke(): ContentSettings
    {
        $section = $this->contentSettingRepository->findByKey(SettingSectionKey::CONTENT);

        return ContentSettings::fromArray($section?->getPayload() ?? []);
    }
}
