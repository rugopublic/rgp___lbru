<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class AccountNotFoundException extends Exception
{

    /**
     * Initializes the account not found exception instance and wires its required dependencies.
     *
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     * @return void
     */
    public function __construct(
        string $message = "The user's account does not exist.",
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
