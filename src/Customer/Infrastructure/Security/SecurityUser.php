<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Security;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private int $id,
        private string $email,
        private string $password,
        private UserRoleEnum $role,
        private ?UserStatusEnum $status,
    ) {
    }

    public static function fromDomainUser(User $user): self
    {
        return new self(
            $user->getId() ?? throw new \LogicException('A persisted user is required to create a security user.'),
            $user->getEmail()->getEmail(),
            $user->getPassword()->getHashedPassword(),
            $user->getRole(),
            $user->getStatus(),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return [UserRoleEnum::ADMIN === $this->role ? 'ROLE_ADMIN' : 'ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): UserRoleEnum
    {
        return $this->role;
    }

    public function getStatus(): ?UserStatusEnum
    {
        return $this->status;
    }
}
