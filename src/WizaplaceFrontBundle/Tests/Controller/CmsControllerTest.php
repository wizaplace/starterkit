<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Cms\Page;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class CmsControllerTest extends BundleTestCase
{
    public function testCmsPage()
    {
        $this->client->request('GET', '/test-cms-page-slug');

        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);
        $data = $this->getRenderedData('@WizaplaceFront/cms/page.html.twig');

        /** @var Page $page */
        $page = $data['page'];
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('Test Cms Page Slug', $page->getTitle());
    }

    public function testCmsPageNotFound()
    {
        $this->client->request('GET', '/cms404'); // valid slug that doesn't exist at all

        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testInvalidSlugNotFound()
    {
        $this->client->request('GET', '/invalid+*slug'); // invalid slug

        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testCategorySlugWithoutPrefixNotFound()
    {
        // We use a valid category slug, but without the `/c/` prefix so it hits the CMS controller.
        // This should give a 404
        $this->client->request('GET', '/informatique');

        $this->assertResponseCodeEquals(Response::HTTP_NOT_FOUND, $this->client);
    }
}
