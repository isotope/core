<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\FrontendUser;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Context\StoreContextException;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Config;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[Autoconfigure(public: true)]
class StoreManager
{
    public const string CONTEXT_COOKIE = 'ISOTOPE_CONTEXT';

    public const string CONTEXT_ATTRIBUTE = '_isotope_store_context';

    public const string CART_ATTRIBUTE = '_isotope_cart';

    private StoreContext|null $runWithContext = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RequestStack $requestStack,
        private readonly PageFinder $pageFinder,
        private readonly Security $security,
        private readonly StoreContextFactory $contextFactory,
    ) {
    }

    public function getCurrentContext(): StoreContext
    {
        // TODO: StoreContext contains data from shop config and cart;
        //  current member, price calculation, currency, country, shipping and payment, tax etc.

        if ($this->runWithContext) {
            return $this->runWithContext;
        }

        if (($context = $this->requestStack->getCurrentRequest()?->attributes->get(self::CONTEXT_ATTRIBUTE)) instanceof StoreContext) {
            return $context;
        }

        $request = $this->requestStack->getMainRequest();

        if (!$request) {
            throw StoreContextException::noRequestAvailable();
        }

        if (($context = $request->attributes->get(self::CONTEXT_ATTRIBUTE)) instanceof StoreContext) {
            return $context;
        }

        $context = $this->createForRequest($request);
        $request->attributes->set(self::CONTEXT_ATTRIBUTE, $context);

        return $context;
    }

    public function runWithContext(StoreContext $context, \Closure $closure): mixed
    {
        try {
            $this->runWithContext = $context;

            return $closure($context);
        } finally {
            $this->runWithContext = null;
        }
    }

    public function runWithCollection(IsotopeProductCollection $collection, \Closure $closure): mixed
    {
        $context = $this->contextFactory->create(
            $collection->getStoreId(),
            $collection->getConfig(),
            $collection->language ?? $GLOBALS['TL_LANGUAGE'], // TODO: a collection must always have a language!
            $collection->getUniqueId() ?? $this->generateCookieId(), // TODO: a collection must always have an ID!
            $collection->getMember()?->id
        );

        return $this->runWithContext($context, $closure);
    }

    private function createForRequest(Request $request): StoreContext
    {
        $rootPage = $this->pageFinder->findRootPageForRequest($request);

        if (!$rootPage) {
            throw StoreContextException::noRootPage();
        }

        $this->framework->initialize();

        $config = null;
        $storeId = (int) $rootPage->iso_store_id;
        $memberId = $this->getMemberId();
        $guestCartId = $request->cookies->get(self::CONTEXT_COOKIE);

        if ($memberId) {
            $config = Config::findByMemberCart($memberId, $storeId);
        }

        if (!$config && $guestCartId) {
            $config = Config::findByGuestCart($guestCartId, $storeId);
        }

        if (!$config) {
            $config = Config::findByRootPage((int) $rootPage->id);
        }

        if (!$config) {
            throw StoreContextException::noConfig();
        }

        return $this->contextFactory->create(
            (int) $rootPage->iso_store_id,
            $config,
            $request->getLocale(),
            $guestCartId ?? $this->generateCookieId(),
            $memberId,
        );
    }

    private function getMemberId(): int|null
    {
        $user = $this->security->getUser();

        if ($user instanceof FrontendUser) {
            return (int) $user->id;
        }

        return null;
    }

    private function generateCookieId(): string
    {
        if (!function_exists('random_bytes')) {
            return uniqid('', true);
        }

        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception) {
            return uniqid('', true);
        }
    }
}
