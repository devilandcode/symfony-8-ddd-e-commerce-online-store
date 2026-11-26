<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

final readonly class CartOwner
{
    private const string GUEST_TOKEN_PATTERN = '/\A[0-9a-f]{64}\z/';

    private function __construct(
        private ?int $userId,
        private ?string $guestToken,
    ) {
    }

    public static function user(int $userId): self
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Cart owner user id must be positive.');
        }

        return new self($userId, null);
    }

    public static function guest(string $guestToken): self
    {
        if (1 !== preg_match(self::GUEST_TOKEN_PATTERN, $guestToken)) {
            throw new \InvalidArgumentException('Guest cart token must be a 64-character lowercase hexadecimal string.');
        }

        return new self(null, $guestToken);
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getGuestToken(): ?string
    {
        return $this->guestToken;
    }

    public function isGuest(): bool
    {
        return null !== $this->guestToken;
    }

    public function equals(self $other): bool
    {
        return $this->userId === $other->userId && $this->guestToken === $other->guestToken;
    }
}
