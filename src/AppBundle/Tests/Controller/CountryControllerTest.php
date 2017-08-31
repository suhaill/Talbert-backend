<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CountryControllerTest extends WebTestCase
{
    public function testGetcountrylist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/country/getCountryList');
    }

}
