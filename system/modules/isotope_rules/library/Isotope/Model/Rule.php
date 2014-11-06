<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Translation;

/**
 * Class Payment
 *
 * Implements payment surcharge in product collection
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $discount
 * @property string $tax_class
 * @property string $applyTo
 * @property string $enableCode
 * @property string $code
 * @property int    $limitPerMember
 * @property int    $limitPerConfig
 * @property int    $minSubtotal
 * @property int    $maxSubtotal
 * @property int    $minItemQuantity
 * @property int    $maxItemQuantity
 * @property string $quantityMode
 * @property int    $startDate
 * @property int    $endDate
 * @property int    $startTime
 * @property int    $endTime
 * @property string $configRestrictions
 * @property string $configCondition
 * @property string $configs
 * @property string $memberRestrictions
 * @property string $memberCondition
 * @property string $groups
 * @property string $members
 * @property string $productRestrictions
 * @property string $productCondition
 * @property string $producttypes
 * @property string $pages
 * @property string $products
 * @property string $variants
 * @property string $attributeName
 * @property string $attributeCondition
 * @property string $attributeValue
 * @property bool   $enabled
 */
class Rule extends \Model
{
    /**
     * Cached $this->discount['unit'] value.
     *
     * @var string
     */
    private $discountUnit = null;

    /**
     * Cached $this->discount['value'] value.
     *
     * @var float
     */
    private $discountValue = null;

    /**
     * Name of the current table.
     *
     * @var string
     */
    protected static $strTable = 'tl_iso_rule';

    /**
     * Get label for rule.
     *
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get(($this->label ?: $this->name));
    }

    /**
     * Parse the serialized discount field and save prepared results to $this->discountUnit and $this->discountValue.
     *
     * @return void
     */
    private function loadDiscount()
    {
        if (null === $this->discountValue) {
            $discount = deserialize($this->discount, true);

            if (1 == count($discount)) {
                // fallback for all non-upgraded fields
                $discount            = array_shift($discount);
                $this->discountUnit  = substr($discount, -1) == '%' ? '%' : '';
                $this->discountValue = (float)($this->discountUnit ? substr($discount, 0, -1) : $discount);
            } else {
                $this->discountUnit  = $discount['unit'];
                $this->discountValue = (float)$discount['value'];
            }
        }
    }

    /**
     * Return the discount value.
     *
     * @return float
     */
    public function getDiscountValue()
    {
        $this->loadDiscount();
        return $this->discountValue;
    }

    /**
     * Return the discount unit.
     *
     * @return string Return "%", "unit" or "".
     */
    public function getDiscountUnit()
    {
        $this->loadDiscount();
        return $this->discountUnit;
    }

    /**
     * Return discount label.
     *
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getDiscountLabel()
    {
        $this->loadDiscount();
        $unit = $GLOBALS['TL_LANG']['tl_iso_rule']['discountUnits'][$this->discountUnit];

        if ($unit) {
            list($whole, $decimal) = sscanf($this->discountValue, '%d.%d');
            return \Controller::getFormattedNumber($this->discountValue, strlen($decimal)) . ' ' . $unit;
        }

        return '';
    }

    /**
     * Return true if the rule has a percentage based.
     *
     * @return bool
     */
    public function isPercentage()
    {
        return '%' === $this->getDiscountUnit();
    }

    /**
     * Return true if the rule is item unit based.
     *
     * @return bool
     */
    public function isItemUnit()
    {
        return 'unit' === $this->getDiscountUnit();
    }

    /**
     * Return true if the rule has a fixed value.
     *
     * @return bool
     */
    public function isFixedValue()
    {
        return '' === $this->getDiscountUnit();
    }

    /**
     * Return percentage unit (if applicable).
     *
     * @return float
     * @deprecated Use Rule::getDiscountValue() instead.
     */
    public function getPercentage()
    {
        return $this->discountValue;
    }

    /**
     * Return percentage label if price is percentage.
     *
     * @return string
     * @deprecated Use Rule::getDiscountLabel() instead.
     */
    public function getPercentageLabel()
    {
        return $this->getDiscountLabel();
    }

