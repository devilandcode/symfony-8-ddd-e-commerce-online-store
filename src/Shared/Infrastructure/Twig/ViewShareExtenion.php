<?php

namespace App\Shared\Infrastructure\Twig;



use App\Cart\Application\FrontUseCases\GetCartSummary\GetCartSummaryHandler;
use App\Cart\Application\FrontUseCases\Output\CartSummaryDTO;
use App\Catalog\Application\Category\FrontUseCases\ListCategory\ListActiveRootCategoryHandler;
use App\ContentManagement\Application\SiteMenu\FrontUseCases\ListActiveRootMenus\ListActiveRootMenusHandler;
use App\ContentManagement\Domain\Entity\SiteMenu;
use App\Shared\Infrastructure\Image\ImageVariantProcessor;
use App\StoreSettings\Application\FrontUseCases\ListContactSettings\ListContactSettingsHandler;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViewShareExtenion extends AbstractExtension
{
    /** @var list<SiteMenu>|null */
    private ?array $siteMenus = null;

    private ?array $settings = null;

    /** @var list<Category>|null */
    private ?array $menuCategories = null;

    private ?CartSummaryDTO $cartSummary = null;

    public function __construct(
        private ListActiveRootMenusHandler $listActiveRootMenus,
        private ListContactSettingsHandler $listContactSettings,
        private ListContentSettingHandler $listContentSetting,
        private ListActiveRootCategoryHandler $listActiveRootCategories,
        private readonly ImageVariantProcessor $imageVariantProcessor,
        private readonly GetCartSummaryHandler $getCartSummary,
    ){}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_menus', function() {
                if (null === $this->siteMenus) {
                    $this->siteMenus = ($this->listActiveRootMenus)();
                }

                return $this->siteMenus;
            }),

            new TwigFunction('settings', function() {
                if (null === $this->settings) {
                    $this->settings = [
                        'contacts' => ($this->listContactSettings)(),
                        'content' => ['payload' => ($this->listContentSetting)()->toArray()],
                    ];
                }

                return $this->settings;
            }),

            new TwigFunction('menu_categories', function() {
                if (null === $this->menuCategories) {
                    $this->menuCategories = ($this->listActiveRootCategories)();
                }
            
                return $this->menuCategories;
            }),

            new TwigFunction('cart_summary', fn (): CartSummaryDTO => $this->cartSummary ??= ($this->getCartSummary)()),

            new TwigFunction('image_variant', $this->createVariant(...)),
        ];
    }

    public function createVariant(?string $sourcePath, int $width, int $height, string $fit = 'contain'): ?string
    {
        return $this->imageVariantProcessor->create($sourcePath, $width, $height, $fit);
    }
}
