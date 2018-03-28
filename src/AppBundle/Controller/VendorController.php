<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class VendorController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function dashboardSummaryAction(): Response
    {
        if (!$this->isAccessGranted()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('@App/vendor/dashboard-summary.html.twig');
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