    /**
     * Calculate the rule discount on the given price.
     *
     * @param float $fltPrice The item price to calculate the discount on.
     * @param int   $quantity The item quantity.
     *
     * @return float
     */
    public function calculateDiscount(
        $fltPrice,
        $quantity = 1
    ) {
        if ($this->isPercentage()) {
            $fltDiscount = $this->getDiscountValue();
            $fltDiscount = round($fltPrice / 100 * $fltDiscount, 10);
            $fltDiscount = $fltDiscount > 0
                ? (floor($fltDiscount * 100) / 100)
                : (ceil($fltDiscount * 100) / 100);
        } elseif ($this->isItemUnit()) {
            $fltItemPrice = $fltPrice / $quantity;
            $fltDiscount  = $fltItemPrice * $this->getDiscountValue();
        } elseif ($this->isFixedValue()) {
            $fltDiscount = $this->getDiscountValue();
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Unsupported discount type: "%s"',
                    $this->getDiscountUnit()
                )
            );
        }

        return $fltDiscount;
    }

    /**
     * Apply the rule discount on the given price.
     *
     * @param float $fltPrice The item price to calculate the discount on.
     * @param int   $quantity The item quantity.
     *
     * @return float
     */
    public function applyDiscount(
        $fltPrice,
        $quantity = 1
    ) {
        $fltPrice += static::calculateDiscount($fltPrice, $quantity);

        return $fltPrice;
    }

    public static function findByProduct(IsotopeProduct $objProduct, $strField, $fltPrice)
    {
        return static::findByConditions(
            array("type='product'"),
            array(),
            array($objProduct),
            ($strField == 'low_price' ? true : false),
            array($strField => $fltPrice)
        );
    }


    public static function findForCart()
    {
        return static::findByConditions(array("type='cart'", "enableCode=''"));
    }


    public static function findForCartWithCoupons()
    {
        return static::findByConditions(array("type='cart'", "enableCode='1'"));
    }

    public static function findActiveWitoutCoupons()
    {
        return static::findByConditions(array("(type='product' OR (type='cart' AND enableCode=''))"));
    }


    public static function findOneByCouponCode($strCode, $arrCollectionItems)
    {
        $objRules = static::findByConditions(
            array("type='cart'", "enableCode='1'", 'code=?'),
            array($strCode),
            $arrCollectionItems
        );

        if (null !== $objRules) {
            return $objRules->current();
        }

        return null;
    }


    /**
     * Fetch rules
     */
    protected static function findByConditions(
        $whereParts,
        $parameters = array(),
        $arrProducts = null,
        $blnIncludeVariants = false,
        $arrAttributeData = array()
    ) {
        // Only enabled rules
        $whereParts[] = "enabled='1'";

        // Date & Time restrictions
        $date = date('Y-m-d');
        $time = date('H:i:s');

        $whereParts[] = "(startDate='' OR startDate <= UNIX_TIMESTAMP('$date'))";
        $whereParts[] = "(endDate='' OR endDate >= UNIX_TIMESTAMP('$date'))";
        $whereParts[] = "(startTime='' OR startTime <= UNIX_TIMESTAMP('1970-01-01 $time'))";
        $whereParts[] = "(endTime='' OR endTime >= UNIX_TIMESTAMP('1970-01-01 $time'))";

        $configId = (int)Isotope::getConfig()->id;
        $userId   = (int)\FrontendUser::getInstance()->id;
        $cartId   = (int)Isotope::getCart()->id;

        // Limits
        $whereParts[] = <<<SQL
(
    limitPerConfig=0
    OR limitPerConfig > (
        SELECT COUNT(*)
        FROM tl_iso_rule_usage
        WHERE pid=r.id
        AND config_id={$configId}
        AND order_id NOT IN (
            SELECT id
            FROM tl_iso_product_collection
            WHERE type='order'
            AND source_collection_id={$configId}
        )
    )
)
SQL;

        if (Isotope::getCart()->member > 0) {
            $whereParts[] = <<<SQL
(
    limitPerMember=0
    OR limitPerMember > (
        SELECT COUNT(*)
        FROM tl_iso_rule_usage
        WHERE pid=r.id
        AND member_id={$userId}
        AND order_id NOT IN (
            SELECT id
            FROM tl_iso_product_collection
            WHERE type='order'
            AND source_collection_id={$cartId}
        )
    )
)
SQL;
        }

        // Store config restrictions
        $whereParts[] = <<<SQL
(
    configRestrictions=''
    OR (
        configRestrictions='1'
        AND configCondition='1'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='configs'
            AND object_id={$configId}
        ) > 0
    )
    OR (
        configRestrictions='1'
        AND configCondition='0'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='configs'
            AND object_id={$configId}
        ) = 0
    )
)
SQL;

        // Member restrictions
        if (Isotope::getCart()->member > 0) {

            $groups = array_map('intval', deserialize(\FrontendUser::getInstance()->groups, true));
            $groups = implode(',', $groups);

            $procedure = <<<SQL
(
    memberRestrictions='none'
    OR (memberRestrictions='guests' AND memberCondition='0')
    OR (
        memberRestrictions='members'
        AND memberCondition='1'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='members'
            AND object_id={$userId}
        ) > 0
    )
    OR (
        memberRestrictions='members'
        AND memberCondition='0'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='members'
            AND object_id={$userId}
        ) = 0
    )

