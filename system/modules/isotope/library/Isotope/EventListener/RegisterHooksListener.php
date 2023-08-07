<?php

namespace Isotope\EventListener;

class RegisterHooksListener
{
    /**
     * @var array
     */
    private $hookListeners;

    public function __construct(array $hookListeners)
    {
        $this->hookListeners = $hookListeners;
    }

    public function __invoke(): void
    {
        foreach ($this->hookListeners as $hookName => $priorities) {
            if (isset($GLOBALS['ISO_HOOKS'][$hookName]) && \is_array($GLOBALS['ISO_HOOKS'][$hookName])) {
                if (isset($priorities[0])) {
                    $priorities[0] = array_merge($GLOBALS['ISO_HOOKS'][$hookName], $priorities[0]);
                } else {
                    $priorities[0] = $GLOBALS['ISO_HOOKS'][$hookName];
                    krsort($priorities);
                }
            }

            $GLOBALS['ISO_HOOKS'][$hookName] = array_merge(...$priorities);
        }
    }
}
