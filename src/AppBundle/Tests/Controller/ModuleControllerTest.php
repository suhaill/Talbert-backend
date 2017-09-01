<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModuleControllerTest extends WebTestCase
{
    public function testGetmoduleslist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/getModulesList');
    }

}
