<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Favorite\FavoriteService;
use WizaplaceFrontBundle\Service\AuthenticationService;
use WizaplaceFrontBundle\Service\ProductUrlGenerator;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class ProductUrlGeneratorTest extends BundleTestCase
{
    public function testGeneratingUrlFromProduct()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById(1);

        $result = $container->get(ProductUrlGenerator::class)->generateProductUrl($product);

        $this->assertSame('/p/informatique/test-product-slug', $result);
    }

    public function testGeneratingUrlFromProductWithDeclinationId()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById(1);

        $declinationId = $product->getDeclinations()[0]->getId();

        $result = $container->get(ProductUrlGenerator::class)->generateProductUrl($product, $declinationId);

        $this->assertSame('/p/informatique/test-product-slug?d=1_0', $result);
    }

    public function testGeneratingUrlFromProductSummary()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->search('optio corporis similique voluptatum')->getProducts()[0];

        $result = $container->get(ProductUrlGenerator::class)->generateProductUrl($product);

        $this->assertSame('/p/informatique/test-product-slug', $result);
    }

    public function testGeneratingUrlFromDeclinationSummary()
    {
        $container = self::$kernel->getContainer();
        $container->get(AuthenticationService::class)->authenticate('user@wizaplace.com', 'password');

        $favoriteService = $container->get(FavoriteService::class);

        $favoriteService->addDeclinationToUserFavorites(1);
        $declinations = $favoriteService->getAll();
        $favoriteService->removeDeclinationToUserFavorites(1); // cleanup

        $result = $container->get(ProductUrlGenerator::class)->generateProductUrl($declinations[0]);

        $this->assertSame('/p/informatique/test-product-slug?d=1_0', $result);
    }

    public function testGeneratingUrlFromString()
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$kernel->getContainer()->get(ProductUrlGenerator::class)->generateProductUrl('test');
    }
}
