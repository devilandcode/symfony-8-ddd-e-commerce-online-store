<?php

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Characteristic;

interface CharacteristicRepositoryInterface
{
    public function findBy(string $title, ?string $measure): ?Characteristic;

    public function save(Characteristic $characteristic): void;

    public function delete(Characteristic $characteristic): void;
}
