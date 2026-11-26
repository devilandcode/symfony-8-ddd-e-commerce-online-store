<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Session;

use App\Cart\Application\Interface\CartOwnerProviderInterface;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Customer\Infrastructure\Security\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-backed cart identity.
 *
 * - Authenticated customers own their cart by user id.
 * - Guests get a random 256-bit secret stored in the session; only its SHA-256 digest reaches the database.
 *
 * The secret is created lazily on the first write (add to cart), so anonymous visitors and bots that never
 * touch the cart do not get a token. Reads never start a session that does not exist yet.
 */
final readonly class SessionCartOwnerProvider implements CartOwnerProviderInterface
{
    private const string SESSION_KEY = 'cart.guest_secret';

    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
    ) {
    }

    public function findCurrent(): ?CartOwner
    {
        $user = $this->security->getUser();
        if ($user instanceof SecurityUser) {
            return CartOwner::user($user->getId());
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->hasSession() || !$request->hasPreviousSession()) {
            return null;
        }

        $secret = $request->getSession()->get(self::SESSION_KEY);

        return \is_string($secret) && '' !== $secret ? $this->guestOwner($secret) : null;
    }

    public function resolveCurrent(): CartOwner
    {
        $owner = $this->findCurrent();
        if (null !== $owner) {
            return $owner;
        }

        $secret = bin2hex(random_bytes(32));
        $this->requestStack->getSession()->set(self::SESSION_KEY, $secret);

        return $this->guestOwner($secret);
    }

    private function guestOwner(string $secret): CartOwner
    {
        return CartOwner::guest(hash('sha256', $secret));
    }
}
