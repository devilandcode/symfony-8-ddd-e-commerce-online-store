<?php

namespace App\Storefront\Infrastructure\Twig;

use App\ContentManagement\Application\SiteMenu\ListActiveRootMenus\ListActiveRootMenusHandler;
use App\ContentManagement\Domain\Entity\SiteMenu;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViewShareExtenion extends AbstractExtension
{
    /** @var list<SiteMenu>|null */
    private ?array $siteMenus = null;

    public function __construct(private ListActiveRootMenusHandler $listActiveRootMenus)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_menus', function() {
                if (null === $this->siteMenus) {
                    $this->siteMenus = ($this->listActiveRootMenus)();
                }

                return $this->siteMenus;
            }),
        ];
    }
}
