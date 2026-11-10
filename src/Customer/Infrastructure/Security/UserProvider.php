<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Security;

use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Customer\Domain\ValueObject\Email;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<SecurityUser>
 */
final readonly class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function loadUserByIdentifier(string $identifier): SecurityUser
    {
        try {
            $email = new Email($identifier);
        } catch (\InvalidArgumentException $exception) {
            $notFound = new UserNotFoundException(sprintf('User "%s" not found.', $identifier), previous: $exception);
            $notFound->setUserIdentifier($identifier);

            throw $notFound;
        }

        $user = $this->users->findByEmail($email);
        if (null === $user) {
            $notFound = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $notFound->setUserIdentifier($identifier);

            throw $notFound;
        }

        return SecurityUser::fromDomainUser($user);
    }

    public function refreshUser(UserInterface $user): SecurityUser
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class || is_subclass_of($class, SecurityUser::class);
    }
}
