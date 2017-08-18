<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace WizaplaceFrontBundle\Twig;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Wizaplace\Basket\Basket;
use Wizaplace\Basket\BasketService;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Cms\CmsService;
use Wizaplace\Image\Image;
use Wizaplace\Image\ImageService;
use Wizaplace\User\UserService;

class AppExtension extends \Twig_Extension
{
    /** @var CatalogService */
    private $catalogService;
    /** @var SessionInterface */
    private $session;
    /** @var UserService */
    private $userService;
    /** @var BasketService */
    private $basketService;
    /** @var ImageService */
    private $imageService;
    /** @var CmsService */
    private $cmsService;
    /** @var CacheItemPoolInterface */
    private $cache;
    /** @var string */
    private $recaptchaKey;
    /** @var Packages */
    private $assets;

    public function __construct(
        CatalogService $catalogService,
        SessionInterface $session,
        UserService $userService,
        BasketService $basketService,
        ImageService $imageService,
        CmsService $cmsService,
        CacheItemPoolInterface $cache,
        string $recaptchaKey,
        Packages $assets
    ) {
        $this->catalogService = $catalogService;
        $this->session = $session;
        $this->userService = $userService;
        $this->basketService = $basketService;
        $this->imageService = $imageService;
        $this->cmsService = $cmsService;
        $this->cache = $cache;
        $this->recaptchaKey = $recaptchaKey;
        $this->assets = $assets;
    }

    public function getFunctions()
    {
        return [
            //Le service est appelé directement pour pouvoir mettre du cache dessus.
            new \Twig_SimpleFunction('categoryTree', [$this, 'getCategoryTree']),
            new \Twig_SimpleFunction('currentUser', [$this, 'getCurrentUser']),
            new \Twig_SimpleFunction('basket', [$this, 'getBasket']),
            new \Twig_SimpleFunction('recaptchaKey', [$this, 'getRecaptchaKey']),
            new \Twig_SimpleFunction('menus', [$this->cmsService, 'getAllMenus']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('imageUrl', [$this, 'imageUrl']),
            new \Twig_SimpleFilter('price', [$this, 'formatPrice']),
        ];
    }

    /**
     * @param int|Image|null $image An Image object, an image ID or null.
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function imageUrl($image, int $width = null, int $height = null): string
    {
        if ($image === null) {
            return $this->assets->getUrl('images/no-image.jpg');
        }

        $imageId = ($image instanceof Image) ? $image->getId() : $image;

        return $this->imageService->getImageLink($imageId, $width, $height);
    }

    public function getCategoryTree():array
    {
        $categoryTree = $this->cache->getItem('categoryTree');
        if (!$categoryTree->isHit()) {
            $categoryTree->set($this->catalogService->getCategoryTree());
            $categoryTree->expiresAfter(3600);
            $this->cache->save($categoryTree);
        }

        return $categoryTree->get();
    }

    public function getBasket(): ?Basket
    {
        $basketId = $this->session->get(\AppBundle\Controller\BasketController::SESSION_BASKET_ATTRIBUTE, null);
        if (!$basketId) {
            return null;
        }
        $basket = $this->basketService->getBasket($basketId);

        return $basket;
    }

    public function getRecaptchaKey(): string
    {
        return $this->recaptchaKey;
    }

    public function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', ' ').'€';
    }
}
