<?php

namespace App\Rules;

use App\Misc\EmailValidation\EmailValidator;
use Illuminate\Contracts\Validation\Rule;

class AuthenticEmail implements Rule
{
    /**
     * @var EmailValidator
     */
    private $validator;

    /**
     * Create a new rule instance.
     *
     * @param EmailValidator $validator
     */
    public function __construct(EmailValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return config('app.env') == 'production' ?
            $this->validator->isValid($value) :
            true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please provide a valid email address.';
    }
}
