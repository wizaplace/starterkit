<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\Cms\CmsService;
use Wizaplace\SDK\Cms\Page;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugTargetType;

class CmsPageService
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

    public function getCmsPageFromSlug(string $slug): ?Page
    {
        $slugTarget = $this->seoService->resolveSlug($slug);
        if (is_null($slugTarget) || !$slugTarget->getObjectType()->equals(SlugTargetType::CMS_PAGE())) {
            return null;
        }

        return $this->cmsService->getPage((int) $slugTarget->getObjectId());
    }
}
