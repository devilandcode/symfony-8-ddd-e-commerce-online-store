<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\PageContent\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class SafeLinkValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SafeLink) {
            throw new UnexpectedTypeException($constraint, SafeLink::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $url = trim($value);
        if ('' === $url || self::isSafe($url)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    private static function isSafe(string $url): bool
    {
        if (preg_match('/[\x00-\x20\\\\]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $parts = parse_url($url);

        return \is_array($parts)
            && isset($parts['scheme'], $parts['host'])
            && \in_array(strtolower($parts['scheme']), ['http', 'https'], true);
    }
}
