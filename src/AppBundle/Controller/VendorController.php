<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\Catalog\CatalogServiceInterface;
use Wizaplace\SDK\Catalog\Category;
use Wizaplace\SDK\Pim\Product\CreateProductCommand;
use Wizaplace\SDK\Pim\Product\ProductGeolocationUpsertData;
use Wizaplace\SDK\Pim\Product\ProductService;
use Wizaplace\SDK\Pim\Product\ProductStatus;

class VendorController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * @var CatalogServiceInterface
     */
    private $catalogService;

    public function __construct(
        TranslatorInterface $translator,
        ProductService $productService,
        CatalogServiceInterface $catalogService
    ) {
        $this->translator = $translator;
        $this->productService = $productService;
        $this->catalogService = $catalogService;
    }

    public function dashboardSummaryAction(): Response
    {
        if (!$this->isAccessGranted()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('@App/vendor/dashboard-summary.html.twig');
    }

    public function createProductAction(): Response
    {
        if (!$this->isAccessGranted()) {
            return $this->redirectToRoute('home');
        }

        $statusList = ProductStatus::toArray();
        $categoriesList = $this->catalogService->getCategories();

        $productStatusList = [];
        foreach ($statusList as $status) {
            $productStatusList[] = [
                'value' => $status,
                'name'  => $this->translator->trans('vendor.products.creation.status.'.$status),
            ];
        }
        $categoriesList = array_map(static function ($category){
            /** @var Category $category */
            return [
                'value' => $category->getId(),
                'name'  => $category->getName(),
            ];
        }, $categoriesList);
        $taxList = [ //TODO: récupérer les vraies taxes de l'API via le SDK
            [
                'value' => 0,
                'name' => 'TVA 20%',
            ]
        ];

        return $this->render('@App/vendor/products/create.html.twig', [
            'productStatusList' => $productStatusList,
            'categoriesList'    => $categoriesList,
            'taxList'           => $taxList,
        ]);
    }

    /**
     * User needs to be a vendor to access this controller
     * @throws AccessDeniedException
     */
    private function isAccessGranted(): bool
    {
        if (!$this->getParameter('is_bof_available')) {
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();
        if (!$user || !$user->isVendor()) {
            $message = $this->translator->trans('vendor.access_denied.not_a_vendor');
            $this->addFlash('danger', $message);

            return false;
        }

        return true;
    }
}
