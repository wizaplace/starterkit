<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\ProductSummary;
use Wizaplace\Cms\Banner;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class HomeControllerTest extends BundleTestCase
{
    public function testHome()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $client);

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
