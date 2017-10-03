<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\MailingList\Exception\MailingListDoesNotExist;
use Wizaplace\SDK\MailingList\Exception\UserAlreadySubscribed;
use Wizaplace\SDK\MailingList\MailingListService;

class NewsletterController extends Controller
{
    protected const DEFAULT_MAILING_LIST_ID = 1;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function subscribeAction(Request $request): JsonResponse
    {
        $mailingListId = $request->request->getInt('newsletter_id');
        if (! $mailingListId) {
            $mailingListId = static::DEFAULT_MAILING_LIST_ID;
        }
        $email = $request->request->get('email');
        $response = $this->subscribe($mailingListId, $email);

        return $response;
    }

    public function toggleNewsletterSubscriptionAction(): JsonResponse
    {
        $mailingListService = $this->get(MailingListService::class);
        $userIsSubscribed = $mailingListService->isSubscribed(static::DEFAULT_MAILING_LIST_ID);
        $email = $this->getUser()->getWizaplaceUser()->getEmail();

        // toggle user's subscription regarding their last subscription status
        if ($userIsSubscribed) {
            $response = $this->unsubscribe(static::DEFAULT_MAILING_LIST_ID, $email);
        } else {
            $response = $this->subscribe(static::DEFAULT_MAILING_LIST_ID, $email);
        }

        return $response;
    }

    protected function subscribe(int $newsletterId, string $email): JsonResponse
    {
        $mailingListService = $this->get(MailingListService::class);

        try {
            $mailingListService->subscribe($newsletterId, $email);
        } catch (MailingListDoesNotExist $e) {
            $response = new JsonResponse();
            $message = $this->translator->trans('newsletter_not_found_error_message');
            $response->setContent($message);
            $response->setStatusCode(404);

            return $response;
        } catch (UserAlreadySubscribed $e) {
            $response = new JsonResponse();
            $message = $this->translator->trans('newsletter_already_subscribed_error_message');
            $response->setContent($message);
            $response->setStatusCode(409);

            return $response;
        }

        return new JsonResponse();
    }

    protected function unsubscribe(int $newsletterId, string $email): JsonResponse
    {
        $mailingListService = $this->get(MailingListService::class);
        $mailingListService->unsubscribe($newsletterId, $email);

        return new JsonResponse();
    }
}
