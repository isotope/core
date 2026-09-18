<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Store\AddressManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_addressbook', category: 'isotope')]
class AddressbookController extends AbstractContentElementController
{
    public function __construct(private readonly AddressManager $addressManager)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($this->isBackendScope($request)) {
            return new Response();
        }

        $context = $this->getStoreContext();

        if (!$context->getMemberId()) {
            return new Response('');
        }

        $addressConfig = $context
            ->getBillingAddressConfig()
            ->intersectWith($context->getShippingAddressConfig())
        ;

        $template->address_manager = $this->addressManager->generate($request, $context->getMemberId(), $context->getStoreId(), $addressConfig, 'cte'.$model->id);

        return $template->getResponse();
    }
}
