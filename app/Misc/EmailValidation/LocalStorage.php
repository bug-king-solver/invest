<?php

namespace App\Misc\EmailValidation;

use App\Models\BurnerEmail;

class LocalStorage extends AbstractEmailValidator implements EmailValidatorInterface
{
    /**
     * @param $email
     * @return bool
     */
    public function isValid($email)
    {
        $mail_parts = explode('@', $email);
        if (count($mail_parts) < 2) {
            $this->log('not a valid email: ' . $email);
            return true;
        }

        $domain = $mail_parts[1];

        if (BurnerEmail::where('domain', $domain)->exists()) {
            $this->log('invalid email: ' . $email);
            return false;
        }

        return true;
    }
}
