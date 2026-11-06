<?php

namespace App\Customer\Application\UpdateUser;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;

<?php

final readonly class UpdateUser
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $surname,
        public string $email,
        public ?string $plainPassword,
        public UserRoleEnum $role,
        public ?UserStatusEnum $status,
    ) {}
}
