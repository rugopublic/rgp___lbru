<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class DeletionNotRequestedException extends Exception
{

    /**
     * Initializes the deletion not requested exception instance and wires its required dependencies.
     *
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     * @return void
     */
    public function __construct(
        string $message = "The user has not been scheduled for deletion.",
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
