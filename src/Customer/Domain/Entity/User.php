<?php

namespace App\Customer\Domain\Entity;


use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\Name;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: '`user`')]
#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $email_verified_at = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', enumType: UserRoleEnum::class, length: 255, nullable: false)]
    private ?UserRoleEnum $role = UserRoleEnum::USER;

    #[ORM\Column(type: 'string', enumType: UserStatusEnum::class, length: 255, nullable: true)]
    private ?UserStatusEnum $status = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $verify_token = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $verify_token_expire = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $remember_token = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTime $created_at = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTime $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public static function register(
        Name $name,
        ?string $surname,
        Email $email,
        string $passwordHash,
        UserRoleEnum $role,
        ?UserStatusEnum $status,
        \DateTime $now,
    ): self {
        $user = new self();
        $user->name = $name->getName();
        $user->surname = self::normalizeOptionalText($surname);
        $user->email = $email->getEmail();
        $user->password = $passwordHash;
        $user->role = $role;
        $user->status = $status;
        $user->created_at = $now;
        $user->updated_at = $now;

        return $user;
    }

    public function updateProfile(
        Name $name,
        ?string $surname,
        Email $email,
        UserRoleEnum $role,
        ?UserStatusEnum $status,
        \DateTime $updatedAt,
    ): void {
        $this->name = $name->getName();
        $this->surname = self::normalizeOptionalText($surname);
        $this->email = $email->getEmail();
        $this->role = $role;
        $this->status = $status;
        $this->updated_at = $updatedAt;
    }

    public function changePassword(string $passwordHash, \DateTime $updatedAt): void
    {
        if ('' === $passwordHash) {
            throw new \InvalidArgumentException('A password hash cannot be empty.');
        }

        $this->password = $passwordHash;
        $this->updated_at = $updatedAt;
    }

    private static function normalizeOptionalText(?string $value): ?string
    {
        $value = null === $value ? null : trim($value);

        if (null !== $value && mb_strlen($value) > 255) {
            throw new \InvalidArgumentException('A surname cannot exceed 255 characters.');
        }

        return '' === $value ? null : $value;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEmailVerifiedAt(): ?\DateTime
    {
        return $this->email_verified_at;
    }

    public function setEmailVerifiedAt(?\DateTime $email_verified_at): static
    {
        $this->email_verified_at = $email_verified_at;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return [$this->role === UserRoleEnum::ADMIN ? 'ROLE_ADMIN' : 'ROLE_USER'];
    }

    public function getRole(): ?UserRoleEnum
    {
        return $this->role;
    }

    public function getStatus(): ?UserStatusEnum
    {
        return $this->status;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verify_token;
    }

    public function setVerifyToken(?string $verify_token): static
    {
        $this->verify_token = $verify_token;

        return $this;
    }

    public function getVerifyTokenExpire(): ?\DateTimeImmutable
    {
        return $this->verify_token_expire;
    }

    public function setVerifyTokenExpire(?\DateTimeImmutable $verify_token_expire): static
    {
        $this->verify_token_expire = $verify_token_expire;

        return $this;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken(?string $remember_token): static
    {
        $this->remember_token = $remember_token;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime 
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

}
