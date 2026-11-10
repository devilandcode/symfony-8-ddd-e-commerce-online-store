<?php

declare(strict_types=1);

namespace App\Tests\Customer\Application;

use App\Customer\Application\CreateUser\CreateUserDTO;
use App\Customer\Application\CreateUser\CreateUserHandler;
use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Application\UpdateUser\UpdateUserDTO;
use App\Customer\Application\UpdateUser\UpdateUserHandler;
use App\Customer\Application\ValueObject\PlainPassword;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\HashedPassword;
use App\Customer\Domain\ValueObject\Name;
use App\Customer\Domain\ValueObject\Surname;
use App\Customer\Infrastructure\Security\SecurityUser;
use App\Customer\Infrastructure\Security\SymfonyPasswordHasher;
use App\Shared\Application\Flusher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

final class UserHandlersTest extends TestCase
{
    public function testCreateHandlerHashesPasswordAndSavesTheNewUser(): void
    {
        $users = new InMemoryUserRepository();
        $hasher = new RecordingPasswordHasher();
        $flusher = new RecordingFlusher();
        $handler = new CreateUserHandler($users, $hasher, $flusher);

        $handler(new CreateUserDTO(
            new Name('  Alice  '),
            new Surname(' Smith '),
            new Email('ALICE@example.com'),
            new PlainPassword('password123'),
            UserRoleEnum::USER,
            UserStatusEnum::ACTIVE,
        ));

        self::assertSame('Alice', $users->saved[0]->getName()->getName());
        self::assertSame('Smith', $users->saved[0]->getSurname()->getSurname());
        self::assertSame('alice@example.com', $users->saved[0]->getEmail()->getEmail());
        self::assertSame('hashed:password123', $users->saved[0]->getPassword()->getHashedPassword());
        self::assertSame(UserStatusEnum::ACTIVE, $users->saved[0]->getStatus());
        self::assertSame(['password123'], $hasher->plainPasswords);
        self::assertSame(1, $flusher->commits);
    }

    public function testCreateHandlerRejectsAnEmailAlreadyInUse(): void
    {
        $users = new InMemoryUserRepository();
        $users->save($this->user('taken@example.com'));
        $handler = new CreateUserHandler($users, new RecordingPasswordHasher(), new RecordingFlusher());

        $this->expectException(UserEmailAlreadyInUse::class);
        $handler(new CreateUserDTO(
            new Name('Alice'),
            new Surname(),
            new Email('TAKEN@example.com'),
            new PlainPassword('password123'),
            UserRoleEnum::USER,
            UserStatusEnum::ACTIVE,
        ));
    }

    public function testUpdateHandlerChangesProfileAndOnlyChangesNonEmptyPassword(): void
    {
        $users = new InMemoryUserRepository();
        $user = $this->user('old@example.com');
        $users->save($user);
        $hasher = new RecordingPasswordHasher();
        $flusher = new RecordingFlusher();
        $handler = new UpdateUserHandler($users, $hasher, $flusher);

        $handler(new UpdateUserDTO(
            1,
            new Name('Updated'),
            new Surname(),
            new Email('new@example.com'),
            null,
            UserRoleEnum::ADMIN,
            UserStatusEnum::BLOCKED,
        ));

        self::assertSame('Updated', $user->getName()->getName());
        self::assertSame('new@example.com', $user->getEmail()->getEmail());
        self::assertSame(UserRoleEnum::ADMIN, $user->getRole());
        self::assertSame(UserStatusEnum::BLOCKED, $user->getStatus());
        self::assertSame('hashed:existing-password', $user->getPassword()->getHashedPassword());
        self::assertSame([], $hasher->plainPasswords);

        $handler(new UpdateUserDTO(
            1,
            new Name('Updated'),
            new Surname(),
            new Email('new@example.com'),
            new PlainPassword('replacement-password'),
            UserRoleEnum::ADMIN,
            UserStatusEnum::BLOCKED,
        ));

        self::assertSame('hashed:replacement-password', $user->getPassword()->getHashedPassword());
        self::assertSame(2, $flusher->commits);
        self::assertSame(['replacement-password'], $hasher->plainPasswords);
    }

    public function testSymfonyPasswordHasherUsesTheConfiguredUserHasherWithoutAnEntityInstance(): void
    {
        $factory = new PasswordHasherFactory([
            SecurityUser::class => ['algorithm' => 'auto'],
        ]);
        $passwordHasher = new SymfonyPasswordHasher($factory);
        $hash = $passwordHasher->hash(new PlainPassword('password123'));

        self::assertNotSame('password123', $hash->getHashedPassword());
        self::assertTrue($factory->getPasswordHasher(SecurityUser::class)->verify($hash->getHashedPassword(), 'password123'));
    }

    private function user(string $email): User
    {
        return User::register(
            new Name('Existing'),
            new Surname(),
            new Email($email),
            new HashedPassword('hashed:existing-password'),
            UserRoleEnum::USER,
            UserStatusEnum::ACTIVE,
            new \DateTime(),
        );
    }
}

final class RecordingFlusher implements Flusher
{
    public int $commits = 0;

    public function commit(): void
    {
        ++$this->commits;
    }
}

final class RecordingPasswordHasher implements PasswordHasherInterface
{
    /** @var list<string> */
    public array $plainPasswords = [];

    public function hash(PlainPassword $plainPassword): HashedPassword
    {
        $this->plainPasswords[] = $plainPassword->getPlainPassword();

        return new HashedPassword('hashed:'.$plainPassword->getPlainPassword());
    }
}

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var list<User> */
    public array $saved = [];

    public function findAll(): array
    {
        return $this->saved;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->saved as $user) {
            if ($user->getEmail()->getEmail() === $email->getEmail()) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->saved as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

        return null;
    }

    public function save(User $user): void
    {
        if (!\in_array($user, $this->saved, true)) {
            $id = new \ReflectionProperty(User::class, 'id');
            $id->setValue($user, count($this->saved) + 1);
            $this->saved[] = $user;
        }
    }

    public function delete(User $user): void
    {
        $this->saved = array_values(array_filter(
            $this->saved,
            static fn (User $savedUser): bool => $savedUser !== $user,
        ));
    }
}
