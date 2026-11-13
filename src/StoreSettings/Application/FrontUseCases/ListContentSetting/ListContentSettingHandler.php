<?php

declare(strict_types=1);

namespace App\StoreSettings\Application\FrontUseCases\ListContentSetting;

use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;

final readonly class ListContentSettingHandler
{
    public function __construct(private SettingSectionRepositoryInterface $contentSettingRepository)
    {}

    public function __invoke(): array
    {
        return $this->contentSettingRepository->getContentSetting();
    }
}
