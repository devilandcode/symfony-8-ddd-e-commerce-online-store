<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\User\UserFormData;
use App\Backoffice\Infrastructure\Http\Form\User\UserType;
use App\Customer\Application\CreateUser\CreateUserDTO;
use App\Customer\Application\CreateUser\CreateUserHandler;
use App\Customer\Application\DeleteUser\DeleteUserHandler;
use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\Exception\UserNotFound;
use App\Customer\Application\FindUser\FindUserHandler;
use App\Customer\Application\ListUsers\ListUsersHandler;
use App\Customer\Application\Output\UserViewDTO;
use App\Customer\Application\UpdateUser\UpdateUserDTO;
use App\Customer\Application\UpdateUser\UpdateUserHandler;
use App\Customer\Application\ValueObject\PlainPassword;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\Name;
use App\Customer\Domain\ValueObject\Surname;
use App\Customer\Infrastructure\Security\SecurityUser;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/users', name: 'users')]
final class UserManagementController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListUsersHandler $listUsers): Response
    {
        return $this->render('backoffice/admin/users/index.html.twig', [
            'users' => $listUsers(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateUserHandler $createUser): Response
    {
        $data = new UserFormData();
        $form = $this->createForm(UserType::class, $data, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createUser(new CreateUserDTO(
                    new Name($data->name),
                    new Surname($data->surname),
                    new Email($data->email),
                    new PlainPassword($data->plainPassword ?? throw new \LogicException('A password is required to create a user.')),
                    $data->role,
                    $this->requireStatus($data->status),
                ));
            } catch (UserEmailAlreadyInUse $exception) {
                $form->get('email')->addError(new FormError($exception->getMessage()));

                return $this->renderUserForm($form, 'Create user');
            } catch (\InvalidArgumentException $exception) {
                $form->addError(new FormError($exception->getMessage()));

                return $this->renderUserForm($form, 'Create user');
            }

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->renderUserForm($form, 'Create user');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindUserHandler $findUser,
        UpdateUserHandler $updateUser,
    ): Response {
        try {
            $user = $findUser($id);
        } catch (UserNotFound) {
            throw $this->createNotFoundException();
        }

        $data = $this->formDataFromUser($user);
        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updateUser(new UpdateUserDTO(
                    $id,
                    new Name($data->name),
                    new Surname($data->surname),
                    new Email($data->email),
                    null === $data->plainPassword || '' === $data->plainPassword
                        ? null
                        : new PlainPassword($data->plainPassword),
                    $data->role,
                    $this->requireStatus($data->status),
                ));
            } catch (UserEmailAlreadyInUse $exception) {
                $form->get('email')->addError(new FormError($exception->getMessage()));

                return $this->renderUserForm($form, 'Edit user');
            } catch (\InvalidArgumentException $exception) {
                $form->addError(new FormError($exception->getMessage()));

                return $this->renderUserForm($form, 'Edit user');
            }

            $this->addFlash('success', 'User updated.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->renderUserForm($form, 'Edit user');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteUserHandler $deleteUser): Response
    {
        if (!$this->isCsrfTokenValid('delete-user-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof SecurityUser && $currentUser->getId() === $id) {
            throw $this->createAccessDeniedException('You cannot delete your own account.');
        }

        try {
            $deleteUser($id);
        } catch (UserNotFound) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'User deleted.');

        return $this->redirectToRoute('admin_users_index');
    }

    private function renderUserForm(\Symfony\Component\Form\FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/users/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    private function formDataFromUser(UserViewDTO $user): UserFormData
    {
        $data = new UserFormData();
        $data->name = $user->name;
        $data->surname = $user->surname;
        $data->email = $user->email;
        $data->role = $user->role;
        $data->status = $user->status;

        return $data;
    }

    private function requireStatus(?UserStatusEnum $status): UserStatusEnum
    {
        if (null === $status) {
            throw new \InvalidArgumentException('A user status is required.');
        }

        return $status;
    }
}
