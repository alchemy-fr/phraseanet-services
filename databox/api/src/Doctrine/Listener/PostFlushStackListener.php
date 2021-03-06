<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Listener\TerminateStackListener;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

class PostFlushStackListener implements EventSubscriber
{
    private array $callbacks = [];
    private array $events = [];
    private EventProducer $eventProducer;
    private TerminateStackListener $terminateStackListener;

    /**
     * @required
     */
    public function setEventProducer(EventProducer $eventProducer)
    {
        $this->eventProducer = $eventProducer;
    }

    /**
     * @required
     */
    public function setTerminateStackListener(TerminateStackListener $terminateStackListener)
    {
        $this->terminateStackListener = $terminateStackListener;
    }

    public function addCallback(callable $callback): void
    {
        $this->callbacks[] = $callback;
    }

    public function addEvent(EventMessage $eventMessage): void
    {
        $this->events[] = $eventMessage;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $callbacks = $this->callbacks;
        $events = $this->events;

        $this->callbacks = [];
        $this->events = [];

        $em = $args->getEntityManager();
        if ($em->getConnection()->getTransactionNestingLevel() > 0) {
            while ($callback = array_shift($callbacks)) {
                $this->terminateStackListener->addCallback($callback);
            }

            while ($event = array_shift($events)) {
                $this->terminateStackListener->addEvent($event);
            }

            return;
        }

        while ($callback = array_shift($callbacks)) {
            $callback();
        }

        while ($event = array_shift($events)) {
            $this->eventProducer->publish($event);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postFlush,
        ];
    }
}
