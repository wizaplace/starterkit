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
use Wizaplace\SDK\Pim\Category\Category;
use Wizaplace\SDK\Pim\Category\CategoryService;
use Wizaplace\SDK\Pim\Product\CreateProductCommand;
use Wizaplace\SDK\Pim\Product\ProductDeclinationUpsertData;
use Wizaplace\SDK\Pim\Product\ProductGeolocationUpsertData;
use Wizaplace\SDK\Pim\Product\ProductService;
use Wizaplace\SDK\Pim\Product\ProductStatus;
use Wizaplace\SDK\Pim\Tax\Tax;
use Wizaplace\SDK\Pim\Tax\TaxService;

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
     * @var TaxService
     */
    private $taxService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    public function __construct(
        TranslatorInterface $translator,
        ProductService $productService,
        TaxService $taxService,
        CategoryService $categoryService
    ) {
        $this->translator = $translator;
        $this->productService = $productService;
        $this->taxService = $taxService;
        $this->categoryService = $categoryService;
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
            $message = $this->translator->trans('vendor.products.notifications.success.product_created');
            $this->addFlash('success', $message);

            return $this->redirectToRoute('profile_vendor_dashboard');
        }

        $statusList = ProductStatus::toArray();
        $statusList = array_map(function (string $status) {
            return [
                'value' => $status,
                'name'  => $this->translator->trans('vendor.products.creation.status.'.$status),
            ];
        }, $statusList);

        $categoriesList = $this->categoryService->listCategories();
        $categoriesList = array_map(static function (Category $category) {
            return [
                'value' => $category->getId(),
                'name'  => $category->getName(),
            ];
        }, $categoriesList);

        $taxList = $this->taxService->listTaxes();
        $taxList = array_map(static function (Tax $tax) {
            return [
                'value' => $tax->getId(),
                'name'  => $tax->getName(),
            ];
        }, $taxList);

        return $this->render('@App/vendor/products/create.html.twig', [
            'productStatusList' => $statusList,
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
        $price = $request->get('price');
        $quantity = $request->get('quantity');
        $supplierReference = $request->get('supplier_reference');
        $status = $request->get('status');
        $mainCategory = (int) $request->get('main_category');
        $greenTax = (float) $request->get('green_tax');
        $isBrandNew = $request->get('is_brand_new');
        $geolocation = $request->get('geolocation');
        $freeAttributes = $request->get('free_attributes') ?? [];
        $hasFreeShipping = $request->get('has_free_shipping') ?? false;
        $weight = $request->get('weight');
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

        $productDeclinationUpsertData = new ProductDeclinationUpsertData([]);
        $productDeclinationUpsertData->setPrice( (float) $price);
        $productDeclinationUpsertData->setQuantity( (int) $quantity);

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
            $createProductCommand->setIsBrandNew((bool) $isBrandNew);
        }
        if ($geolocation !== null) {
            $geoloc = new ProductGeolocationUpsertData(
                $request->get('latitude'),
                $request->get('longitude')
            );
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
            $createProductCommand->setWeight((float) $weight);
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
        if ($declinations !== []) {
            $createProductCommand->setDeclinations($declinations);
        } else {
            $createProductCommand->setDeclinations([$productDeclinationUpsertData]);
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
