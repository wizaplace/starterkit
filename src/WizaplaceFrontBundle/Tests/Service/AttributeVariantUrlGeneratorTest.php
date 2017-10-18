<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\Catalog\CatalogService;
use WizaplaceFrontBundle\Service\AttributeVariantUrlGenerator;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class AttributeVariantUrlGeneratorTest extends BundleTestCase
{
    public function testGeneratingUrlFromBrand()
    {
        $container = self::$kernel->getContainer();
        $catalogService = $container->get(CatalogService::class);
        $brand = $catalogService->getBrand($catalogService->getProductById('5'));

        $result = $container->get(AttributeVariantUrlGenerator::class)->generateAttributeVariantUrl($brand);

        $this->assertSame('/a/puma', $result);
    }

    public function testGeneratingUrlFromAttributeVariant()
    {
        $container = self::$kernel->getContainer();
        $attributeVariant = $container->get(CatalogService::class)->getAttributeVariant(2);

        $result = $container->get(AttributeVariantUrlGenerator::class)->generateAttributeVariantUrl($attributeVariant);

        $this->assertSame('/a/blanc', $result);
    }

    public function testGeneratingUrlFromString()
    {
        $this->expectException(\InvalidArgumentException::class);
        self::$kernel->getContainer()->get(AttributeVariantUrlGenerator::class)->generateAttributeVariantUrl('test');
    }
}
