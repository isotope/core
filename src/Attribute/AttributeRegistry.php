<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\VirtualFieldsHandler;
use Contao\Model;
use Contao\Model\Collection;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\Exception\MissingAttributeClassException;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Model\AttributeModel;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\ModelType\Exception\ContainerNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Loads attributes into the tl_iso_product table.
 */
#[Autoconfigure(public: true)]
class AttributeRegistry implements ResetInterface
{
    /**
     * @var array<class-string<AttributeInterface>, ContainerInterface|null>
     */
    private array $subscribedServices = [];

    private array|null $attributes = null;

    /**
     * @param iterable<AttributeFactoryInterface> $factories
     */
    public function __construct(
        private readonly Connection $connection,
        #[Autowire(service: 'contao.data_container.virtual_fields_handler')] private readonly VirtualFieldsHandler $virtualFieldsHandler,
        #[AutowireIterator('isotope.attribute_factory')] private readonly iterable $factories,
    ) {
    }

    public function setContainer(string $attributeClass, ContainerInterface $container): void
    {
        $this->subscribedServices[$attributeClass] = $container;
    }

    public function load(bool $reload = false): void
    {
        if (null !== $this->attributes && !$reload) {
            return;
        }

        Controller::loadDataContainer('tl_iso_product');

        $this->attributes = [];
        $dca = &$GLOBALS['TL_DCA']['tl_iso_product'];

        if (!isset($dca['config']['sql']['keys'])) {
            $dca['config']['sql']['keys'] = [];
        }

        if ($this->connection->createSchemaManager()->tablesExist(['tl_iso_attribute'])) {
            $attributes = $this->connection->fetchAllAssociative(
                "SELECT * FROM tl_iso_attribute WHERE tstamp>0 AND type!='' AND field_name!=''",
            );

            foreach ($attributes as $config) {
                $name = $config['field_name'];
                $config = $this->virtualFieldsHandler->expandFields($config, 'tl_iso_attribute');
                $attribute = $this->create($name, $config, $dca['fields'][$name] ?? []);

                if (null === $attribute) {
                    throw new MissingAttributeClassException($name, $config, $dca['fields'][$name] ?? []);
                }

                if (!isset($dca['fields'][$name])) {
                    $dca['fields'][$name] = [];
                }

                $this->applyContainer($attribute);
                $attribute->updateDcaField($dca['fields'][$name], $dca['config']['sql']['keys']);
                $this->attributes[$name] = $attribute;

                $dca['fields'][$name]['attributes']['legend'] ??= $config['legend'];
            }
        }

        // Create non-database attributes
        foreach (array_diff_key($dca['fields'], $this->attributes) as $name => $field) {
            if ($field['attributes']['systemColumn'] ?? false) {
                continue;
            }

            $attribute = $this->create($name, $field['attributes'] ?? [], $field);

            if (null !== $attribute) {
                $this->applyContainer($attribute);
                $attribute->updateDcaField($dca['fields'][$name], $dca['config']['sql']['keys']);
                $this->attributes[$name] = $attribute;
            }
        }
    }

    /**
     * @param string|Collection<AttributeModel>|AttributeModel|array<AttributeModel>|null $fieldOrModelOrCollection
     *
     * @return ($fieldOrModelOrCollection is array|Collection<AttributeModel> ? array<AttributeInterface> : ($modelOrCollection is null ? null : AttributeInterface|null) )
     */
    public function get(Collection|Model|array|string|null $fieldOrModelOrCollection): AttributeInterface|array|null
    {
        $this->load();

        return match (true) {
            null === $fieldOrModelOrCollection => null,
            $fieldOrModelOrCollection instanceof AttributeModel => $this->get($fieldOrModelOrCollection->field_name),
            $fieldOrModelOrCollection instanceof Collection => array_map($this->get(...), $fieldOrModelOrCollection->getModels()),
            \is_array($fieldOrModelOrCollection) => array_map($this->get(...), $fieldOrModelOrCollection),
            default => $this->attributes[$fieldOrModelOrCollection] ?? null,
        };
    }

    public function getNames(): array
    {
        Controller::loadDataContainer('tl_iso_product');

        return array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']);
    }

    public function getSystemColumns(): array
    {
        Controller::loadDataContainer('tl_iso_product');

        return array_keys(array_filter(
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'],
            static fn (array $config) => (bool) ($config['attributes']['systemColumn'] ?? false),
        ));
    }

    /**
     * @return list<string>
     */
    public function getTranslatableFields(): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $attributes = array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute instanceof TranslatableAttributeInterface && $attribute->isTranslatable(),
        ));

        $translatable = array_keys(array_filter(
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'],
            static fn (array $config) => null !== ($config['eval']['translatableFor'] ?? null),
        ));

        return array_unique(array_merge($translatable, $attributes));
    }

    /**
     * @return list<string>
     */
    public function getFetchTranslationFallbackFields(): array
    {
        $this->load();

        return array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute instanceof TranslatableAttributeInterface && $attribute->fetchTranslationFallback(),
        ));
    }

    /**
     * @return list<string>
     */
    public function getVariantOptionFields(): array
    {
        $this->load();

        return array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute instanceof VariantAttributeInterface && $attribute->isVariantOption(),
        ));
    }

    /**
     * @return list<string>
     */
    public function getCustomerDefinedFields(): array
    {
        $this->load();

        return array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute->isCustomerDefined(),
        ));
    }

    /**
     * @return list<string>
     */
    public function getBackendColumnFields(): array
    {
        $this->load();

        return array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute instanceof AbstractAttribute && $attribute->isBackendColumn(),
        ));
    }

    /**
     * Return array of attributes that have price relevant information.
     *
     * @return list<string>
     */
    public function getPricedFields(): array
    {
        $this->load();

        $attributes = array_keys(array_filter(
            $this->attributes,
            static fn (AttributeInterface $attribute) => $attribute instanceof OptionsAttributeInterface
                && (OptionsSource::Table === $attribute->getOptionsSource() || OptionsSource::Product === $attribute->getOptionsSource())
                && $attribute->canHavePrices(),
        ));

        if ([] === $attributes) {
            return [];
        }

        return $this->connection->fetchFirstColumn(
            <<<SQL
                SELECT a.field_name
                FROM tl_iso_attribute a
                JOIN tl_iso_attribute_option o ON a.id=o.pid
                WHERE
                  a.field_name IN (:attributes)
                  AND a.optionsSource='table'
                  AND o.ptable='tl_iso_attribute'
                  AND o.published='1'
                  AND o.price!=''

                UNION

                SELECT a.field_name
                FROM tl_iso_attribute a
                JOIN tl_iso_attribute_option o ON a.field_name=o.field_name
                WHERE
                  a.field_name IN (:attributes)
                  AND a.optionsSource='product'
                  AND o.ptable='tl_iso_product'
                  AND o.published='1'
                  AND o.price!=''
            SQL,
            ['attributes' => $attributes],
            ['attributes' => ArrayParameterType::STRING],
        );
    }

    public function reset(): void
    {
        $this->attributes = null;
    }

    private function create(string $fieldName, array $config, array $dca): AttributeInterface|null
    {
        foreach ($this->factories as $factory) {
            if ($attribute = $factory->create($fieldName, $config, $dca)) {
                return $attribute;
            }
        }

        return null;
    }

    private function applyContainer(AttributeInterface $attribute): void
    {
        if ($attribute instanceof ContainerAwareInterface) {
            if (!isset($this->subscribedServices[$attribute::class])) {
                throw new ContainerNotFoundException($attribute::class);
            }

            $attribute->setContainer($this->subscribedServices[$attribute::class]);
        }
    }
}
