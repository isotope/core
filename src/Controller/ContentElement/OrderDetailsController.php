<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Contao\StringUtil;
use Isotope\Model\ProductCollection\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_orderdetails', category: 'isotope')]
class OrderDetailsController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $collection = Order::findOneBy('uniqid', $request->query->getString('uid'));

        if (!$collection) {
            throw new PageNotFoundException();
        }

        if ($collection->member > 0) {
            $user = $this->getUser();

            if (
                (!$user instanceof FrontendUser && $model->iso_loginRequired)
                || ($user instanceof FrontendUser && (int) $user->id !== (int) $collection->member)
            ) {
                throw new AccessDeniedException();
            }
        }

        $template->collection = $collection;
        $template->info = StringUtil::deserialize($collection->checkout_info, true);

        return $template->getResponse();
    }
}
