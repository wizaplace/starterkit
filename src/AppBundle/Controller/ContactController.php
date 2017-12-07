<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\Discussion\DiscussionService;

class ContactController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var DiscussionService
     */
    private $discussionService;

    public function __construct(TranslatorInterface $translator, DiscussionService $discussionService)
    {
        $this->translator = $translator;
        $this->discussionService = $discussionService;
    }

    public function contactAction(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            $errorMessages = [];
            if (!$email = $request->request->get('email')) {
                $errorMessages[] = $this->translator->trans('contact.error.missing_email');
            }

            if (!$subject = $request->request->get('subject')) {
                $errorMessages[] = $this->translator->trans('contact.error.missing_subject');
            }

            if (!$message = $request->request->get('message')) {
                $errorMessages[] = $this->translator->trans('contact.error.missing_email');
            }

            if (count($errorMessages) > 0) {
                foreach ($errorMessages as $errorMessage) {
                    $this->addFlash('danger', $errorMessage);
                }

                return $this->redirectToRoute('contact');
            }

            $this->discussionService->submitContactRequest(
                $email,
                $subject,
                $message
            );
            $message = $this->translator->trans('contact.success_message');
            $this->addFlash('success', $message);

            return $this->redirectToRoute('home');
        }

        return $this->render('@App/pages/contact.html.twig');
    }
}
