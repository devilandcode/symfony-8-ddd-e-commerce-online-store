<?php

declare(strict_types=1);

namespace App\Tests\Customer\Application;

use App\Customer\Application\CreateUser\CreateUser;
use App\Customer\Application\CreateUser\CreateUserHandler;
use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Application\UpdateUser\UpdateUser;
use App\Customer\Application\UpdateUser\UpdateUserHandler;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Customer\Infrastructure\Security\SymfonyPasswordHasher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class UserHandlersTest extends TestCase
{
    public function testCreateHandlerHashesPasswordAndSavesTheNewUser(): void
    {
        $users = new InMemoryUserRepository();
        $hasher = new RecordingPasswordHasher();
        $handler = new CreateUserHandler($users, $hasher);

        $handler(new CreateUser(
            '  Alice  ',
            ' Smith ',
            'ALICE@example.com',
            'password123',
            UserRoleEnum::USER,
            UserStatusEnum::ACTIVE,
        ));

        self::assertSame('Alice', $users->saved[0]->getName());
        self::assertSame('Smith', $users->saved[0]->getSurname());
        self::assertSame('alice@example.com', $users->saved[0]->getEmail());
        self::assertSame('hashed:password123', $users->saved[0]->getPassword());
        self::assertSame(['password123'], $hasher->plainPasswords);
    }

    public function testCreateHandlerRejectsAnEmailAlreadyInUse(): void
    {
        $users = new InMemoryUserRepository();
        $users->save($this->user('taken@example.com'));
        $handler = new CreateUserHandler($users, new RecordingPasswordHasher());

        $this->expectException(UserEmailAlreadyInUse::class);
        $handler(new CreateUser(
            'Alice',
            null,
            'TAKEN@example.com',
            'password123',
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
        $handler = new UpdateUserHandler($users, $hasher);

        $handler(new UpdateUser(
            1,
            'Updated',
            null,
            'new@example.com',
            null,
            UserRoleEnum::ADMIN,
            UserStatusEnum::BLOCKED,
        ));

        self::assertSame('Updated', $user->getName());
        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame(UserRoleEnum::ADMIN, $user->getRole());
        self::assertSame(UserStatusEnum::BLOCKED, $user->getStatus());
        self::assertSame('hashed:existing-password', $user->getPassword());
        self::assertSame([], $hasher->plainPasswords);

        $handler(new UpdateUser(
            1,
            'Updated',
            null,
            'new@example.com',
            'replacement-password',
            UserRoleEnum::ADMIN,
            UserStatusEnum::BLOCKED,
        ));

        self::assertSame('hashed:replacement-password', $user->getPassword());
        self::assertSame(['replacement-password'], $hasher->plainPasswords);
    }

    public function testSymfonyPasswordHasherUsesTheConfiguredUserHasherWithoutAnEntityInstance(): void
    {
        $factory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);
        $passwordHasher = new SymfonyPasswordHasher($factory);
        $hash = $passwordHasher->hash('password123');

        self::assertNotSame('password123', $hash);
        self::assertTrue($factory->getPasswordHasher(User::class)->verify($hash, 'password123'));
    }

    private function user(string $email): User
    {
        return User::register(
            'Existing',
            null,
            $email,
            'hashed:existing-password',
            UserRoleEnum::USER,
            UserStatusEnum::ACTIVE,
            new \DateTime(),
        );
    }
}

final class RecordingPasswordHasher implements PasswordHasherInterface
{
    /** @var list<string> */
    public array $plainPasswords = [];

    public function hash(string $plainPassword): string
    {
        $this->plainPasswords[] = $plainPassword;

        return 'hashed:'.$plainPassword;
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

    public function findByEmail(string $email): ?User
    {
        foreach ($this->saved as $user) {
            if ($user->getEmail() === $email) {
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
