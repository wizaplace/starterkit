<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\Catalog\CatalogService;
use Wizaplace\Catalog\ProductSummary;

class ProductListService
{
    /** @var CatalogService */
    private $productService;

    public function __construct(CatalogService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @return ProductSummary[]
     */
    public function getLatestProducts(int $maxProductCount = 6) : array
    {
        return $this->productService->search('', [], ['createdAt' => 'desc'], $maxProductCount)->getProducts();
    }
}
