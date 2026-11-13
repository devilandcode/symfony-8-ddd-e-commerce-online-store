<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\Setting\ContactsSettingsType;
use App\Backoffice\Infrastructure\Http\Form\Setting\ContentSettingsType;
use App\Backoffice\Infrastructure\Http\Form\Setting\MetricsSettingsType;
use App\StoreSettings\Application\SettingsDataFactory;
use App\StoreSettings\Application\AdminUseCases\SettingsManager\SettingsManager;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AdminRoute(path: '/settings', name: 'settings', options: ['methods' => ['GET', 'POST']])]
#[AsController]
final class SettingsController extends AbstractController
{
    public function __construct(
        private readonly SettingsManager $settings,
        private readonly SettingsDataFactory $dataFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $contactsForm = $this->createForm(ContactsSettingsType::class, $this->settings->get(SettingSectionKey::CONTACTS)->toArray());
        $contentForm = $this->createForm(ContentSettingsType::class, $this->settings->get(SettingSectionKey::CONTENT)->toArray());
        $metricsForm = $this->createForm(MetricsSettingsType::class, $this->settings->get(SettingSectionKey::METRICS)->toArray());
        $forms = [
            SettingSectionKey::CONTACTS->value => [$contactsForm, SettingSectionKey::CONTACTS],
            SettingSectionKey::CONTENT->value => [$contentForm, SettingSectionKey::CONTENT],
            SettingSectionKey::METRICS->value => [$metricsForm, SettingSectionKey::METRICS],
        ];

        foreach ($forms as $section => [$form, $key]) {
            $form->handleRequest($request);

            if (!$form->isSubmitted()) {
                continue;
            }

            if ($form->isValid()) {
                $data = $form->getData();
                if (!is_array($data)) {
                    throw new \LogicException('The settings form did not return an array.');
                }

                $this->settings->update($this->dataFactory->fromArray($key, $data));
                $this->addFlash('success', 'Settings saved.');

                return $this->redirectToRoute('admin_settings', ['tab' => $section]);
            }

            $activeTab = $section;

            return $this->render('backoffice/admin/settings.html.twig', [
                'contactsForm' => $contactsForm->createView(),
                'contentForm' => $contentForm->createView(),
                'metricsForm' => $metricsForm->createView(),
                'activeTab' => $activeTab,
            ]);
        }

        $activeTab = $request->query->getString('tab', SettingSectionKey::CONTACTS->value);
        if (!array_key_exists($activeTab, $forms)) {
            $activeTab = SettingSectionKey::CONTACTS->value;
        }

        return $this->render('backoffice/admin/settings.html.twig', [
            'contactsForm' => $contactsForm->createView(),
            'contentForm' => $contentForm->createView(),
            'metricsForm' => $metricsForm->createView(),
            'activeTab' => $activeTab,
        ]);
    }
}
