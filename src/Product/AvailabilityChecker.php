<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\StringUtil;

class AvailabilityChecker
{
    public function __construct(
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    public function isAvailable(CollectibleInterface $product, MemberModel|FrontendUser|int|null $member = null): bool
    {
        $time = time();

        if (!$product instanceof ProductInterface) {
            // TODO: add check for collectibles
            return true;
        }

        if (
            (
                !$product['published']
                || ('' !== $product['start'] && $product['start'] > $time)
                || ('' !== $product['stop'] && $product['stop'] < $time)
            )
            && !$this->tokenChecker->isPreviewMode()
        ) {
            return false;
        }

        $attributes = $product->getProductType()?->getAttributes() ?? [];

        if (
            \in_array('protected', $attributes, true)
            && \in_array('groups', $attributes, true)
            && $product['protected']
            && !$this->isGranted($member, $product['groups'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * TODO: use the security voters here
     */
    public static function isGranted(MemberModel|FrontendUser|int|null $member, array|string $groups): bool
    {
        $groups = StringUtil::deserialize($groups, true);

        if (is_int($member) && $member > 0) {
            $member = MemberModel::findByPk($member);
        }

        if (!$member) {
            return \in_array(-1, array_map(\intval(...), $groups), true);
        }

        return [] !== array_intersect($groups, StringUtil::deserialize($member->groups, true));
    }
}
