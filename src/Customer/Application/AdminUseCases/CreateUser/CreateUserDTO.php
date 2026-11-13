<?php

declare(strict_types=1);

namespace App\Customer\Application\AdminUseCases\CreateUser;

use App\Customer\Application\ValueObject\PlainPassword;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\Name;
use App\Customer\Domain\ValueObject\Surname;

final readonly class CreateUserDTO
{
    public function __construct(
        public Name $name,
        public Surname $surname,
        public Email $email,
        public PlainPassword $plainPassword,
        public UserRoleEnum $role,
        public UserStatusEnum $status,
    ) {}
}
