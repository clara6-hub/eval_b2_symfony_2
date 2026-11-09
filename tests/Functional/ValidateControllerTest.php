<?php

namespace App\Tests\Functional;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ValidateControllerTest extends WebTestCase
{
    public function testAdminCanAccessValidationPage(): void
    {
        $client = static::createClient();

        $admin = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'admin@test.fr']);

        $client->loginUser($admin);
        $client->request('GET', '/validate');

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotAccessValidationPage(): void
    {
        $client = static::createClient();

        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'user@test.fr']);

        $client->loginUser($user);
        $client->request('GET', '/validate');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanRefuseEvent(): void
    {
        $client = static::createClient();

        $admin = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'admin@test.fr']);

        $eventRepository = static::getContainer()->get(EventRepository::class);
        $event = $eventRepository->findOneBy(['title' => 'Tournoi de basket']);

        $this->assertNotNull($event);

        $eventId = $event->getId();

        $client->loginUser($admin);
        $client->request('POST', '/validate/'.$eventId.'/refuser');

        $this->assertResponseRedirects('/validate');
        $this->assertNull($eventRepository->find($eventId));
    }
}
