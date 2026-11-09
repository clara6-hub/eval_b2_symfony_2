<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testRemainingPlacesWithoutParticipants(): void
    {
        $event = new Event();
        $event->setPlaces(10);

        $this->assertSame(10, $event->getRemainingPlaces());
    }

    public function testRemainingPlacesWithParticipant(): void
    {
        $event = new Event();
        $event->setPlaces(10);
        $event->addParticipant(new User());

        $this->assertSame(9, $event->getRemainingPlaces());
    }

    public function testRemainingPlacesWhenFull(): void
    {
        $event = new Event();
        $event->setPlaces(1);
        $event->addParticipant(new User());

        $this->assertSame(0, $event->getRemainingPlaces());
    }
}
