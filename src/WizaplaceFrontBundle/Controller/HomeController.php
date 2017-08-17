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

    public function __construct(ProductListService $productListService, BannerService $bannerService)
    {
        $this->productListService = $productListService;
        $this->bannerService = $bannerService;
    }


    public function homeAction(): Response
    {
        // latest products
        $latestProducts = $this->productListService->getLatestProducts();

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
