<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\Catalog\CatalogService;
use WizaplaceFrontBundle\Service\Brand;
use WizaplaceFrontBundle\Service\BrandService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class BrandServiceTest extends BundleTestCase
{
    public function testGetBrandFromProductWithoutBrand()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById(1);

        $brand = $container->get(BrandService::class)->getBrand($product);
        $this->assertNull($brand);
    }

    public function testGetBrandFromProduct()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById(5);

        $brand = $container->get(BrandService::class)->getBrand($product);
        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertSame('Puma', $brand->getName());
        $this->assertSame('/a/puma', (string) $brand->getUrl());
    }

    public function testGetBrandFromProductSummaryWithoutBrand()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->search('Z11 Plus Boîtier PC en Acier ATX')->getProducts()[0];

        $brand = $container->get(BrandService::class)->getBrand($product);
        $this->assertNull($brand);
    }

    public function testGetBrandFromProductSummary()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->search('complex')->getProducts()[0];

        $brand = $container->get(BrandService::class)->getBrand($product);
        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertSame('Puma', $brand->getName());
        $this->assertSame('/a/puma', (string) $brand->getUrl());
    }

    public function testGetBrandFromInvalidType()
    {
        $brand = self::$kernel->getContainer()->get(BrandService::class)->getBrand(42);
        $this->assertNull($brand);
    }
}
