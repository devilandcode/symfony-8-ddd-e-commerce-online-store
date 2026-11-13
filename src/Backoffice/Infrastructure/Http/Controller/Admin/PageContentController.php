<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Controller\Admin;

use App\Backoffice\Infrastructure\Http\Form\PageContent\Exception\PageContentFormNotRegistered;
use App\Backoffice\Infrastructure\Http\Form\PageContent\PageContentFormRegistry;
use App\Backoffice\Infrastructure\Storage\PageContentPayloadNormalizer;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\AdminUseCases\FindPageContent\FindPageContentHandler;
use App\ContentManagement\Application\Page\AdminUseCases\SavePageContent\SavePageContentHandler;
use App\ContentManagement\Domain\ValueObject\PageContentData;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminRoute(path: '/page-content', name: 'page_content')]
#[IsGranted('ROLE_ADMIN')]
final class PageContentController extends AbstractController
{
    #[AdminRoute('/{pageId}/edit', name: 'edit', options: ['methods' => ['GET', 'POST']])]
    public function edit(
        int $pageId,
        Request $request,
        FindPageContentHandler $findPageContent,
        SavePageContentHandler $savePageContent,
        PageContentFormRegistry $formRegistry,
        PageContentPayloadNormalizer $payloadNormalizer,
    ): Response {
        try {
            $editor = $findPageContent($pageId);
        } catch (PageNotFound) {
            throw $this->createNotFoundException('Page not found.');
        }

        try {
            $formType = $formRegistry->typeFor($editor->pageSlug);
        } catch (PageContentFormNotRegistered $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
        }

        $form = $this->createForm($formType, $editor->payload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = $form->getData();
            if (!\is_array($submitted)) {
                throw new \UnexpectedValueException('Page content form data must be an array.');
            }

            $normalized = $payloadNormalizer->normalize($editor->pageSlug, $submitted, $editor->payload);
            $savePageContent($pageId, PageContentData::fromArray($normalized));
            $payloadNormalizer->removeUnused($editor->payload, $normalized);

            $this->addFlash('success', 'Page content saved.');

            return $this->redirectToRoute('admin_page_content_edit', ['pageId' => $pageId]);
        }

        return $this->render('backoffice/admin/page_content/edit.html.twig', [
            'form' => $form->createView(),
            'pageId' => $pageId,
            'pageTitle' => $editor->pageTitle,
        ]);
    }
}
