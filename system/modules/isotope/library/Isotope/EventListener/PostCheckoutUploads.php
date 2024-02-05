<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\EventListener;

use Contao\File;
use Contao\FilesModel;
use Contao\FrontendUser;
use Haste\Util\FileUpload;
use Haste\Util\StringUtil;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Model\Attribute;
use Isotope\Model\ProductCollectionItem;

class PostCheckoutUploads
{
    public function onPostCheckout(IsotopeOrderableCollection $order)
    {
        $items    = $order->getItems();
        $total    = \count($items);
        $position = 0;

        foreach ($items as $item) {
            ++$position;

            $hasChanges = false;
            $configuration = \Contao\StringUtil::deserialize($item->configuration);

            if (!\is_array($configuration)) {
                continue;
            }

            foreach ($configuration as $attributeName => $value) {
                /** @var Attribute $attribute */
                $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attributeName] ?? null;

                if (!$attribute instanceof \uploadable || !$attribute->checkoutRelocate) {
                    continue;
                }

                if (\is_array($value)) {
                    foreach ($value as $i => $source) {
                        $value[$i] = $this->renameFile($order, $item, $position, $total, $attribute, $source);
                    }
                } else {
                    $value = $this->renameFile($order, $item, $position, $total, $attribute, $value);
                }

                $configuration[$attributeName] = $value;
                $hasChanges = true;
            }

            if ($hasChanges) {
                $item->configuration = serialize($configuration);
                $item->save();
            }
        }
    }

    /**
     * @param ProductCollectionItem    $item
     * @param int                      $position
     * @param int                      $total
     * @param Attribute                $attribute
     * @param string                   $source
     * @return array
     */
    private function generateTokens(IsotopeOrderableCollection $order, $item, $position, $total, $attribute, $source)
    {
        $isMember = \Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER');

        $tokens = [
            'document_number'  => $order->getDocumentNumber() ?: $order->getId(),
            'order_id'         => $order->getId(),
            'order_date'       => $order->getLockTime(),
            'product_id'       => $item->product_id,
            'product_sku'      => $item->sku,
            'product_name'     => $item->name,
            'product_position' => str_pad($position, max(3, \strlen((string) $total)), '0', STR_PAD_LEFT),
            'attribute_field'  => $attribute->field_name,
            'attribute_name'   => $attribute->name,
            'file_name'        => basename($source),
            'file_extension'   => pathinfo($source, PATHINFO_EXTENSION),
            'has_member'       => $isMember ? '1' : '0'
        ];

        if ($isMember) {
            $userData = FrontendUser::getInstance()->getData();
            unset($userData['password']);

            StringUtil::flatten(
                $userData,
                'member',
                $tokens
            );

            if ($userData['assignDir']) {
                $homeDir = FilesModel::findByPk($userData['homeDir']);
                $tokens['member_homeDir'] = null !== $homeDir ? $homeDir->path : '';
            }
        }

        return $tokens;
    }

    private function renameFile(IsotopeOrderableCollection $order, $item, $position, $total, $attribute, $source)
    {
        $tokens = $this->generateTokens($order, $item, $position, $total, $attribute, $source);

        $targetFolder = StringUtil::recursiveReplaceTokensAndTags(
            $attribute->checkoutTargetFolder,
            $tokens,
            StringUtil::NO_TAGS | StringUtil::NO_BREAKS
        );

        if ($attribute->doNotOverwrite) {
            $tokens['file_target'] = FileUpload::getFileName($tokens['file_name'], $targetFolder);
        } else {
            $tokens['file_target'] = $tokens['file_name'];
        }

        $targetFile = StringUtil::recursiveReplaceTokensAndTags(
            $attribute->checkoutTargetFile,
            $tokens,
            StringUtil::NO_TAGS | StringUtil::NO_BREAKS
        );

        $file = new File($source);
        $file->renameTo($targetFolder . '/' . $targetFile);

        return $targetFolder . '/' . $targetFile;
    }
}
