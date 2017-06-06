<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\AppBundle\Controller;

use Tests\VcrWebTestCase;

class CategoryControllerTest extends VcrWebTestCase
{
    public function testValidCategorySlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/c/informatique');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testValidProductSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/c/voluptas-nostrum-ea-consequatur'); // slug belongs to a product, so the category is not found
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/c/404');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testInvalidSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/c/invalid+*slug');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
