<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SalesmanControllerTest extends WebTestCase
{
    public function testGetsalesmanlist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/salesman/getSalesmans');
    }

}
