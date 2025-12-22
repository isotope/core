<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\Model\Address;

/**
 * @implements AbstractProductCollectionStorage<Cart>
 */
class CartStorage extends AbstractProductCollectionStorage
{
    public function __construct(
        Connection $connection,
        private readonly CartFactory $cartFactory,
    ) {
        parent::__construct($connection);
    }

    public function load(StoreContext $context): AbstractProductCollection|null
    {
        $memberCart = $context->getMemberId() ? ProductCollection::findByMember($context->getMemberId(), Cart::COLLECTION_TYPE, $context->getStoreId()) : null;
        $guestCart = ProductCollection::findByIdentifier($context->getIdentifier(), Cart::COLLECTION_TYPE, $context->getStoreId());

        if ($memberCart) {
            $model = $memberCart;

            if ($guestCart) {
                // TODO: merge guest cart
            }
        } elseif ($guestCart) {
            $model = $guestCart;
        } else {
            return null;
        }

        // TODO: merge guest cart
        //$strHash = (string) Input::cookie(static::$strCookie);
        //
        //// Temporary cart available, move to this cart. Must be after creating a new cart!
        //if (\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER') && '' !== $strHash && $this->member > 0) {
        //    $blnMerge = $this->countItems() > 0;
        //    $objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $this->store_id));
        //
        //    if (null !== $objTemp) {
        //        $arrIds = $this->copyItemsFrom($objTemp);
        //
        //        if ($blnMerge && \count($arrIds) > 0) {
        //            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['cartMerged']);
        //        }
        //
        //        $objTemp->delete();
        //    }
        //
        //    // Delete cookie
        //    System::setCookie(static::$strCookie, '', time() - 3600);
        //    Controller::reload();
        //}

        return $this->cartFactory->create($context, $model);
    }

    public function save(AbstractProductCollection $collection, StoreContext $context): void
    {
        if (!$collection instanceof Cart) {
            throw new \InvalidArgumentException('Argument must be a cart');
        }

        $cartModel = ProductCollection::findByIdentifier($collection->getIdentifier(), Cart::COLLECTION_TYPE, $context->getStoreId());

        if (!$cartModel) {
            $cartModel = new ProductCollection();
            $cartModel->uniqid = $collection->getIdentifier();
            $cartModel->type = Cart::COLLECTION_TYPE;
            $cartModel->store_id = $context->getStoreId();
        }

        $cartModel->tstamp = time();
        $cartModel->config_id = $context->getConfigId();
        $cartModel->language = $context->getLocale();
        $cartModel->currency = $context->getCurrency()->getCode();

        $cartModel->billing_address_id = $this->saveAddress($collection->getBillingAddress(), $cartModel);
        $cartModel->shipping_address_id = $this->saveAddress($collection->getShippingAddress(), $cartModel);

        // TODO: save price etc.
        $cartModel->save();

        $itemIds = [0];

        foreach ($collection->getLineItems() as $lineItem) {
            if ($lineItem->isDynamic()) {
                continue;
            }

            $itemModel = ProductCollectionItem::findByOneCollection($cartModel->id, $lineItem->getIdentifier(), $lineItem->getConfiguration());

            if (!$itemModel) {
                $itemModel = new ProductCollectionItem();
            }

            $itemModel->pid = $cartModel->id;
            $itemModel->tstamp = time();
            $itemModel->type = $lineItem->getType();
            $itemModel->identifier = $lineItem->getIdentifier();
            $itemModel->configuration = $lineItem->getConfiguration();
            $itemModel->quantity = $lineItem->getQuantity();
            $itemModel->name = $lineItem->getLabel();
            $itemModel->sku = $lineItem->getSku();

            if ($product = $lineItem->getProduct()) {
                //$itemModel->product_id = $product->getId();
                //$itemModel->type = $product->getType();
                $itemModel->name = $product->getName();
                $itemModel->sku = $product->getSku();
                // TODO: save price etc.
            }

            $itemModel->save();
            $itemIds[] = $itemModel->id;
        }

        $this->connection->executeStatement(
            'DELETE FROM tl_iso_product_collection_item WHERE pid=? AND id NOT IN (?)',
            [$cartModel->id, $itemIds],
            [Types::INTEGER, ArrayParameterType::INTEGER],
        );
    }

    private function saveAddress(Address|null $address, ProductCollection $collection): int
    {
        if (null === $address) {
            return 0;
        }

        if ($address->id) {
            $address->save();

            return (int) $address->id;
        }

        if (!$collection->id) {
            $collection->save();
        }

        $address->tstamp = time();
        $address->ptable = 'tl_iso_product_collection';
        $address->pid = $collection->id;
        $address->store_id = $collection->store_id;
        $address->save();

        return (int) $address->id;
    }
}
