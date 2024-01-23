<?php

namespace App\Misc\EmailValidation;

class WhiteList
{
    const WHITELIST = [
        'peaceful.projects@protonmail.com'
    ];

    public function contains($email)
    {
        return in_array($email, self::WHITELIST);
    }
}
