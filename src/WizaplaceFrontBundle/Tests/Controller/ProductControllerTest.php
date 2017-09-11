<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\Product;
use Wizaplace\SDK\Catalog\ProductSummary;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class ProductControllerTest extends BundleTestCase
{
    public function testSimpleProductView()
    {
        $this->client->request('GET', '/p/informatique/ecrans/voluptas-nostrum-ea-consequatur');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        $renderedData = $this->getRenderedData('@WizaplaceFront/product/product.html.twig');

        $this->assertArrayHasKey('product', $renderedData);
        $this->assertArrayHasKey('latestProducts', $renderedData);
        $this->assertArrayHasKey('reviews', $renderedData);
        $this->assertArrayHasKey('declination', $renderedData);
        $this->assertArrayHasKey('options', $renderedData);
        $this->assertArrayHasKey('isFavorite', $renderedData);

        $this->assertInstanceOf(Product::class, $renderedData['product']);
        $this->assertSame('4', $renderedData['product']->getId());

        $this->assertFalse($renderedData['isFavorite']);

        $this->assertCount(6, $renderedData['latestProducts']);
        $this->assertContainsOnlyInstancesOf(ProductSummary::class, $renderedData['latestProducts']);

        $this->assertCount(0, $renderedData['reviews']);

        $this->assertCount(0, $renderedData['options']);
    }

    public function testValidCategorySlugResolution()
    {
        $this->client->request('GET', '/p/informatique/informatique'); // slug belongs to a category, so the product is not found
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testUnknownSlugResolution()
    {
        $this->client->request('GET', '/p/informatique/ecrans/404');
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testInvalidSlugResolution()
    {
        $this->client->request('GET', '/p/informatique/ecrans/invalid+*slug');
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testNonCanonicalCategoryPathIsRedirected()
    {
        $this->client->followRedirects(false);
        $this->client->request('GET', '/p/informatitititique/ecrans/voluptas-nostrum-ea-consequatur');
        $response = $this->client->getResponse();
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->assertEquals('/p/informatique/ecrans/voluptas-nostrum-ea-consequatur', $response->headers->get('Location'));
    }
}
