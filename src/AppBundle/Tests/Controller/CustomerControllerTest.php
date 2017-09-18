<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerControllerTest extends WebTestCase
{
    public function testAddcustomer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/addCustomer');
    }

}
