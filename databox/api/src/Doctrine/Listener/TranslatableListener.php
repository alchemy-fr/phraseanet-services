<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Entity\TranslatableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TranslatableListener implements EventSubscriber
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getEntity();

        if ($object instanceof TranslatableInterface) {
            if (!$object->hasLocale()) {
                $object->setLocale($this->defaultLocale);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }
}
