<?php

namespace App\Misc\EmailValidation;

interface EmailValidatorInterface
{
    public function isValid($email);
}
