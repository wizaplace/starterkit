<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Cms\BannerService;
use WizaplaceFrontBundle\Service\ProductListService;

class HomeController extends Controller
{
    /** @var ProductListService */
    private $productListService;

    /** @var BannerService */
    private $bannerService;

    /**
     * @var int Max number of latest products to be fetched.
     */
    protected const LATEST_PRODUCTS_MAX_COUNT = 6;

    public function __construct(ProductListService $productListService, BannerService $bannerService)
    {
        $this->productListService = $productListService;
        $this->bannerService = $bannerService;
    }


    public function homeAction(): Response
    {
        // latest products
        $latestProducts = $this->productListService->getLatestProducts(static::LATEST_PRODUCTS_MAX_COUNT);

        // banners
        $desktopBanners = $this->bannerService->getHomepageBanners("desktop");
        $mobileBanners = $this->bannerService->getHomepageBanners("mobile");

        return $this->render('@WizaplaceFront/home/home.html.twig', [
            'latestProducts' => $latestProducts,
            'desktopBanners' => $desktopBanners,
            'mobileBanners' => $mobileBanners,
        ]);
    }
}
