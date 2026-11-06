<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ){
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('surname');
        yield EmailField::new('email');
        yield TextField::new('plainPassword', 'Password')
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->onlyOnForms();

        yield ChoiceField::new('role')->setChoices([
            'User' => UserRoleEnum::USER,
            'Admin' => UserRoleEnum::ADMIN,
        ]);

        yield ChoiceField::new('status')->setChoices([
            'Active' => UserStatusEnum::ACTIVE,
            'Waiting' => UserStatusEnum::WAITING,
            'Blocked' => UserStatusEnum::BLOCKED,
        ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        if (empty($entityInstance->getPlainPassword())) {
            throw new \LogicException('A password is required when creating a user.');
        }

        $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()));
        $now = new \DateTime();
        $entityInstance->setCreatedAt($now);
        $entityInstance->setUpdatedAt($now);

        $this->userRepository->save($entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        if ('' !== $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()));
        }

        $entityInstance->setUpdatedAt(new \DateTime());
        $this->userRepository->save($entityInstance);
    }
}