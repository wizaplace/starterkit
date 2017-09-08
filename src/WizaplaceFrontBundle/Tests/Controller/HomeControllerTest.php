<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\ProductSummary;
use Wizaplace\SDK\Cms\Banner;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class HomeControllerTest extends BundleTestCase
{
    public function testHome()
    {
        $this->client->request('GET', '/');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        $renderedData = $this->getRenderedData('@WizaplaceFront/home/home.html.twig');

        $this->assertCount(6, $renderedData['latestProducts']);
        foreach ($renderedData['latestProducts'] as $product) {
            $this->assertInstanceOf(ProductSummary::class, $product);
        }

        $this->assertCount(1, $renderedData['desktopBanners']);
        foreach ($renderedData['desktopBanners'] as $banner) {
            $this->assertInstanceOf(Banner::class, $banner);
        }

        $this->assertCount(1, $renderedData['mobileBanners']);
        foreach ($renderedData['desktopBanners'] as $banner) {
            $this->assertInstanceOf(Banner::class, $banner);
        }
    }
}
