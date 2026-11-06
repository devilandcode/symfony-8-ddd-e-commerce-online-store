<?php

namespace App\Customer\Application;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword): string;
}
