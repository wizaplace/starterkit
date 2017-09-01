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
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class AttributeController extends Controller
{
    public function viewVariantAction(string $slug): Response
    {
        $seoService = $this->get(SeoService::class);
        $slugTarget = $seoService->resolveSlug($slug);
        if (!$slugTarget || $slugTarget->getObjectType() != SlugTargetType::ATTRIBUTE_VARIANT()) {
            throw $this->createNotFoundException('Variant '.$slug.' not found');
        }
        $selectedVariantId = $slugTarget->getObjectId();

        $catalogService = $this->get(CatalogService::class);
        $selectedVariant = $catalogService->getAttributeVariant($selectedVariantId);

        $filters = [];
        $filters[$selectedVariant->getAttributeId()] = $selectedVariantId;

        return $this->render('@App/attribute/variant-attribute.html.twig', [
            'filters' => $filters,
            'selectedVariant' => $selectedVariant,
        ]);
    }
}
