<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugTargetType;
use WizaplaceFrontBundle\Service\FavoriteService;

class AttributeController extends Controller
{
    public function viewVariantAction(string $slug): Response
    {
        $seoService = $this->get(SeoService::class);
        $slugTarget = $seoService->resolveSlug($slug);
        if (!$slugTarget || !$slugTarget->getObjectType()->equals(SlugTargetType::ATTRIBUTE_VARIANT())) {
            throw $this->createNotFoundException('Variant '.$slug.' not found');
        }
        $selectedVariantId = $slugTarget->getObjectId();

        $catalogService = $this->get(CatalogService::class);
        $selectedVariant = $catalogService->getAttributeVariant((int) $selectedVariantId);

        $filters = [];
        $filters[$selectedVariant->getAttributeId()] = $selectedVariantId;

        $userFavoriteIds = [];
        if ($this->getUser()) {
            $userFavoriteIds = $this->get(FavoriteService::class)->getFavoriteIds();
        }

        return $this->render('@App/attribute/variant-attribute.html.twig', [
            'filters' => $filters,
            'selectedVariant' => $selectedVariant,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }
}
