<?php

namespace App\Misc\EmailValidation;

use Illuminate\Support\Str;

class EmailValidator
{
    /**
     * @var WhiteList
     */
    private $white_list;

    public function __construct(WhiteList $white_list)
    {
        $this->white_list = $white_list;
    }

    /**
     * @param $email
     * @return bool
     */
    public function isValid($email)
    {
        if ($this->white_list->contains($email)) {
            return true;
        }

        foreach (['local_storage', 'email_hippo'] as $name) {
            $provider = $this->buildProvider($name);
            if ( ! $provider->isValid($email)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $name
     * @return EmailValidatorInterface
     */
    private function buildProvider($name)
    {
        $class_name = __NAMESPACE__ . '\\' . Str::studly($name);
        return app($class_name);
    }
}
