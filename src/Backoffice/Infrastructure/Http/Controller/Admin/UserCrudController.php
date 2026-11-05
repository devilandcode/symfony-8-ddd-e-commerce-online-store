<?php

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
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
}