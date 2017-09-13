<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\Category;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class CategoryControllerTest extends BundleTestCase
{
    public function testSimpleCategoryView()
    {
        $this->client->request('GET', '/c/informatique');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        $renderedData = $this->getRenderedData('@WizaplaceFront/search/search.html.twig');

        $this->assertArrayHasKey('currentCategory', $renderedData);
        $this->assertArrayHasKey('filters', $renderedData);

        $this->assertInstanceOf(Category::class, $renderedData['currentCategory']);
        $this->assertSame(3, $renderedData['currentCategory']->getId());

        $this->assertSame([
            'categories' => 3,
        ], $renderedData['filters']);
    }

    public function testValidProductSlugResolution()
    {
        $this->client->request('GET', '/c/voluptas-nostrum-ea-consequatur'); // slug belongs to a product, so the category is not found
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testUnknownSlugResolution()
    {
        $this->client->request('GET', '/c/404');
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testInvalidSlugResolution()
    {
        $this->client->request('GET', '/c/invalid+*slug');
        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }
}
