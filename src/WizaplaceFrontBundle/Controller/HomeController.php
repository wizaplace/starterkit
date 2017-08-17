<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Cms\BannerService;

class HomeController extends Controller
{
    public function homeAction(): Response
    {
        // get services from sdk
        $catalogService = $this->get(CatalogService::class);
        $bannerService = $this->get(BannerService::class);

        // latest products
        $latestProducts = $catalogService->search('', [], ['createdAt' => 'desc'], 6)->getProducts();

        // banners
        $desktopBanners = $bannerService->getHomepageBanners("desktop");
        $mobileBanners = $bannerService->getHomepageBanners("mobile");

        return $this->render('@WizaplaceFront/home/home.html.twig', [
            'latestProducts' => $latestProducts,
            'desktopBanners' => $desktopBanners,
            'mobileBanners' => $mobileBanners,
        ]);
    }
}
