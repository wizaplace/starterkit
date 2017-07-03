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
use Wizaplace\Cms\CmsService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class CmsController extends Controller
{
    /** @var CmsService */
    private $cmsService;

    /** @var SeoService */
    private $seoService;

    public function __construct(CmsService $cmsService, SeoService $seoService)
    {
        $this->cmsService = $cmsService;
        $this->seoService = $seoService;
    }

    public function pageAction(string $slug): Response
    {
        $slugTarget = $this->seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::CMS_PAGE()) {
            throw $this->createNotFoundException("Page '${slug}' Not Found");
        }
        $pageId = (int) $slugTarget->getObjectId();

        $page = $this->cmsService->getPage($pageId);

        return $this->render('cms/page.html.twig', [
            'page' => $page,
        ]);
    }
}
