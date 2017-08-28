<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\Discussion\DiscussionService;

class DiscussionController extends Controller
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function createAction(Request $request): Response
    {
        // CSRF token validation
        $referer = $request->headers->get('referer') ?? $request->request->get('return_url');
        $submittedToken = $request->request->get('csrf_token');

        if (! $this->isCsrfTokenValid('discussion_token', $submittedToken)) {
            $notification = $this->translator->trans('csrf_error_message');
            $this->addFlash('warning', $notification);

            return $this->redirect($referer);
        }

        // create a new discussion and post message
        $productId = (int) $request->request->get('product_id');
        $discussionMessage = $request->get('discussion_message');
        $discussionService = $this->get(DiscussionService::class);
        $discussionData = $discussionService->startDiscussion($productId);

        try {
            $discussionService->postMessage($discussionData->getId(), $discussionMessage);
        } catch (\Exception $exception) {
            // add alert notification
            $notification = $this->translator->trans('message_sent_error_notification');
            $this->addFlash('danger', $notification);

            return $this->redirect($referer);
        }

        // add success notification
        $notification = $this->translator->trans('message_sent_success_notification');
        $this->addFlash('success', $notification);

        // redirect user to product page
        return $this->redirect($referer);
    }

    public function createMessageAction(Request $request): Response
    {
        // CSRF token validation
        $referer = $request->headers->get('referer');
        $submittedToken = $request->request->get('csrf_token');

        if (! $this->isCsrfTokenValid('discussion_token', $submittedToken)) {
            $notification = $this->translator->trans('csrf_error_message');
            $this->addFlash('warning', $notification);

            return $this->redirect($referer);
        }

        // add a message to the discussion
        $discussionId = (int) $request->get('id');
        $discussionMessage = $request->get('discussion_message');
        $discussionService = $this->get(DiscussionService::class);

        try {
            $discussionService->postMessage($discussionId, $discussionMessage);
        } catch (\Exception $exception) {
            // add alert notification
            $notification = $this->translator->trans('message_sent_error_notification');
            $this->addFlash('danger', $notification);

            return $this->redirect($referer);
        }

        // add success notification
        $notification = $this->translator->trans('message_sent_success_notification');
        $this->addFlash('success', $notification);

        return $this->redirectToRoute('profile_discussion', [
            'id' => $discussionId,
        ]);
    }
}
