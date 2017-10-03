<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wizaplace\SDK\Catalog\AttributeType;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\Product;
use Wizaplace\SDK\Catalog\ProductSummary;

class BrandService
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var CatalogService */
    private $catalogService;

    /** @var LoggerInterface */
    private $logger;

    /**
     * BrandService constructor.
     * @param UrlGeneratorInterface $urlGenerator
     * @param CatalogService $catalogService
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, CatalogService $catalogService, LoggerInterface $logger)
    {
        $this->urlGenerator = $urlGenerator;
        $this->catalogService = $catalogService;
        $this->logger = $logger;
    }

    /**
     * @param ProductSummary|Product $product
     * @return null|Brand
     */
    public function getBrand($product): ?Brand
    {
        if ($product instanceof ProductSummary) {
            return $this->getBrandFromProductSummary($product);
        }

        if ($product instanceof Product) {
            return $this->getBrandFromProduct($product);
        }

        $this->logger->warning('Unexpected type for $product in getBrand : '.(is_object($product) ? get_class($product) : gettype($product)));

        return null;
    }

    public function getBrandFromProductSummary(ProductSummary $product): ?Brand
    {
        foreach ($product->getAttributes() as $attribute) {
            if ($attribute->getType()->equals(AttributeType::LIST_BRAND())) {
                $values = $attribute->getValues();
                $brand = reset($values);

                return new Brand(
                    $brand['name'],
                    new Uri($this->urlGenerator->generate('attribute_variant', ['slug' => $brand['slug']]))
                );
            }
        }

        return null;
    }

    public function getBrandFromProduct(Product $product): ?Brand
    {
        foreach ($product->getAttributes() as $attribute) {
            if ($attribute->getType()->equals(AttributeType::LIST_BRAND())) {
                $values = $attribute->getValueIds();
                $variant = $this->catalogService->getAttributeVariant(reset($values));

                return new Brand(
                    $variant->getName(),
                    new Uri($this->urlGenerator->generate('attribute_variant', ['slug' => $variant->getSlug()]))
                );
            }
        }

        return null;
    }
}
