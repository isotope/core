<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Util\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_orderhistory', category: 'isotope')]
class OrderHistoryController extends AbstractContentElementController
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $user = $this->getUser();
        $configIds = StringUtil::csv($model->iso_config_ids, intval(...));

        if (!$configIds || !$user instanceof FrontendUser) {
            return new Response();
        }

        $orders = [];
        $models = ProductCollection::findBy(
            [
                'order_status IS NOT NULL',
                'locked IS NOT NULL',
                'type = ?',
                'member = ?',
                'config_id IN (' . implode(',', $configIds) . ')'
            ],
            [Order::getType(), $user->id],
            ['order' => 'locked DESC']
        );

        if (null !== $models) {
            foreach ($models as $collection) {
                $orders[] = $this->orderFactory->load($collection);
            }
        }

        $template->orders = $orders;

        return $template->getResponse();
    }
}
