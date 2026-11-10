<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Security;

use App\Customer\Domain\Enum\UserStatusEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountStatusUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof SecurityUser) {
            return;
        }

        $this->rejectInactiveAccount($user);
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }

    private function rejectInactiveAccount(SecurityUser $user): void
    {
        if (UserStatusEnum::ACTIVE !== $user->getStatus()) {
            throw new CustomUserMessageAccountStatusException('This account is not active.');
        }
    }
}
