<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Service\CmsPageService;

class CmsController extends Controller
{
    /** @var CmsPageService */
    protected $cmsPageService;

    public function __construct(CmsPageService $cmsPageService)
    {
        $this->cmsPageService = $cmsPageService;
    }

    public function pageAction(string $slug): Response
    {
        $page = $this->cmsPageService->getCmsPageFromSlug($slug);
        if (!$page) {
            throw $this->createNotFoundException("Page '${slug}' Not Found");
        }

        return $this->render('@WizaplaceFront/cms/page.html.twig', [
            'page' => $page,
        ]);
    }
}
