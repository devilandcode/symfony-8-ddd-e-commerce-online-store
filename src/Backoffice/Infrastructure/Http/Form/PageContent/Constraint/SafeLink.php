<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class SafeLink extends Constraint
{
    public string $message = 'Links must be relative paths or HTTP(S) URLs.';
}
