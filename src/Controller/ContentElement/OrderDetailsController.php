<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_orderdetails', category: 'isotope')]
class OrderDetailsController extends AbstractContentElementController
{
    public function __construct(
        private readonly OrderFactory $loader,
        private readonly AddressFormatter $addressFormatter,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $order = $this->loader->loadFromIdentifier($request->query->getString('uid'));

        if (!$order) {
            throw new PageNotFoundException();
        }

        $context = $order->getContext();

        if ($context->getMemberId() > 0) {
            $user = $this->getUser();

            if (
                (!$user instanceof FrontendUser && $model->iso_loginRequired)
                || ($user instanceof FrontendUser && (int) $user->id !== $context->getMemberId())
            ) {
                throw new AccessDeniedException();
            }
        }

        $template->order = $order;

        return $template->getResponse();
    }
}
