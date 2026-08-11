<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Isotope\CoreBundle\Exception\RuntimeException;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Twig\Environment;

class MessagesUtil
{
    public const string TYPE_RAW = 'isotope.raw';

    public const string TYPE_SUCCESS = 'isotope.success';

    public const string TYPE_ERROR = 'isotope.error';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
    ) {
    }

    public function success(string $message): void
    {
        $this->add(self::TYPE_SUCCESS, $message);
    }

    public function error(string $message): void
    {
        $this->add(self::TYPE_ERROR, $message);
    }

    public function raw(TemplateMarkup $markup): void
    {
        $this->add(self::TYPE_RAW, (string) $markup);
    }

    public function add(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        if (!$session instanceof FlashBagAwareSessionInterface) {
            throw new RuntimeException('The current session does not support flash messages.');
        }

        $session->getFlashBag()->add($type, $message);
    }

    public function handleActionResult(string $type, ActionResult $result, string|null $redirectUrl = null): never
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new RuntimeException('Cannot handle action result without a request.');
        }

        if ($request->isXmlHttpRequest()) {
            $this->throwJsonResponse($type, $result);
        }

        if (
            $result->message
            && ($session = $request->getSession()) instanceof FlashBagAwareSessionInterface
        ) {
            $type = match (true) {
                $result->message instanceof TemplateMarkup => 'isotope.raw',
                $result->success => 'isotope.success',
                !$result->success => 'isotope.error',
            };

            $session->getFlashBag()->add($type, (string) $result->message);
        }

        if ($result->redirect) {
            throw new RedirectResponseException($result->redirect);
        }

        throw new RedirectResponseException($redirectUrl ?? $request->getUri());
    }

    private function throwJsonResponse(string $type, ActionResult $result): never
    {
        $message = null;

        if ($result->message instanceof TemplateMarkup) {
            $message = (string) $result->message;
        } elseif ($result->message) {
            $message = $this->twig->render('@Contao/component/_isotope_message.html.twig', [
                'type' => $type,
                'message' => $result->message,
            ]);
        }

        $response = new JsonResponse(
            [
                'type' => $type,
                'success' => $result->success,
                'message' => $message,
                'redirect' => $result->redirect,
            ],
            200,
            ['Content-Type' => 'application/isotope-action+json'],
        );

        throw new ResponseException($response);
    }
}
