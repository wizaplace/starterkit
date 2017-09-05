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
use Wizaplace\MailingList\Exception\MailingListDoesNotExist;
use Wizaplace\MailingList\Exception\UserAlreadySubscribed;
use Wizaplace\MailingList\MailingListService;

class NewsletterController extends Controller
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function subscribeAction(Request $request): JsonResponse
    {
        $mailingListId = $request->request->getInt('newsletter_id');
        if (! $mailingListId) {
            $mailingListId = 1; // default marketplace mailing list id is 1
        }
        $email = $request->request->get('email');
        $mailingListService = $this->get(MailingListService::class);

        try {
            $mailingListService->subscribe($mailingListId, $email);
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

        return new JsonResponse($email);
    }
}
