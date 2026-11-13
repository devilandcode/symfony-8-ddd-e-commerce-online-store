<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\PageContent\HomePageContentType;
use App\Backoffice\Infrastructure\Http\Form\PageContent\PageContentFormRegistry;
use App\ContentManagement\Application\Page\AdminUseCases\Output\PageViewDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageContentAdminUiTest extends KernelTestCase
{
    public function testCollapsedEntriesRenderHumanTitles(): void
    {
        $html = $this->renderPageContentForm(HomePageContentType::class, [
            'slides' => [
                [
                    'heading' => 'Summer',
                    'description' => '',
                    'buttonLabel' => 'Shop',
                    'buttonUrl' => '/catalog',
                    'desktopImage' => 'uploads/page-content/0123456789abcdef0123456789abcdef.jpg',
                    'mobileImage' => null,
                    'isActive' => false,
                ],
                [
                    'heading' => '',
                    'description' => '',
                    'buttonLabel' => '',
                    'buttonUrl' => '',
                    'desktopImage' => null,
                    'mobileImage' => null,
                    'isActive' => false,
                ],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'Just in', 'buttonLabel' => 'See more', 'buttonUrl' => ''],
                'popular_brands' => ['heading' => 'Brands', 'buttonLabel' => 'All brands', 'buttonUrl' => ''],
                'price_hits' => ['heading' => 'Offers', 'buttonLabel' => 'See more', 'buttonUrl' => ''],
            ],
        ]);

        self::assertStringContainsString('page-content-entry-title">'."Slide 1 \u{2014} Summer", $html);
        self::assertStringContainsString('page-content-entry-title">Slide 2', $html);
        self::assertStringContainsString('page-content-entry-title">New arrivals', $html);
        self::assertStringContainsString('page-content-entry-title">Popular brands', $html);
        self::assertStringContainsString('page-content-entry-title">Price hits', $html);
        self::assertMatchesRegularExpression('/data-entry-prefix=(?:"|&quot;)Slide(?:"|&quot;)/', $html);
        self::assertMatchesRegularExpression('/data-entry-index=(?:"|&quot;)__name__(?:"|&quot;)/', $html);
        self::assertStringContainsString('index + 1', $html);
        self::assertStringContainsString('Heading', $html);
        self::assertStringContainsString('Current image: uploads/page-content/0123456789abcdef0123456789abcdef.jpg', $html);
        self::assertStringContainsString('field-collection-add-button', $html);
        self::assertStringContainsString('field-collection-delete-button', $html);
        self::assertStringNotContainsString('Array (', $html);
        self::assertStringNotContainsString('__name__label__', $html);
    }

    public function testACollectionWithoutAnExplicitTitleDoesNotRenderArray(): void
    {
        self::bootKernel();
        $this->pushRequest('/admin/page-content/1/edit');

        $form = static::getContainer()->get('form.factory')->createBuilder()
            ->add('banners', CollectionType::class, [
                'entry_type' => BannerCollectionEntryType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->getForm();
        $form->setData([
            'banners' => [
                ['heading' => 'Launch'],
                [],
            ],
        ]);

        $html = $this->renderForm($form->createView());

        self::assertStringContainsString('page-content-entry-title">'."Banner 1 \u{2014} Launch", $html);
        self::assertStringContainsString('page-content-entry-title">Banner 2', $html);
        self::assertStringNotContainsString('Array (', $html);
    }

    public function testPagesIndexShowsEditContentOnlyForSlugsTheRegistryHas(): void
    {
        self::bootKernel();
        $this->pushRequest('/admin/pages');

        /** @var PageContentFormRegistry $registry */
        $registry = static::getContainer()->get(PageContentFormRegistry::class);
        $pages = [
            new PageViewDTO(1, 'Home', 'home', null, null, null, null, null, 1, true),
            new PageViewDTO(2, 'Catalog', 'catalog', null, null, null, null, null, 2, true),
        ];
        $pageContentFormSlugs = [];
        foreach ($pages as $page) {
            if ($registry->has($page->slug)) {
                $pageContentFormSlugs[] = $page->slug;
            }
        }

        self::assertSame(['home'], $pageContentFormSlugs);

        $html = static::getContainer()->get('twig')->load('backoffice/admin/pages/index.html.twig')->renderBlock('main', [
            'pages' => $pages,
            'pageContentFormSlugs' => $pageContentFormSlugs,
        ]);
        $router = static::getContainer()->get('router');
        $homeContentUrl = $router->generate('admin_page_content_edit', ['pageId' => 1]);
        $catalogContentUrl = $router->generate('admin_page_content_edit', ['pageId' => 2]);

        self::assertStringContainsString($homeContentUrl, $html);
        self::assertStringNotContainsString($catalogContentUrl, $html);
        self::assertSame(1, substr_count($html, 'Edit content'));
        self::assertSame(2, substr_count($html, '>Edit</a>'));
    }

    /**
     * @param class-string $type
     * @param array<string, mixed> $data
     */
    private function renderPageContentForm(string $type, array $data): string
    {
        self::bootKernel();
        $this->pushRequest('/admin/page-content/1/edit');

        $form = static::getContainer()->get('form.factory')->create($type, $data);

        return $this->renderForm($form->createView());
    }

    private function renderForm(mixed $formView): string
    {
        $source = <<<'TWIG'
            {% form_theme form with ['backoffice/admin/page_content/edit.html.twig', '@EasyAdmin/crud/form_theme.html.twig', 'backoffice/admin/page_content/_collection_theme.html.twig'] only %}
            {{ form_widget(form) }}
            TWIG;

        return static::getContainer()->get('twig')->createTemplate($source)->render(['form' => $formView]);
    }

    private function pushRequest(string $path): void
    {
        $request = Request::create($path);
        $request->setSession(new Session(new MockArraySessionStorage()));
        static::getContainer()->get('request_stack')->push($request);
    }
}

final class BannerCollectionEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('heading', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }

    public function getBlockPrefix(): string
    {
        return 'banner_entry';
    }
}
