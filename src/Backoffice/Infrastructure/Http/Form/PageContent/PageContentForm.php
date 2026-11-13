<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Binds a page-content form type to a page slug and tags it for the registry.
 *
 * The slug argument is stored only as a tag attribute so container autoconfiguration
 * does not see an extra service-definition key.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PageContentForm extends AutoconfigureTag
{
    public function __construct(string $slug)
    {
        parent::__construct('app.page_content_form', ['slug' => $slug]);
    }
}
