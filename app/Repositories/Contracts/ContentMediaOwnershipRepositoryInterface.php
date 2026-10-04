<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ContentMediaOwnershipRepositoryInterface
{
    /** @return iterable<string> */
    public function expectedFolders(): iterable;
}
