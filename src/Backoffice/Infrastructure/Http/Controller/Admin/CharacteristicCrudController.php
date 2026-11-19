<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Characteristic\CharacteristicFormData;
use App\Backoffice\Infrastructure\Http\Form\Characteristic\CharacteristicType;
use App\Catalog\Application\Characteristic\AdminUseCases\CreateChar\CreateCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\DeleteChar\DeleteCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharAlreadyInUseException;
use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharacteristicNotFoundException;
use App\Catalog\Application\Characteristic\AdminUseCases\FindChar\FindCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\Input\CharDataDTO;
use App\Catalog\Application\Characteristic\AdminUseCases\ListChars\ListCharsHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\Output\CharacteristicViewDTO;
use App\Catalog\Application\Characteristic\AdminUseCases\UpdateChar\UpdateCharHandler;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminRoute(path: '/characteristics', name: 'characteristics')]
final class CharacteristicCrudController extends AbstractController
{
    #[AdminRoute('/', name: 'index', options: ['methods' => ['GET']])]
    public function index(ListCharsHandler $listCharacteristics): Response
    {
        return $this->render('backoffice/admin/characteristics/index.html.twig', [
            'characteristics' => $listCharacteristics(),
        ]);
    }

    #[AdminRoute('/new', name: 'new', options: ['methods' => ['GET', 'POST']])]
    public function new(Request $request, CreateCharHandler $createCharacteristic): Response
    {
        $form = $this->createForm(CharacteristicType::class, new CharacteristicFormData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createCharacteristic($this->dataFromForm($form->getData()));
            } catch (CharAlreadyInUseException $exception) {
                $form->get('title')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Create characteristic');
            } catch (\InvalidArgumentException $exception) {
                $form->get('title')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Create characteristic');
            }

            $this->addFlash('success', 'Characteristic created.');

            return $this->redirectToRoute('admin_characteristics_index');
        }

        return $this->renderForm($form, 'Create characteristic');
    }

    #[AdminRoute('/{id}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $id,
        Request $request,
        FindCharHandler $findCharacteristic,
        UpdateCharHandler $updateCharacteristic,
    ): Response {
        try {
            $characteristic = $findCharacteristic($id);
        } catch (CharacteristicNotFoundException) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CharacteristicType::class, $this->formDataFromCharacteristic($characteristic));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updateCharacteristic($id, $this->dataFromForm($form->getData()));
            } catch (CharAlreadyInUseException $exception) {
                $form->get('title')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Edit characteristic');
            } catch (\InvalidArgumentException $exception) {
                $form->get('title')->addError(new FormError($exception->getMessage()));

                return $this->renderForm($form, 'Edit characteristic');
            } catch (CharacteristicNotFoundException) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('success', 'Characteristic updated.');

            return $this->redirectToRoute('admin_characteristics_index');
        }

        return $this->renderForm($form, 'Edit characteristic');
    }

    #[AdminRoute('/{id}/delete', name: 'delete', options: ['methods' => ['POST']])]
    public function delete(int $id, Request $request, DeleteCharHandler $deleteCharacteristic): Response
    {
        if (!$this->isCsrfTokenValid('delete-characteristic-'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        try {
            $deleteCharacteristic($id);
        } catch (CharacteristicNotFoundException) {
            throw $this->createNotFoundException();
        }

        $this->addFlash('success', 'Characteristic deleted.');

        return $this->redirectToRoute('admin_characteristics_index');
    }

    private function dataFromForm(CharacteristicFormData $data): CharDataDTO
    {
        $measure = null === $data->measure ? null : trim($data->measure);

        return new CharDataDTO(
            trim($data->title),
            '' === $measure ? null : $measure,
            $data->position,
            $data->isActive,
        );
    }

    private function formDataFromCharacteristic(CharacteristicViewDTO $characteristic): CharacteristicFormData
    {
        $data = new CharacteristicFormData();
        $data->title = $characteristic->title;
        $data->measure = $characteristic->measure;
        $data->position = $characteristic->position;
        $data->isActive = $characteristic->isActive;

        return $data;
    }

    private function renderForm(FormInterface $form, string $title): Response
    {
        return $this->render('backoffice/admin/characteristics/form.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
}
