<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\AppBundle\Controller;

use Tests\VcrWebTestCase;

class ProductControllerTest extends VcrWebTestCase
{
    public function testValidProductSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/p/informatique/ecrans/voluptas-nostrum-ea-consequatur');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testValidCategorySlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/p/informatique/informatique'); // slug belongs to a category, so the product is not found
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/p/informatique/ecrans/404');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testInvalidSlugResolution()
    {
        $client = $this->createClient();

        $client->request('GET', '/p/informatique/ecrans/invalid+*slug');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNonCanonicalCategoryPathIsRedirected()
    {
        $client = $this->createClient();

        $client->followRedirects(false);
        $client->request('GET', '/p/product/informatitititique/ecrans/voluptas-nostrum-ea-consequatur');
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/p/product/informatique/ecrans/voluptas-nostrum-ea-consequatur', $response->headers->get('Location'));
    }
}
