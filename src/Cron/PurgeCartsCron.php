<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Remove carts that have not been accessed for a given number of days.
 */
#[AsCronJob('daily')]
class PurgeCartsCron
{
    public function __construct(
        private readonly ProductCollectionStorage $storage,
        private readonly LoggerInterface $contaoCronlogger,
        #[Autowire(param: 'isotope.cart_timeout')] private readonly string $cartTimeout,
    ) {
    }

    public function __invoke(): void
    {
        $t = ProductCollectionModel::getTable();

        $carts = ProductCollectionModel::findBy(
            ["$t.type=? AND (($t.member=0 AND $t.tstamp<?) OR ($t.member > 0 AND $t.member NOT IN (SELECT id FROM tl_member)))"],
            [Cart::getType(), time() - strtotime($this->cartTimeout, 0)],
        );

        if (!$carts) {
            return;
        }

        $total = 0;

        foreach ($carts as $cart) {
            if ($this->storage->delete($cart)) {
                ++$total;
            }
        }

        $this->contaoCronlogger->info('Deleted '.$total.' old guest carts.');
    }
}
