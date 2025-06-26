<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

interface ShopConfigInterface
{
    public function getGroup(): string;

    public function getName(): string;

    public function getUrl(): string|null;

    public function getIcon(): string|null;

    /**
     * @return list<string>
     */
    public function getTables(): array;
}
