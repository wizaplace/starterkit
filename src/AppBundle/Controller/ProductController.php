<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class ProductController extends Controller
{
    public function viewAction(
        CatalogService $catalogService,
        SeoService $seoService,
        string $categoryPath,
        string $slug
    ) : Response
    {
        $slugTarget = $seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::PRODUCT()) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }
        $productId = $slugTarget->getObjectId();

        $product = $catalogService->getProductById($productId);

        $realCategoryPath = implode('/', $product->getCategorySlugs());
        if ($categoryPath !== $realCategoryPath) {
            return $this->redirect($this->generateUrl('product', ['categoryPath' => $realCategoryPath, 'slug' => $product->getSlug()]));
        }

        // latestProducts
        $latestProducts = $catalogService->search('', [], ['timestamp' => 'desc'], 6)->getProducts();

        return $this->render('product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
        ]);
    }
}
