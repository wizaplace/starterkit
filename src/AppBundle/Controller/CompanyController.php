<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Catalog\Review\ReviewService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class CompanyController extends Controller
{
    public function viewAction(string $slug): Response
    {
        $seoService = $this->get(SeoService::class);
        $slugTarget = $seoService->resolveSlug($slug);

        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::COMPANY()) {
            throw $this->createNotFoundException("Company '${slug}' Not Found'");
        }
        $companyId = (int) $slugTarget->getObjectId();

        $company = $this->get(CatalogService::class)->getCompanyById($companyId);
        $filters = [];
        $filters['companies'] = $companyId;

        $reviewService = $this->get(ReviewService::class);
        $reviews = $reviewService->getCompanyReviews($companyId);
        $canUserReviewCompany = $reviewService->canUserReviewCompany($companyId);

        return $this->render('@App/company/company.html.twig', [
            'filters' => $filters,
            'company' => $company,
            'reviews' => $reviews,
            'canUserReviewCompany' => $canUserReviewCompany,
        ]);
    }

    public function reviewAction(ReviewService $reviewService, Request $request) : RedirectResponse
    {
        $reviewService->reviewCompany(
            (int) $request->request->get('company_id'),
            (string) $request->request->get('message'),
            (int) $request->request->get('rating')
        );

        return $this->redirect($request->request->get('redirect_url'));
    }
}
