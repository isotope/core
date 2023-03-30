<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Database;
use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Translation;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $discount
 * @property int    $tax_class
 * @property array  $groupRules
 * @property string $groupCondition
 * @property string $applyTo
 * @property string $rounding
 * @property bool   $enableCode
 * @property string $code
 * @property bool   $singleCode
 * @property int    $limitPerMember
 * @property int    $limitPerConfig
 * @property int    $minSubtotal
 * @property int    $maxSubtotal
 * @property string $minWeight
 * @property string $maxWeight
 * @property int    $minItemQuantity
 * @property int    $maxItemQuantity
 * @property string $quantityMode
 * @property int    $startDate
 * @property int    $endDate
 * @property int    $startTime
 * @property int    $endTime
 * @property string $configRestrictions
 * @property bool   $configCondition
 * @property string $memberRestrictions
 * @property bool   $memberCondition
 * @property string $productRestrictions
 * @property bool   $productCondition
 * @property string $attributeName
 * @property string $attributeCondition
 * @property string $attributeValue
 * @property bool   $enabled
 * @property bool   $groupOnly
 */
class Rule extends Model
{
    const ROUND_NORMAL = 'normal';
    const ROUND_UP = 'up';
    const ROUND_DOWN = 'down';

    const GROUP_FIRST = 'first';
    const GROUP_ALL = 'all';

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_rule';

    /**
     * Get label for rule
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get(($this->label ? : $this->name));
    }

    /**
     * Return true if the rule has a percentage (not fixed) amount
     * @return bool
     */
    public function isPercentage()
    {
        return '%' === substr($this->discount, -1);
    }

    /**
     * Return percentage amount (if applicable)
     * @return float
     * @throws UnexpectedValueException
     */
    public function getPercentage()
    {
        if (!$this->isPercentage()) {
            throw new \UnexpectedValueException('Rule does not have a percentage amount.');
        }

        return (float) substr($this->discount, 0, -1);
    }

    /**
     * Return percentage label if price is percentage
     * @return  string
     */
    public function getPercentageLabel()
    {
        return $this->isPercentage() ? $this->discount : '';
    }


    public static function findByProduct(IsotopeProduct $objProduct, $strField, $fltPrice)
    {
        return static::findByConditions(array("type='product'"), array(), array($objProduct), 'low_price' === $strField, array($strField => $fltPrice));
    }

    public static function findForCart($intId = null)
    {
        $arrProcedures = array("(type='cart' OR type='cart_group')", "enableCode=''");

        if (null === $intId) {
            $arrProcedures[] = "groupOnly=''";
        } else {
            $arrProcedures[] = 'id='.(int)$intId;
        }

        return static::findByConditions($arrProcedures);
    }

    public static function findForCartWithCoupons()
    {
        return static::findByConditions(array("(type='cart' OR type='cart_group')", "enableCode='1'"));
    }

    /**
     * @deprecated Deprecated since 2.1.9, to be removed in 3.0
     * @see Rule::findActiveWithoutCoupons
     */
    public static function findActiveWitoutCoupons()
    {
        return static::findActiveWithoutCoupons();
    }

    public static function findActiveWithoutCoupons()
    {
        return static::findByConditions(array("(type='product' OR ((type='cart' OR type='cart_group') AND enableCode=''))"));
    }

    /**
     * @param string $strCode
     * @param array $arrCollectionItems
     *
     * @return Rule|null
     */
    public static function findOneByCouponCode($strCode, $arrCollectionItems)
    {
        $objRules = static::findByConditions(array("(type='cart' OR type='cart_group')", "enableCode='1'", 'code=?', "groupOnly=''"), array($strCode), $arrCollectionItems);

        if (null !== $objRules) {
            return $objRules->current();
        }

        return null;
    }


