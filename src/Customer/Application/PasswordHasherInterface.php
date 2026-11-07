<?php

namespace App\Customer\Application;

use App\Customer\Domain\ValueObject\HashedPassword;
use App\Customer\Application\ValueObject\PlainPassword;

interface PasswordHasherInterface
{
    public function hash(PlainPassword $plainPassword): HashedPassword;
}
