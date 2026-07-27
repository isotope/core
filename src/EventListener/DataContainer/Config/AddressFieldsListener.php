<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Config;

use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Model\AddressModel;

class AddressFieldsListener extends Backend
{
    #[AsCallback('tl_iso_config', 'fields.address_fields.attributes')]
    public function addNameCallback(array $attributes, DataContainer $dc): array
    {
        $attributes['fields']['name']['input_field_callback'] = $this->getNameClosure($dc->field, $this->load($attributes['value'] ?? null));

        return $attributes;
    }

    /**
     * @param string $value
     *
     * @return array<array<mixed>>
     */
    #[AsCallback('tl_iso_config', 'fields.address_fields.load')]
    public function load($value): array
    {
        Controller::loadDataContainer(AddressModel::getTable());

        $dca = &$GLOBALS['TL_DCA'][AddressModel::getTable()]['fields'];
        $fields = [];
        $values = StringUtil::deserialize($value);

        if (!\is_array($values)) {
            $values = [];
        }

        foreach ($values as $field) {
            $name = $field['name'];

            if ('' === $name || !isset($dca[$name]) || !$dca[$name]['eval']['feEditable']) {
                continue;
            }

            $fields[$field['name']] = $field;
        }

        foreach (array_diff_key($dca, $fields) as $name => $field) {
            if (!($dca[$name]['eval']['feEditable'] ?? false)) {
                continue;
            }

            $mandatory = $field['eval']['mandatory'] ?? null;

            $fields[$name] = [
                'name' => $name,
                'billing' => true === $mandatory ? 'mandatory' : (false === $mandatory ? 'enabled' : 'disabled'),
                'shipping' => true === $mandatory ? 'mandatory' : (false === $mandatory ? 'enabled' : 'disabled'),
            ];
        }

        return array_values($fields);
    }

    /**
     * @param string $value
     */
    #[AsCallback('tl_iso_config', 'fields.address_fields.save')]
    public function save($value, DataContainer $dc): string
    {
        Controller::loadDataContainer(AddressModel::getTable());

        $fields = StringUtil::deserialize($value);
        $input = Input::post($dc->field);

        if (empty($fields) || !\is_array($fields)) {
            return $value;
        }

        $value = [];

        foreach (array_values($fields) as $pos => $config) {
            $name = $config['name'] ?? $input[$pos]['name'] ?? null;

            $config['name'] = $name;
            $config['position'] = $pos;
            $value[$name] = $config;
        }

        return serialize($value);
    }

    /**
     * For each call, return the name of the next address field in the wizard (for input_field_callback).
     */
    private function getNameClosure(string $name, array|null $value): \Closure
    {
        System::loadLanguageFile(AddressModel::getTable());

        $row = 0;

        return static function () use ($name, &$row, &$value) {
            $field = array_shift($value);
            $fieldName = $field['name'];

            return \sprintf(
                '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div>%s <span class="label-info">[%s]</span></div>',
                $name,
                $row,
                $name,
                $row++,
                $fieldName,
                $GLOBALS['TL_DCA'][AddressModel::getTable()]['fields'][$fieldName]['label'][0] ?: $fieldName,
                $fieldName,
            );
        };
    }
}
