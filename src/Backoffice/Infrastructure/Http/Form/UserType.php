<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('surname', TextType::class, ['required' => false])
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                'required' => $options['is_new'],
                'always_empty' => true,
                'help' => $options['is_new']
                    ? 'At least 8 characters.'
                    : 'Leave blank to keep the current password.',
            ])
            ->add('role', EnumType::class, [
                'class' => UserRoleEnum::class,
                'choice_label' => static fn (UserRoleEnum $role): string => match ($role) {
                    UserRoleEnum::ADMIN => 'Administrator',
                    UserRoleEnum::USER => 'User',
                },
            ])
            ->add('status', EnumType::class, [
                'class' => UserStatusEnum::class,
                'choice_label' => static fn (UserStatusEnum $status): string => ucfirst($status->value),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserFormData::class,
            'is_new' => false,
            'validation_groups' => static function (FormInterface $form): array {
                $data = $form->getData();

                if ($form->getConfig()->getOption('is_new')) {
                    return ['Default', 'create'];
                }

                if ($data instanceof UserFormData && null !== $data->plainPassword && '' !== $data->plainPassword) {
                    return ['Default', 'password_change'];
                }

                return ['Default'];
            },
        ]);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}