    /**
     * Fetch rules
     */
    protected static function findByConditions($arrProcedures, $arrValues = array(), $arrProducts = null, $blnIncludeVariants = false, $arrAttributeData = array())
    {
        // Only enabled rules
        $arrProcedures[] = "enabled='1'";

        // Date & Time restrictions
        $date = date('Y-m-d H:i:s');
        $time = date('H:i:s');
        $arrProcedures[] = "(startDate='' OR startDate <= UNIX_TIMESTAMP('$date'))";
        $arrProcedures[] = "(endDate='' OR endDate >= UNIX_TIMESTAMP('$date'))";
        $arrProcedures[] = "(startTime='' OR startTime <= UNIX_TIMESTAMP('1970-01-01 $time'))";
        $arrProcedures[] = "(endTime='' OR endTime >= UNIX_TIMESTAMP('1970-01-01 $time'))";


        // Limits
        $arrProcedures[] = "(limitPerConfig=0 OR limitPerConfig>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND config_id=" . (int) Isotope::getConfig()->id . " AND order_id NOT IN (SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=" . (int) Isotope::getCart()->id . ")))";

        if (Isotope::getCart()->member > 0) {
            $arrProcedures[] = "(limitPerMember=0 OR limitPerMember>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND member_id=" . (int) Isotope::getCart()->member . " AND order_id NOT IN (SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=" . (int) Isotope::getCart()->id . ")))";
        }

        // Store config restrictions
        $arrProcedures[] = "(configRestrictions=''
                            OR (configRestrictions='1' AND configCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='configs' AND object_id=" . (int) Isotope::getConfig()->id . ")>0)
                            OR (configRestrictions='1' AND configCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='configs' AND object_id=" . (int) Isotope::getConfig()->id . ")=0))";


        // Member restrictions
        if (Isotope::getCart()->member > 0) {

            $objMember = MemberModel::findByPk(Isotope::getCart()->member);
            $arrGroups = (null === $objMember) ? array() : array_map('intval', StringUtil::deserialize($objMember->groups, true));

            $arrProcedures[] = "(memberRestrictions='none'
                                OR (memberRestrictions='guests' AND memberCondition='0')
                                OR (memberRestrictions='members' AND memberCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='members' AND object_id=" . (int) Isotope::getCart()->member . ")>0)
                                OR (memberRestrictions='members' AND memberCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='members' AND object_id=" . (int) Isotope::getCart()->member . ")=0)
                                " . (!empty($arrGroups) ? "
                                OR (memberRestrictions='groups' AND memberCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $arrGroups) . "))>0)
                                OR (memberRestrictions='groups' AND memberCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $arrGroups) . "))=0)" : '') . ")";
        } else {
            $arrProcedures[] = "(memberRestrictions='none' OR (memberRestrictions='guests' AND memberCondition='1'))";
        }


        // Product restrictions
        if (!\is_array($arrProducts)) {
            $arrProducts = Isotope::getCart()->getItems();
        }

        if (!empty($arrProducts)) {
            $arrProductIds = [0];
            $arrVariantIds = [0];
            $arrAttributes = [];
            $arrTypes = [0];

            // Prepare product attribute condition
            $objAttributeRules = Database::getInstance()->execute("SELECT attributeName, attributeCondition FROM " . static::$strTable . " WHERE enabled='1' AND productRestrictions='attribute' AND attributeName!='' GROUP BY attributeName, attributeCondition");
            while ($objAttributeRules->next()) {
                $arrAttributes[] = array
                (
                    'attribute' => $objAttributeRules->attributeName,
                    'condition' => $objAttributeRules->attributeCondition,
                    'values'    => [],
                );
            }

            foreach ($arrProducts as $objProduct) {
                if ($objProduct instanceof ProductCollectionItem) {
                    if (!$objProduct->hasProduct()) {
                        continue;
                    }

                    $objProduct = $objProduct->getProduct();
                }

                $arrProductIds[] = (int) $objProduct->getProductId();
                $arrVariantIds[] = (int) $objProduct->{$objProduct->getPk()};
                $arrTypes[] = (int) $objProduct->type;

                if ($objProduct->isVariant()) {
                    $arrVariantIds[] = (int) $objProduct->pid;
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

                    if (!\is_null($varValue)) {
                        $arrAttributes[$k]['values'][] = \is_array($varValue) ? serialize($varValue) : $varValue;
                    }
                }
            }

            $arrProductIds = array_unique($arrProductIds);
            $arrVariantIds = array_unique($arrVariantIds);

            $arrRestrictions = array("productRestrictions='none'");
            $arrRestrictions[] = "(productRestrictions='producttypes' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='producttypes' AND object_id IN (" . implode(',', $arrTypes) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='producttypes' AND productCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='producttypes' AND NOT object_id IN (" . implode(',', $arrTypes) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='products' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='products' AND object_id IN (" . implode(',', $arrProductIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='products' AND productCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='products' AND object_id NOT IN (" . implode(',', $arrProductIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='variants' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='variants' AND object_id IN (" . implode(',', $arrVariantIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='variants' AND productCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='variants' AND object_id NOT IN (" . implode(',', $arrVariantIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='pages' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM " . ProductCategory::getTable() . " WHERE pid IN (" . implode(',', $arrProductIds) . ")))>0)";
            $arrRestrictions[] = "(productRestrictions='pages' AND productCondition='0' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='pages' AND object_id NOT IN (SELECT page_id FROM " . ProductCategory::getTable() . " WHERE pid IN (" . implode(',', $arrProductIds) . ")))>0)";

            foreach ($arrAttributes as $restriction) {
                if (empty($restriction['values'])) {
                    continue;
                }

                $strRestriction = "(productRestrictions='attribute' AND attributeName='" . $restriction['attribute'] . "' AND attributeCondition='" . $restriction['condition'] . "' AND ";

                switch ($restriction['condition']) {
                    case 'eq':
                    case 'neq':
                        $strRestriction .= sprintf(
                            "attributeValue %s IN (%s)",
                            ('neq' === $restriction['condition'] ? 'NOT' : ''),
                            implode(', ', array_fill(0, \count($restriction['values']), '?'))
                        );
                        $arrValues = array_merge($arrValues, $restriction['values']);
                        break;

                    case 'lt':
                    case 'gt':
                    case 'elt':
                    case 'egt':
                        $arrOR = array();
                        foreach ($restriction['values'] as $value) {
                            $arrOR[] = sprintf(
                                'attributeValue %s%s ?',
                                (('lt' === $restriction['condition'] || 'elt' === $restriction['condition']) ? '>' : '<'),
                                (('elt' === $restriction['condition'] || 'egt' === $restriction['condition']) ? '=' : '')
                            );
                            $arrValues[] = $value;
                        }
                        $strRestriction .= '(' . implode(' OR ', $arrOR) . ')';
                        break;

                    case 'starts':
                    case 'ends':
                    case 'contains':
                        $arrOR = array();
                        foreach ($restriction['values'] as $value) {
                            $arrOR[] = sprintf(
                                "? LIKE CONCAT(%sattributeValue%s)",
                                (('ends' === $restriction['condition'] || 'contains' === $restriction['condition']) ? "'%', " : ''),
                                (('starts' === $restriction['condition'] || 'contains' === $restriction['condition']) ? ", '%'" : '')
                            );
                            $arrValues[] = $value;
                        }
                        $strRestriction .= '(' . implode(' OR ', $arrOR) . ')';
                        break;

                    default:
                        throw new \InvalidArgumentException(
                            sprintf('Unknown rule condition "%s"', $restriction['condition'])
                        );
                }

                $arrRestrictions[] = $strRestriction . ')';
            }

            $arrProcedures[] = '(' . implode(' OR ', $arrRestrictions) . ')';
        }

        $objResult = Database::getInstance()
            ->prepare('SELECT * FROM tl_iso_rule r WHERE ' . implode(' AND ', $arrProcedures))
            ->execute($arrValues)
        ;

        if ($objResult->numRows) {
            return Collection::createFromDbResult($objResult, static::$strTable);
        }

        return null;
    }
}
