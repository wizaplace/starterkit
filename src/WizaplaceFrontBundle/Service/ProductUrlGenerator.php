<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Routing\RouterInterface;
use Wizaplace\Catalog\DeclinationSummary;
use Wizaplace\Catalog\Product;
use Wizaplace\Catalog\ProductCategory;
use Wizaplace\Catalog\ProductSummary;

class ProductUrlGenerator
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Product|ProductSummary|DeclinationSummary $product
     */
    public function generateProductUrl($product, ?string $declinationId = null): string
    {
        if ($product instanceof Product) {
            return $this->generateUrlFromProduct($product, $declinationId);
        } elseif ($product instanceof ProductSummary) {
            return $this->generateUrlFromProductSummary($product, $declinationId);
        } elseif ($product instanceof DeclinationSummary) {
            return $this->generateUrlFromProductDeclinationSummary($product);
        } else {
            throw new \InvalidArgumentException('Cannot generate an url from given $product');
        }
    }

    public function generateUrlFromProduct(Product $product, ?string $declinationId = null): string
    {
        return $this->generateUrl(
            $product->getSlug(),
            $product->getCategorySlugs(),
            $declinationId
        );
    }

    public function generateUrlFromProductSummary(ProductSummary $productSummary, ?string $declinationId = null): string
    {
        return $this->generateUrl(
            $productSummary->getSlug(),
            $productSummary->getCategorySlugs(),
            $declinationId
        );
    }

    public function generateUrlFromProductDeclinationSummary(DeclinationSummary $declinationSummary): string
    {
        return $this->generateUrl(
            $declinationSummary->getSlug(),
            array_map(static function (ProductCategory $category) : string {
                return $category->getSlug();
            }, $declinationSummary->getCategoryPath()),
            $declinationSummary->getId()
        );
    }

    /**
     * @param string[] $categoryPath
     */
    private function generateUrl(string $productSlug, array $categoryPath, ?string $declinationId = null): string
    {
        return $this->router->generate('product', [
            'categoryPath' => join('/', $categoryPath),
            'slug' => $productSlug,
            'd' => $declinationId,
        ]);
    }
}
