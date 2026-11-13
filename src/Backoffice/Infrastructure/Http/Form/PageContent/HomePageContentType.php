<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Presentational fields for the storefront page whose slug is "home".
 */
#[PageContentForm(slug: 'home')]
final class HomePageContentType extends AbstractType
{
    public const SLIDE_TITLE = 'Slide';

    /**
     * Fixed homepage sections. Default headings use these same labels.
     *
     * @var array<string, string>
     */
    public const SECTION_LABELS = [
        'new_arrivals' => 'New arrivals',
        'popular_brands' => 'Popular brands',
        'price_hits' => 'Price hits',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data) || [] === $data) {
                $event->setData(self::defaultPayload());
            }
        });

        $builder
            ->add('slides', CollectionType::class, [
                'entry_type' => HeroSlideType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => 'Hero slider',
                'entry_title' => self::SLIDE_TITLE,
                'entry_options' => ['label' => false],
            ])
            ->add('sections', CollectionType::class, [
                'entry_type' => HomeSectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => 'Section headings and links',
                'entry_title' => self::SECTION_LABELS,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }

    /**
     * Editor defaults used only when a home page has no stored payload yet.
     *
     * @return array{
     *     slides: list<array<string, string|bool|null>>,
     *     sections: array<string, array<string, string>>
     * }
     */
    public static function defaultPayload(): array
    {
        return [
            'slides' => [
                self::slide('To the catalog'),
                self::slide('To the catalog'),
            ],
            'sections' => [
                'new_arrivals' => self::section(self::SECTION_LABELS['new_arrivals'], 'See more'),
                'popular_brands' => self::section(self::SECTION_LABELS['popular_brands'], 'All brands'),
                'price_hits' => self::section(self::SECTION_LABELS['price_hits'], 'See more'),
            ],
        ];
    }

    /**
     * @return array<string, string|bool|null>
     */
    private static function slide(string $buttonLabel): array
    {
        return [
            'heading' => '',
            'description' => '',
            'buttonLabel' => $buttonLabel,
            'buttonUrl' => '',
            'desktopImage' => null,
            'mobileImage' => null,
            'isActive' => false,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function section(string $heading, string $buttonLabel): array
    {
        return [
            'heading' => $heading,
            'buttonLabel' => $buttonLabel,
            'buttonUrl' => '',
        ];
    }
}