SQL;

            if (!empty($groups)) {
                $procedure .= <<<SQL
    OR (
        memberRestrictions='groups'
        AND memberCondition='1'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='groups'
            AND object_id IN ({$groups})
        ) > 0
    )
    OR (
        memberRestrictions='groups'
        AND memberCondition='0'
        AND (
            SELECT COUNT(*)
            FROM tl_iso_rule_restriction
            WHERE pid=r.id
            AND type='groups'
            AND object_id IN ({$groups})
        ) = 0
    )
SQL;
            }

            $whereParts[] = $procedure;
        } else {
            $whereParts[] = <<<SQL
(
    memberRestrictions='none'
    OR (
        memberRestrictions='guests'
        AND memberCondition='1'
    )
)
SQL;
        }

        // Product restrictions
        if (!is_array($arrProducts)) {
            $arrProducts = Isotope::getCart()->getItems();
        }

        if (!empty($arrProducts)) {
            $arrProductIds = array(0);
            $arrVariantIds = array(0);
            $arrAttributes = array(0);
            $arrTypes      = array(0);

            // Prepare product attribute condition
            $objAttributeRules = \Database::getInstance()
                ->execute(
                    sprintf(
                        'SELECT * FROM %s
                         WHERE enabled = "1"
                         AND productRestrictions = "attribute"
                         AND attributeName != ""
                         GROUP BY attributeName, attributeCondition',
                        static::$strTable
                    )
                );
            while ($objAttributeRules->next()) {
                $arrAttributes[] = array
                (
                    'attribute' => $objAttributeRules->attributeName,
                    'condition' => $objAttributeRules->attributeCondition,
                    'values'    => array(),
                );
            }

            foreach ($arrProducts as $objProduct) {
                if ($objProduct instanceof ProductCollectionItem) {
                    if (!$objProduct->hasProduct()) {
                        continue;
                    }

                    $objProduct = $objProduct->getProduct();
                }

                $arrProductIds[] = (int)$objProduct->getProductId();
                $arrVariantIds[] = (int)$objProduct->{$objProduct->getPk()};
                $arrTypes[]      = (int)$objProduct->type;

                if ($objProduct->isVariant()) {
                    $arrVariantIds[] = (int)$objProduct->pid;
                }

                if ($blnIncludeVariants && $objProduct->hasVariants()) {
                    $arrVariantIds = array_merge($arrVariantIds, $objProduct->getVariantIds());
                }

                $arrOptions = $objProduct->getOptions();
                foreach ($arrAttributes as $k => $restriction) {
                    $varValue = null;

                    if (isset($arrAttributeData[$restriction['attribute']])) {
                        $varValue = $arrAttributeData[$restriction['attribute']];
                    } elseif (isset($arrOptions[$restriction['attribute']])) {
                        $varValue = $arrOptions[$restriction['attribute']];
                    } else {
                        $varValue = $objProduct->{$restriction['attribute']};
                    }

                    if (!is_null($varValue)) {
                        $arrAttributes[$k]['values'][] = is_array($varValue) ? serialize($varValue) : $varValue;
                    }
                }
            }

            $arrProductIds = array_unique($arrProductIds);
            $arrVariantIds = array_unique($arrVariantIds);

            $types                    = implode(',', $arrTypes);
            $productIds               = implode(',', $arrProductIds);
            $variantIds               = implode(',', $arrVariantIds);
            $productCategoryTableName = \Isotope\Model\ProductCategory::getTable();

            $restrictionParts   = array("productRestrictions='none'");
            $restrictionParts[] = <<<SQL
(
    productRestrictions='producttypes'
    AND productCondition='1'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='producttypes'
        AND object_id IN ({$types})
    ) > 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='producttypes'
    AND productCondition='0'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='producttypes'
        AND object_id IN ({$types})
    ) = 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='products'
    AND productCondition='1'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='products'
        AND object_id IN ({$productIds})
    ) > 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='products'
    AND productCondition='0'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='products'
        AND object_id IN ({$productIds})
    ) = 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='variants'
    AND productCondition='1'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='variants'
        AND object_id IN ({$variantIds})
    ) > 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='variants'
    AND productCondition='0'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='variants'
        AND object_id IN ({$variantIds})
    ) = 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='pages'
    AND productCondition='1'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='pages'
        AND object_id IN (
            SELECT page_id
            FROM {$productCategoryTableName}
            WHERE pid IN ({$productIds})
        )
    ) > 0
)
SQL;
            $restrictionParts[] = <<<SQL
