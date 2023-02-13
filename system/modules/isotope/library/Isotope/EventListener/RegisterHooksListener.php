<?php

namespace Isotope\EventListener;

class RegisterHooksListener
{
    /**
     * @var array
     */
    private $hooks;

    public function __construct(array $hooks)
    {
        $this->hooks = $hooks;
    }

    public function onInitializeSystem(): void
    {
        if (isset($GLOBALS['ISO_HOOKS']) && \is_array($GLOBALS['ISO_HOOKS'])) {
            $GLOBALS['ISO_HOOKS'] = array_merge_recursive($GLOBALS['ISO_HOOKS'], $this->hooks);
        } else {
            $GLOBALS['ISO_HOOKS'] = $this->hooks;
        }
    }
}
