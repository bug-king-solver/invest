<?php

namespace App\Misc\EmailValidation;

use App\Misc\Logs;
use Monolog\Logger;

abstract class AbstractEmailValidator
{
    /**
     * @var Logger
     */
    protected $logger;

    protected $name = '';

    public function __construct()
    {
        $this->logger = Logs::daily('email_validator');
    }

    public function getName()
    {
        return substr(strrchr(static::class, "\\"), 1);
    }

    public function log($message)
    {
        $this->logger->info($this->getName() . ': ' . $message);
    }
}
