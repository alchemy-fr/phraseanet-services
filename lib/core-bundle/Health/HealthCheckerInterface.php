<?php

declare(strict_types=1);

namespace Alchemy\CoreBundle\Health;

interface HealthCheckerInterface
{
    public function getName(): string;

    public function check(): bool;

    public function getAdditionalInfo(): ?array;
}
