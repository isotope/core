<?php

namespace Isotope\EventListener;

use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\FrontendUser;
use Haste\Util\FileUpload;
use Haste\Util\StringUtil;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Attribute;
use Isotope\Model\Config;
use Isotope\Model\Product\Standard as StandardProduct;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;

class PostCheckoutUploads
{

    /**
     * @param IsotopeProductCollection|Order $order
     */
    public function onPostCheckout(IsotopeProductCollection $order)
    {
        $items    = $order->getItems();
        $total    = count($items);
        $position = 0;

        foreach ($items as $item) {
            ++$position;

            foreach ($item->getConfiguration() as $attributeName => $config) {
                /** @var Attribute $attribute */
                $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attributeName];

                if (!$attribute instanceof \uploadable || !$attribute->checkoutRelocate) {
                    continue;
                }

                $sources = $this->getSources($attribute, $config['value']);

                foreach ($sources as $source) {
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
                }
            }
        }
    }

    /**
     * @param Attribute    $attribute
     * @param string|array $files
     *
     * @return array
     * @throws \Exception
     */
    private function getSources(Attribute $attribute, $files)
    {
        $sources = [];
        $folder  = $attribute->uploadFolder;

        // Overwrite the upload folder with user's home directory
        if ($attribute->useHomeDir && FE_USER_LOGGED_IN) {
            $user = FrontendUser::getInstance();

            if ($user->assignDir && $user->homeDir) {
                $folder = $user->homeDir;
            }
        }

        $filesModel = FilesModel::findByPk($folder);

        // The upload folder could not be found
        if (null === $filesModel) {
            throw new \Exception("Invalid upload folder ID $folder");
        }

        foreach ((array) $files as $file) {
            $sources[] = $filesModel->path . '/' . $file;
        }

        return $sources;
    }

    /**
     * @param Order                 $order
     * @param ProductCollectionItem $item
     * @param int                   $position
     * @param int                   $total
     * @param Attribute             $attribute
     * @param string                $source
     *
     * @return array
     */
    private function generateTokens($order, $item, $position, $total, $attribute, $source)
    {
        return [
            'document_number'  => $order->document_number ?: $order->id,
            'order_id'         => $order->id,
            'order_date'       => $order->locked,
            'product_id'       => $item->product_id,
            'product_sku'      => $item->sku,
            'product_name'     => $item->name,
            'product_position' => str_pad($position, max(3, strlen((string) $total)), '0', STR_PAD_LEFT),
            'attribute_field'  => $attribute->field_name,
            'attribute_name'   => $attribute->name,
            'file_name'        => basename($source),
            'file_extension'   => pathinfo($source, PATHINFO_EXTENSION),
        ];
    }
}
