<?php

declare(strict_types=1);

namespace App\Customer\Domain\Entity;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\HashedPassword;
use App\Customer\Domain\ValueObject\Name;
use App\Customer\Domain\ValueObject\Surname;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`user`')]
#[ORM\Entity]
class User
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Embedded(class: Name::class, columnPrefix: false)]
    private Name $name;

    #[ORM\Embedded(class: Surname::class, columnPrefix: false)]
    private Surname $surname;

    #[ORM\Embedded(class: Email::class, columnPrefix: false)]
    private Email $email;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $email_verified_at = null;

    #[ORM\Embedded(class: HashedPassword::class, columnPrefix: false)]
    private HashedPassword $password;

    #[ORM\Column(type: 'string', enumType: UserRoleEnum::class, length: 255, nullable: false)]
    private UserRoleEnum $role = UserRoleEnum::USER;

    #[ORM\Column(type: 'string', enumType: UserStatusEnum::class, length: 255, nullable: true)]
    private ?UserStatusEnum $status = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $verify_token = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $verify_token_expire = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $remember_token = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $updated_at;

    private function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public static function register(
        Name $name,
        Surname $surname,
        Email $email,
        HashedPassword $passwordHash,
        UserRoleEnum $role,
        UserStatusEnum $status,
        \DateTime $now,
    ): self {
        $user = new self();
        $user->name = $name;
        $user->surname = $surname;
        $user->email = $email;
        $user->password = $passwordHash;
        $user->role = $role;
        $user->status = $status;
        $user->created_at = $now;
        $user->updated_at = $now;

        return $user;
    }

    public function updateProfile(
        Name $name,
        Surname $surname,
        Email $email,
        UserRoleEnum $role,
        UserStatusEnum $status,
        \DateTime $updatedAt,
    ): void {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
        $this->updated_at = $updatedAt;
    }

    public function changePassword(HashedPassword $passwordHash, \DateTime $updatedAt): void
    {
        $this->password = $passwordHash;
        $this->updated_at = $updatedAt;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getSurname(): Surname
    {
        return $this->surname;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEmailVerifiedAt(): ?\DateTime
    {
        return $this->email_verified_at;
    }

    public function getPassword(): HashedPassword
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verify_token;
    }

    public function getVerifyTokenExpire(): ?\DateTimeImmutable
    {
        return $this->verify_token_expire;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }
}
