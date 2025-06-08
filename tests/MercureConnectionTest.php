<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureTest extends WebTestCase
{
    public function testHubInterfaceIsAvailable(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var HubInterface $hub */
        $hub = $container->get(HubInterface::class);

        $this->assertInstanceOf(HubInterface::class, $hub);
    }

    public function testCanPublishUpdate(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var HubInterface $hub */
        $hub = $container->get(HubInterface::class);

        $update = new Update(
            'test/topic',
            json_encode(['message' => 'Test message'])
        );
        
        $this->expectNotToPerformAssertions();
        $hub->publish($update);
    }
}
