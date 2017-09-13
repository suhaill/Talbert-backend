<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderControllerTest extends WebTestCase
{
    public function testGetorderslist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/order/getOrders');
    }

}
