<?php

declare(strict_types=1);

namespace App\Customer\Application\UpdateUser;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\Name;

final readonly class UpdateUserDTO
{
    public function __construct(
        public int $id,
        public Name $name,
        public ?string $surname,
        public Email $email,
        public ?string $plainPassword,
        public UserRoleEnum $role,
        public ?UserStatusEnum $status,
    ) {
    }
}
