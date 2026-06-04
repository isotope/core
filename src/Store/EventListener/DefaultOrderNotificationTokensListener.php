<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\EventListener;

use Codefog\HasteBundle\Formatter;
use Contao\MemberModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Model\OrderStatusModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\CollectOrderNotificationTokensEvent;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @internal
 */
#[AsEventListener(priority: 100)]
class DefaultOrderNotificationTokensListener
{
    public function __construct(
        private readonly Formatter $formatter,
        private readonly PriceFormatter $priceFormatter,
        private readonly AddressFormatter $addressFormatter,
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(CollectOrderNotificationTokensEvent $event): void
    {
        $order = $event->getOrder();
        $context = $order->getContext();
        $member = $context->getMemberId() ? MemberModel::findById($context->getMemberId()) : null;

        $tokens['uniqid'] = $order->getIdentifier();
        $tokens['document_number'] = $order->getDocumentNumber();
        $tokens['recipient_email'] = $this->getEmailRecipient($order, $context, $member);
        $tokens['order_id'] = $order->getId();
        $tokens['order_items'] = $order->getLineItems()->sumQuantity();
        $tokens['order_products'] = $order->getLineItems()->count();
        $tokens['order_subtotal']  = $order->getSubtotal() ? $this->priceFormatter->toText($order->getSubtotal(), $context) : '';
        $tokens['order_total'] = $order->getTotal() ? $this->priceFormatter->toText($order->getTotal(), $context) : '';

        if ($config = ConfigModel::findById($context->getConfigId())) {
            $tokens['bank_name'] = $config->bankName;
            $tokens['bank_account'] = $config->bankAccount;
            $tokens['bank_code'] = $config->bankCode;
            $tokens['tax_number'] = $config->taxNumber;

            foreach ($config->row() as $k => $v) {
                $tokens['config_' . $k] = $this->formatter->dcaValue(ConfigModel::getTable(), $k, $v);
            }
        }

        $statuses = [
            'order' => $order->getOrderStatus(),
            'transaction' => $order->getTransactionStatus(),
            'delivery' => $order->getDeliveryStatus(),
        ];

        foreach ($statuses as $type => $status) {
            if ($status) {
                foreach ($status->row() as $k => $v) {
                    $tokens[$type.'_status_' . $k] = $this->formatter->dcaValue(OrderStatusModel::getTable(), $k, $v);
                }

                $tokens[$type.'_status'] = $this->translator->trans($status->label ?: $status->name, [], 'isotope');
            }
        }

        $addresses = ['billing' => $order->getBillingAddress(), 'shipping' => $order->getShippingAddress()];

        foreach ($addresses as $type => $address) {
            if ($address) {
                foreach ($address->row() as $k => $v) {
                    $tokens[$type.'_address_'.$k] = $this->formatter->dcaValue(AddressModel::getTable(), $k, $v);
                }

                $tokens[$type.'_address'] = $this->addressFormatter->toHtmlString($address, $context->getBillingAddressConfig());
            }
        }

        if ($member) {
            foreach ($member->row() as $k => $v) {
                $tokens['member_' . $k] = $this->formatter->dcaValue(MemberModel::getTable(), $k, $v);
            }
        }

        $template = $event->getNotificationConfig()->get('iso_collectionTpl') ?: 'isotope/product_collection';

        $tokens['cart_html'] = $this->twig->render(
            '@Contao/'.$template.'.html.twig',
            [
                'collection' => $order,
                'is_notification' => true,
                'is_text' => false,
            ],
        );

        $tokens['cart_text'] = $this->twig->render(
            '@Contao/'.$template.'.html.twig',
            [
                'collection' => $order,
                'is_notification' => true,
                'is_text' => true,
            ],
        );

        $event->addTokens($tokens);
    }

    private function getEmailRecipient(Order $order, StoreContext $context, MemberModel|null $member): string|null
    {
        $sources = [
            array_intersect_key($order->getBillingAddress()?->row() ?? [], array_flip($context->getBillingAddressConfig()->getFields())),
            array_intersect_key($order->getShippingAddress()?->row() ?? [], array_flip($context->getShippingAddressConfig()->getFields())),
            $member?->row() ?? [],
        ];

        foreach ($sources as $data) {
            if (empty($data['email'])) {
                continue;
            }

            if ('' !== trim($name = (($data['firstname'] ?? '').' '.($data['lastname'] ?? '')))) {
                // Romanize friendly name to prevent email issues
                $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
                $name = StringUtil::stripInsertTags($name);
                $name = preg_replace('/[^A-Za-z0-9.!#$%&\'*+-\/=?^_ `{|}~]+/i', '_', $name);

                return sprintf('"%s" <%s>', $name, $data['email']);
            }

            return $data['email'];
        }

        return null;
    }
}
