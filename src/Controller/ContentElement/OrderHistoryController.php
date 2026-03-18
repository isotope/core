<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsContentElement('iso_orderhistory', category: 'isotope')]
class OrderHistoryController extends AbstractContentElementController
{
    public function __construct(
        private readonly OrderFactory $orderFactory, private readonly CartManager $cartManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $user = $this->getUser();
        $configIds = explode(',', $model->iso_config_ids);
        $cartPage = null;

        if ([] === $configIds || !$user instanceof FrontendUser) {
            return new Response();
        }

        $orders = [];
        $models = ProductCollection::findBy(
            [
                'type = ?',
                'order_status IS NOT NULL',
                'tl_iso_product_collection.member = ?',
                'config_id IN (' . implode(',', array_map('intval', $configIds)) . ')'
            ],
            [Order::getType(), $user->id],
            ['order' => 'locked DESC']
        );

        if (null !== $models) {
            $cartPage = $model->iso_cart_jumpTo ? PageModel::findById($model->iso_cart_jumpTo) : null;

            foreach ($models as $collection) {
                $order = $this->orderFactory->load($collection);

                if ($cartPage && $order->getIdentifier() === $request->query->getString('reorder')) {
                    $this->reorder($order, $cartPage, $request);
                }

                $orders[] = $order;
            }
        }

        $template->orders = $orders;
        $template->detailPage = $model->jumpTo ? PageModel::findById($model->jumpTo) : null;
        $template->reorder = null !== $cartPage;

        return $template->getResponse();
    }

    private function reorder(Order $order, PageModel $cartPage, Request $request): never
    {
        $context = $this->getStoreContext();

        $this->cartManager->add($context->getCart(), $order->getLineItems()->toArray(), $context);

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['reorderConfirmation']);

        throw new RedirectResponseException($this->generateContentUrl(
            $cartPage,
            ['continue' => base64_encode($request->headers->get('referer'))],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ));
    }
}
