<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Security;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Infrastructure\Security\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminStatusUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof SecurityUser) {
            return;
        }

        $this->assertAdminAccount($user);
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }

    private function assertAdminAccount(SecurityUser $user): void
    {
        if (UserStatusEnum::ACTIVE !== $user->getStatus()) {
            throw new CustomUserMessageAccountStatusException('This account is not active.');
        }

        if (UserRoleEnum::ADMIN !== $user->getRole()) {
            throw new CustomUserMessageAccountStatusException('This account hasn no admin permissions');
        }
    }
}
