<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\CartCreatedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class CartFactory
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProductCollectionStorage $storage,
    ) {
    }

    public function load(StoreContext $context): Cart|null
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

        $cart = $this->create($context);

        return $this->storage->load($cart, $model, $context);
    }

    public function create(StoreContext $context): Cart
    {
        $cart = new Cart($context->getIdentifier());

        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart, $context));

        return $cart;
    }
}
