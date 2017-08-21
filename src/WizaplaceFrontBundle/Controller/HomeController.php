<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

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

    /** @var int */
    private $latestProductsMaxCount;

    public function __construct(ProductListService $productListService, BannerService $bannerService, int $latestProductsMaxCount)
    {
        $this->productListService = $productListService;
        $this->bannerService = $bannerService;
        $this->latestProductsMaxCount = $latestProductsMaxCount;
    }


    public function homeAction(): Response
    {
        // latest products
        $latestProducts = $this->productListService->getLatestProducts($this->latestProductsMaxCount);

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
