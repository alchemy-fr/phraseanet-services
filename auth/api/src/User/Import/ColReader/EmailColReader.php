<?php

declare(strict_types=1);

namespace App\User\Import\ColReader;

use App\Entity\User;

class EmailColReader implements ColReaderInterface
{
    public function __invoke(string $str, User $user): void
    {
        $str = strtolower(trim($str));

        $user->setUsername($str);
    }

    public function supports(string $colName): bool
    {
        return in_array(strtolower($colName), ['username', 'email'], true);
    }
}
