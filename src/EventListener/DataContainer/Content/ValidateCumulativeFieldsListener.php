<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;

#[AsCallback('tl_content', 'fields.iso_cumulativeFields.save')]
class ValidateCumulativeFieldsListener
{
    public function __invoke(mixed $value): string
    {
        $rows = StringUtil::deserialize($value, true);
        $attributes = [];

        foreach ($rows as $row) {
            if (empty($row['attribute'])) {
                throw new \InvalidArgumentException(\sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $GLOBALS['TL_LANG']['tl_content']['iso_cumulativeFields']['attribute'][0]));
            }

            if (\in_array($row['attribute'], $attributes, true)) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['cumulativeDuplicateAttribute']);
            }

            $attributes[] = $row['attribute'];
        }

        return serialize($rows);
    }
}
