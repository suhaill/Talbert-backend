<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleControllerTest extends WebTestCase
{
    public function testGetrole()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/role/getRole');
    }

}
