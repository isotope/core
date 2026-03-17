<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\OrderStatus;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Exception\RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AliasListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param DataContainer|mixed|null $dc
     */
    #[AsCallback('tl_iso_orderstatus', 'fields.alias.attributes')]
    public function disableAlias(array $attributes, mixed $dc): array
    {
        if (
            !$dc instanceof DataContainer
            || !$dc->id
            || '' !== (string) $dc->value
        ) {
            $attributes['disabled'] = true;
            $attributes['mandatory'] = false;
        }

        return $attributes;
    }

    #[AsCallback('tl_iso_orderstatus', 'fields.alias.save')]
    public function checkDuplicate(string $value, DataContainer $dc): string
    {
        $currentRecord = $dc->getCurrentRecord();

        if (!$currentRecord) {
            return $value;
        }

        $duplicate = $this->connection->fetchOne(
            'SELECT TRUE FROM tl_iso_orderstatus WHERE type=? AND alias=? AND id!=?',
            [$currentRecord['type'], $value, $dc->id],
        );

        if (false !== $duplicate) {
            throw new RuntimeException($this->translator->trans('ERR.aliasExists', [$value], 'contao_default'));
        }

        return $value;
    }
}
