<?php

namespace App\Backoffice\Infrastructure\Console;

use App\Customer\Application\CreateUser\CreateUser;
use App\Customer\Application\CreateUser\CreateUserHandler;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Creates an active administrator account',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly CreateUserHandler $createUser,
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

        $name = trim((string) $io->ask('Admin name'));
        if ('' === $name) {
            $io->error('The name cannot be empty.');

            return Command::FAILURE;
        }

        $passwordQuestion = new Question('Admin password (input hidden):');
        $passwordQuestion->setHidden(true);
        $password = $io->askQuestion($passwordQuestion);
        if (!is_string($password) || mb_strlen($password) < 8) {
            $io->error('The password must contain at least 8 characters.');

            return Command::FAILURE;
        }

        try {
            ($this->createUser)(new CreateUser(
                $name,
                null,
                $email,
                $password,
                UserRoleEnum::ADMIN,
                UserStatusEnum::ACTIVE,
            ));
        } catch (UserEmailAlreadyInUse $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Administrator account created for %s.', $email));

        return Command::SUCCESS;
    }
}
