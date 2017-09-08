<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\Option;
use Wizaplace\SDK\Catalog\OptionVariant;
use Wizaplace\SDK\Catalog\Review\ReviewService;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugTargetType;
use WizaplaceFrontBundle\Service\ProductUrlGenerator;

class ProductController extends Controller
{
    /** @var ProductUrlGenerator */
    private $productUrlGenerator;

    public function __construct(ProductUrlGenerator $productUrlGenerator)
    {
        $this->productUrlGenerator = $productUrlGenerator;
    }

    public function viewAction(SeoService $seoService, string $categoryPath, string $slug, Request $request) : Response
    {
        $slugTarget = $seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::PRODUCT()) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }
        $productId = (int) $slugTarget->getObjectId();

        $catalogService = $this->get(CatalogService::class);
        $product = $catalogService->getProductById($productId);

        // Recovering the declinationId from url, if none passed, declination = first declination of the product
        if (!$declinationId = $request->query->get('d')) {
            $declinationId = $product->getDeclinations()[0]->getId();
        }

        $realCategoryPath = implode('/', $product->getCategorySlugs());
        if ($categoryPath !== $realCategoryPath) {
            return $this->redirect($this->productUrlGenerator->generateUrlFromProduct($product));
        }

        // latestProducts
        $latestProducts = $catalogService->search('', [], ['createdAt' => 'desc'], 6)->getProducts();

        //product Reviews
        $reviewService = $this->get(ReviewService::class);
        $reviews = $reviewService->getProductReviews($productId);

        $declination = $product->getDeclination($declinationId);
        $variantIdByOptionId = [];
        foreach ($declination->getOptions() as $option) {
            $variantIdByOptionId[$option->getId()] = $option->getVariantId();
        }

        $options = array_map(function (Option $option) use ($product, $variantIdByOptionId) {
            return [
                'id' => $option->getId(),
                'name' => $option->getName(),
                'variants' => array_map(function (OptionVariant $variant) use ($product, $option, $variantIdByOptionId) {
                    $isSelected = false;
                    if (isset($variantIdByOptionId[$option->getId()])) {
                        $isSelected = $variantIdByOptionId[$option->getId()] === $variant->getId();
                    }
                    $variantIdByOptionId[$option->getId()] = $variant->getId();
                    $declinationId = $product->getDeclinationFromOptions($variantIdByOptionId)->getId();

                    return [
                        'id' => $variant->getId(),
                        'name' => $variant->getName(),
                        'selected' => $isSelected,
                        'url' => $this->productUrlGenerator->generateUrlFromProduct($product, $declinationId),
                    ];
                }, $option->getVariants()),
            ];
        }, $product->getOptions());

        return $this->render('@App/product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
            'reviews' => $reviews,
            'declination' => $declination,
            'options' => $options,
        ]);
    }

    public function reviewAction(ReviewService $reviewService, Request $request) : RedirectResponse
    {
        $reviewService->reviewProduct(
            (int) $request->request->get('product_id'),
            (string) $request->request->get('author'),
            (string) $request->request->get('message'),
            (int) $request->request->get('rating')
        );

        return $this->redirect($request->request->get('redirect_url'));
    }
}
