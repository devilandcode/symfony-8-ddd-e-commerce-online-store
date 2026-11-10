<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

final class UserFormData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name = '';

    #[Assert\Length(max: 255)]
    public ?string $surname = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 8, groups: ['create', 'password_change'])]
    public ?string $plainPassword = null;

    public UserRoleEnum $role = UserRoleEnum::USER;

    #[Assert\NotNull]
    public ?UserStatusEnum $status = UserStatusEnum::ACTIVE;
}
