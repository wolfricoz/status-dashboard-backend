<?php

namespace App\Scheduler;

use App\Message\GetServiceStatus;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('StatusCheck')]
final class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
								RecurringMessage::every('60 seconds', new GetServiceStatus("ServiceStatus"))
            )
            ->stateful($this->cache)
        ;
    }
}