(
    productRestrictions='pages'
    AND productCondition='0'
    AND (
        SELECT COUNT(*)
        FROM tl_iso_rule_restriction
        WHERE pid=r.id
        AND type='pages'
        AND object_id IN (
            SELECT page_id
            FROM {$productCategoryTableName}
            WHERE pid IN ({$productIds})
        )
    ) = 0
)
SQL;

            foreach ($arrAttributes as $restriction) {
                if (empty($restriction['values'])) {
                    continue;
                }

                $attributeName      = $restriction['attribute'];
                $attributeCondition = $restriction['condition'];

                $strRestriction = <<<SQL
(
    productRestrictions='attribute'
    AND attributeName='{$attributeName}'
    AND attributeCondition='{$attributeCondition}'
    AND

SQL;

                switch ($restriction['condition']) {
                    case 'eq':
                    case 'neq':
                        switch ($restriction['condition']) {
                            case 'eq':
                                $condition = 'IN';
                                break;
                            case 'neq':
                                $condition = 'NOT IN';
                                break;
                            default:
                                throw new \RuntimeException(
                                    sprintf(
                                        'Invalid condition "%s"',
                                        $restriction['condition']
                                    )
                                );
                        }

                        $set = implode(',', array_fill(0, count($restriction['values']), '?'));

                        $strRestriction .= sprintf(
                            'attributeValue %s (%s)',
                            $condition,
                            $set
                        );
                        break;

                    case 'lt':
                    case 'gt':
                    case 'elt':
                    case 'egt':
                        switch ($restriction['condition']) {
                            case 'lt':
                                $condition = '<';
                                break;
                            case 'gt':
                                $condition = '>';
                                break;
                            case 'elt':
                                $condition = '<=';
                                break;
                            case 'egt':
                                $condition = '>=';
                                break;
                            default:
                                throw new \RuntimeException(
                                    sprintf(
                                        'Invalid condition "%s"',
                                        $restriction['condition']
                                    )
                                );
                        }

                        $part = sprintf(
                            'attributeValue %s ?',
                            $condition
                        );
                        $or   = array_fill(0, count($restriction['values']), $part);

                        $strRestriction .= '(' . implode(' OR ', $or) . ')';
                        break;

                    case 'starts':
                    case 'ends':
                    case 'contains':
                        switch ($restriction['condition']) {
                            case 'starts':
                                $prefix = '';
                                $suffix = '%';
                                break;
                            case 'ends':
                                $prefix = '%';
                                $suffix = '';
                                break;
                            case 'contains':
                                $prefix = '%';
                                $suffix = '%';
                                break;
                            default:
                                throw new \RuntimeException(
                                    sprintf(
                                        'Invalid condition "%s"',
                                        $restriction['condition']
                                    )
                                );
                        }

                        $part = 'attributeValue LIKE ?';
                        $or   = array_fill(0, count($restriction['values']), $part);

                        $strRestriction .= '(' . implode(' OR ', $or) . ')';

                        foreach ($restriction['values'] as $key => $value) {
                            $restriction['values'][$key] = $prefix . $value . $suffix;
                        }
                        break;

                    default:
                        throw new \InvalidArgumentException(
                            'Unknown rule condition "' . $restriction['condition'] . '"'
                        );
                }

                $parameters = array_merge($parameters, $restriction['values']);

                $restrictionParts[] = $strRestriction . ')';
            }

            $whereParts[] = '(' . implode(' OR ', $restrictionParts) . ')';
        }

        $resultSet = \Database::getInstance()
            ->prepare(
                sprintf(
                    "SELECT * FROM %s r WHERE %s",
                    static::$strTable,
                    implode(' AND ', $whereParts)
                )
            )
            ->execute($parameters);

        if ($resultSet->numRows) {
            return \Model\Collection::createFromDbResult($resultSet, static::$strTable);
        }

        return null;
    }
}
