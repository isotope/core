<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Picker;

use Contao\CoreBundle\DependencyInjection\Attribute\AsPickerProvider;
use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Knp\Menu\FactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsPickerProvider]
class ProductPickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface
{
    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        TranslatorInterface $translator,
        private readonly Security $security,
    ) {
        parent::__construct($menuFactory, $router, $translator);
    }

    public function getName(): string
    {
        return 'productPicker';
    }

    public function supportsContext(string $context): bool
    {
        return \in_array($context, ['dc.tl_iso_product', 'link'], true) && $this->security->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_PRODUCTS);
    }

    public function supportsValue(PickerConfig $config): bool
    {
        if ('dc.tl_iso_product' === $config->getContext()) {
            return is_numeric($config->getValue());
        }

        return $this->isMatchingInsertTag($config);
    }

    public function getDcaTable(PickerConfig|null $config = null): string
    {
        return 'tl_iso_product';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $value = $config->getValue();
        $attributes = ['fieldType' => 'radio'];

        if ('dc.tl_iso_product' === $config->getContext()) {
            if ($fieldType = $config->getExtra('fieldType')) {
                $attributes['fieldType'] = $fieldType;
            }

            if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($value) {
                $attributes['value'] = array_map(\intval(...), explode(',', $value));
            }

            return $attributes;
        }

        if ($value && $this->isMatchingInsertTag($config)) {
            $attributes['value'] = $this->getInsertTagValue($config);

            if ($flags = $this->getInsertTagFlags($config)) {
                $attributes['flags'] = $flags;
            }
        }

        return $attributes;
    }

    public function convertDcaValue(PickerConfig $config, mixed $value): int|string
    {
        if ('dc.tl_iso_product' === $config->getContext()) {
            return (int) $value;
        }

        return \sprintf($this->getInsertTag($config), $value);
    }

    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        return ['do' => 'iso_products'];
    }

    protected function getDefaultInsertTag(): string
    {
        return '{{product_url::%s}}';
    }
}
