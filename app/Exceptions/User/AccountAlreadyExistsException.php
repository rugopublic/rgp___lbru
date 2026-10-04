<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class AccountAlreadyExistsException extends Exception
{

    /**
     * Initializes the account already exists exception instance and wires its required dependencies.
     *
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     * @return void
     */
    public function __construct(
        string $message = "The user already has an account.",
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
