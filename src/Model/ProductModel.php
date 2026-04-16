<?php

namespace Isotope\CoreBundle\Model;

use Terminal42\DcMultilingualBundle\Model\Multilingual;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $language
 * @property int    $dateAdded
 * @property int    $type
 * @property array  $inherit
 * @property bool   $fallback
 * @property string $alias
 * @property string $sku
 * @property string $name
 * @property string $teaser
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property string $price
 * @property string $prices
 * @property bool   $shipping_exempt
 * @property array  $images
 * @property bool   $protected
 * @property array  $groups
 * @property array  $cssID
 * @property bool   $published
 * @property string $start
 * @property string $stop
 */
class ProductModel extends Multilingual
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product';

    protected static function getPidColumn(): string
    {
        return 'pid';
    }

    /**
     * Find a model by its alias or ID when using multilingal aliases.
     *
     * @param string $alias
     * @param string $aliasColumnName
     * @param array  $options
     *
     * @return self
     */
    public static function findByMultilingualAliasOrId($alias, $aliasColumnName = 'alias', $options = [])
    {
        $isAlias = !preg_match('/^[1-9]\d*$/', $alias);

        // Try to load from the registry
        if (!$isAlias && empty($options)) {
            return static::findById($alias, $options);
        }

        $table = static::getTable();
        $options = array_merge(
            [
                'limit' => 1,
                'column' => $isAlias ? ["((translation.$aliasColumnName IS NULL AND CAST($table.$aliasColumnName AS BINARY) = ?) OR CAST(translation.$aliasColumnName AS BINARY) = ?)"] : ["$table.id = ?"],
                'value' => $isAlias ? [$alias, $alias] : [$alias],
                'return' => 'Model',
            ],
            $options,
        );

        return static::find($options);
    }
}
