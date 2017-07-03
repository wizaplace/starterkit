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

class CategoryController extends Controller
{
    public function viewAction(
        SeoService $seoService,
        CatalogService $catalogService,
        string $slug
    ) : Response {
        $slugTarget = $seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::CATEGORY()) {
            throw $this->createNotFoundException("Category '${slug}' Not Found");
        }
        $categoryId = (int) $slugTarget->getObjectId();

        $currentCategory = $catalogService->getCategory($categoryId);
        $apiBaseUrl = $this->getParameter("api.base_url");

        $categories = $catalogService->getCategoryTree();

        $filters = [];
        $filters['categories'] = $categoryId;

        return $this->render(
            'search/search.html.twig',
            [
                    'categories' => $categories,
                    'currentCategory' => $currentCategory,
                    'filters' => $filters,
                    'apiBaseUrl' => $apiBaseUrl,
            ]
        );
    }
}
