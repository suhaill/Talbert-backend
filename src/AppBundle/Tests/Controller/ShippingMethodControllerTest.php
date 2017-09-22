<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShippingMethodControllerTest extends WebTestCase
{
    public function testGetshippingmethodlist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/shippingmethod/getShippingMethods');
    }

}
