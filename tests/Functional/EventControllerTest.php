<?php

namespace App\Tests\Functional;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testMyEventsRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mes-inscriptions');

        $this->assertResponseRedirects('/login');
    }

    public function testMyEventsShowsRegisteredEvent(): void
    {
        $client = static::createClient();

        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'user@test.fr']);

        $this->assertNotNull($user);

        $client->loginUser($user);
        $client->request('GET', '/mes-inscriptions');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Semi-marathon');
    }

    public function testUserCanUnregister(): void
    {
        $client = static::createClient();

        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'user@test.fr']);

        $event = static::getContainer()
            ->get(EventRepository::class)
            ->findOneBy(['title' => 'Semi-marathon']);

        $this->assertNotNull($user);
        $this->assertNotNull($event);
        $this->assertTrue($event->getParticipants()->contains($user));

        $client->loginUser($user);

        $client->request(
            'POST',
            '/evenement/'.$event->getId().'/desinscription',
            [],
            [],
            ['HTTP_REFERER' => 'http://localhost/mes-inscriptions']
        );

        $this->assertResponseRedirects('http://localhost/mes-inscriptions');
        $this->assertFalse($event->getParticipants()->contains($user));
    }

    public function testAnonymousCannotEditEvent(): void
    {
        $client = static::createClient();

        $event = static::getContainer()
            ->get(EventRepository::class)
            ->findOneBy(['title' => 'Semi-marathon']);

        $this->assertNotNull($event);

        $client->request('GET', '/evenement/'.$event->getId().'/modifier');

        $this->assertResponseRedirects('/login');
    }

    public function testAdminCanEditEvent(): void
    {
        $client = static::createClient();

        $admin = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'admin@test.fr']);

        $event = static::getContainer()
            ->get(EventRepository::class)
            ->findOneBy(['title' => 'Semi-marathon']);

        $this->assertNotNull($admin);
        $this->assertNotNull($event);

        $client->loginUser($admin);
        $client->request('GET', '/evenement/'.$event->getId().'/modifier');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testUserCannotEditAnotherUsersEvent(): void
    {
        $client = static::createClient();

        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'user@test.fr']);

        $event = static::getContainer()
            ->get(EventRepository::class)
            ->findOneBy(['title' => 'Semi-marathon']);

        $this->assertNotNull($user);
        $this->assertNotNull($event);

        $client->loginUser($user);
        $client->request('GET', '/evenement/'.$event->getId().'/modifier');

        $this->assertResponseStatusCodeSame(403);
    }
}
