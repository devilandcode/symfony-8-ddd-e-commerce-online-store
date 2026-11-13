<?php

declare(strict_types=1);

namespace App\StoreSettings\Application\FrontUseCases\ListContactSettings;

use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;

final readonly class ListContactSettingsHandler
{
    public function __construct(private SettingSectionRepositoryInterface $contactSettingRepository)
    {}

    public function __invoke(): array
    {
        return $this->contactSettingRepository->getContactSetting();
    }
}
