<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\DataCollector;

use Contao\CoreBundle\DataContainer\VirtualFieldsHandler;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autoconfigure(tags: ['isotope.product_data_collector'])]
class CoreDataCollector implements DataCollectorInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AttributeRegistry $attributeRegistry,
        #[Autowire(service: 'contao.data_container.virtual_fields_handler')] private readonly VirtualFieldsHandler $virtualFieldsHandler,
    ) {
    }

    public function collect(ProductDtoCollection $collection, StoreContext $context): void
    {
        $ids = array_map(
            static fn (string $identifier) => substr($identifier, strrpos($identifier, '.') + 1),
            array_filter(
                $collection->keys(),
                static fn (string $identifier) => str_starts_with($identifier, 'tl_iso_product.'),
            ),
        );

        if ([] === $ids) {
            return;
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'p.id AS __id',
                'p.*',
                't.id AS t__id',
                'pp.id AS pp__id',
                'pt.id AS pt__id',
                'type.class AS __class',
                'ptype.class AS __pclass',
            )
            ->from('tl_iso_product', 'p')
            ->leftJoin('p', 'tl_iso_producttype', 'type', 'p.type=type.id')
            ->leftJoin('p', 'tl_iso_product', 't', 't.pid=p.id AND t.language=:locale')
            ->leftJoin('p', 'tl_iso_product', 'pp', "p.pid=pp.id AND pp.language=''")
            ->leftJoin('pp', 'tl_iso_producttype', 'ptype', 'pp.type=ptype.id')
            ->leftJoin('pp', 'tl_iso_product', 'pt', 'pt.pid=pp.id AND pt.language=:locale')
            ->where('p.id IN (:ids)')
            ->orWhere("(p.pid IN (:ids) AND p.language='')")
            ->orWhere("(p.pid IN (SELECT pid FROM tl_iso_product WHERE id IN (:ids) AND pid>0) AND p.language='')")
            ->setParameter('ids', $ids, ArrayParameterType::INTEGER)
            ->setParameter('locale', $context->getLocale())
        ;

        foreach ($this->attributeRegistry->getTranslatableFields() as $field) {
            $qb->addSelect(\sprintf('t.%s AS t__%s', $field, $field));
            $qb->addSelect(\sprintf('pt.%s AS pt__%s', $field, $field));
        }

        $columns = $this->connection->createSchemaManager()->introspectTableColumnsByUnquotedName('tl_iso_product');

        foreach ($columns as $column) {
            $qb->addSelect(\sprintf('pp.%s AS pp__%s', $column->getName(), $column->getName()));
        }

        $data = $qb->fetchAllAssociativeIndexed();

        foreach ($ids as $id) {
            if (!isset($data[$id])) {
                continue;
            }

            $row = $this->prepare($data[$id]);
            $row['__variants'] = $this->getVariants($data, (int) ($row['pid'] ?: $row['id']));

            $identifier = \sprintf('tl_iso_product.%s', $row['id']);

            $collection->get($identifier)->setType($row['__class'] ?? $row['__pclass'])->exchangeArray($row);
        }
    }

    private function prepare(array $data): array
    {
        $data['__translation'] = $this->extract($data, 't__');
        $data['__parent'] = $this->extract($data, 'pp__');
        $parentTranslation = $this->extract($data, 'pt__');

        if ($data['__parent']) {
            $data['__parent']['__translation'] = $parentTranslation;
        }

        return $this->virtualFieldsHandler->expandFields($data, 'tl_iso_product');
    }

    private function extract(array &$data, string $prefix): array|null
    {
        $extract = [];

        foreach ($data as $key => $v) {
            if (str_starts_with($key, $prefix)) {
                $extract[substr($key, \strlen($prefix))] = $v;
                unset($data[$key]);
            }
        }

        if (!$extract['id']) {
            return null;
        }

        return $this->virtualFieldsHandler->expandFields($extract, 'tl_iso_product');
    }

    private function getVariants(array $data, int $pid): array
    {
        $variants = array_filter($data, static fn (array $row) => (int) $row['pid'] === $pid);

        foreach ($variants as &$variant) {
            $variant = $this->prepare($variant);
        }

        return $variants;
    }
}
