<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum OptionsSource: string implements TranslatableLabelInterface
{
    case Name = 'name';
    case Table = 'table';
    case ForeignKey = 'foreignKey';
    case Attribute = 'attribute';
    case Product = 'product';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_attribute.optionsSource.'.$this->value, [], 'contao_tl_iso_attribute');
    }
}
