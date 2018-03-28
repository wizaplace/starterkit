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

    public function createProductAction(Request $request): Response
    {
        if (!$this->isAccessGranted()) {
            return $this->redirectToRoute('home');
        }

        if ($request->getMethod() === 'POST') {
            $command = $this->newCreateProductCommandFromRequest($request);

            $this->productService->createProduct($command);
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

    private function newCreateProductCommandFromRequest(Request $request): CreateProductCommand
    {
        $productName = $request->get('product_name');
        $code = $request->get('code');
        $supplierReference = $request->get('supplier_reference');
        $status = $request->get('status');
        $mainCategory = (int) $request->get('main_category');
        $greenTax = (float) $request->get('green_tax');
        $isBrandNew = (bool) $request->get('is_brand_new');
        $geolocation = $request->get('geolocation');
        $freeAttributes = $request->get('free_attributes') ?? [];
        $hasFreeShipping = $request->get('has_free_shipping') ?? false;
        $weight = (float) $request->get('weight');
        $isDownloadable = $request->get('is_downloadable') ?? false;
        $affiliateLink = $request->get('affiliate_link');
        $mainImage = $request->files->get('main_image');
        $additionalImages = $request->files->get('additional_images') ?? [];
        $fullDescription = $request->get('full_description');
        $shortDescription = $request->get('short_description');
        $taxIds = (array) $request->get('tax_ids');
        $declinations = $request->get('declinations') ?? [];
        $attachments = $request->files->get('attachments');
        $availabilityDate = $request->get('availability_date');

        $createProductCommand = new CreateProductCommand();
        $createProductCommand->setName($productName);
        $createProductCommand->setCode($code);
        if ($supplierReference !== null) {
            $createProductCommand->setSupplierReference($supplierReference);
        }
        $createProductCommand->setStatus(new ProductStatus($status));
        $createProductCommand->setMainCategoryId($mainCategory);
        $createProductCommand->setGreenTax($greenTax);
        if ($isBrandNew !== null) {
            $createProductCommand->setIsBrandNew($isBrandNew);
        }
        if ($geolocation !== null) {
            $geoloc = new ProductGeolocationUpsertData(
                $request->get('latitude'),
                $request->get('longitude'));
            $geoloc->setLabel($request->get('label'));
            $geoloc->setZipcode($request->get('zipcode'));
            $createProductCommand->setGeolocation($geoloc);
        }
        if ($freeAttributes !== null) {
            $createProductCommand->setFreeAttributes($freeAttributes);
        }
        if ($hasFreeShipping !== null) {
            $createProductCommand->setHasFreeShipping($hasFreeShipping);
        }
        if ($weight !== null) {
            $createProductCommand->setWeight($weight);
        }
        if ($isDownloadable !== null) {
            $createProductCommand->setIsDownloadable($isDownloadable);
        }
        if ($affiliateLink !== null) {
            $createProductCommand->setAffiliateLink($affiliateLink);
        }
        if ($mainImage !== null) {
            $createProductCommand->setMainImage($mainImage);
        }
        if ($additionalImages !== null) {
            $createProductCommand->setAdditionalImages($additionalImages);
        }
        if ($fullDescription !== null) {
            $createProductCommand->setFullDescription($fullDescription);
        }
        if ($shortDescription !== null) {
            $createProductCommand->setShortDescription($shortDescription);
        }
        $createProductCommand->setTaxIds($taxIds);
        if ($declinations !== null) {
            $createProductCommand->setDeclinations($declinations);
        }
        if ($attachments !== null) {
            $createProductCommand->setAttachments($attachments);
        }
        if ($availabilityDate !== null) {
            $createProductCommand->setAvailabilityDate($availabilityDate);
        }

        return $createProductCommand;
    }
}
