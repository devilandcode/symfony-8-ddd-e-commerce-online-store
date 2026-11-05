<?php

namespace App\Backoffice\Infrastructure\Console;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Infrastructure\Persistence\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Creates an active administrator account',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = trim((string) $io->ask('Admin email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Enter a valid email address.');

            return Command::FAILURE;
        }

        if (null !== $this->userRepository->findOneBy(['email' => $email])) {
            $io->error('A user with that email already exists.');

            return Command::FAILURE;
        }

        $name = trim((string) $io->ask('Admin name'));
        if ('' === $name) {
            $io->error('The name cannot be empty.');

            return Command::FAILURE;
        }

        $passwordQuestion = new Question('Admin password (input hidden):');
        $passwordQuestion->setHidden(true);
        $password = $io->askQuestion($passwordQuestion);
        if (!is_string($password) || '' === $password) {
            $io->error('The password cannot be empty.');

            return Command::FAILURE;
        }

        $admin = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($admin, $password))
            ->setRole(UserRoleEnum::ADMIN)
            ->setStatus(UserStatusEnum::ACTIVE)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Administrator account created for %s.', $email));

        return Command::SUCCESS;
    }
}
