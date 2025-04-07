<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Isotope\Interfaces\IsotopeProductCollection;

/**
 * Documents print a collection.
 */
interface DocumentInterface
{
    public function getId(): int;

    /**
     * Generates the document and sends it to browser.
     */
    public function outputToBrowser(IsotopeProductCollection $objCollection);

    /**
     * Generates the document to the given path and returns the absolute path of the file.
     */
    public function outputToFile(IsotopeProductCollection $objCollection, string $path): string;
}
