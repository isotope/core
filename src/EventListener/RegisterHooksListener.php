<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

class RegisterHooksListener
{
    /**
     * @param array<string, array<int, array<callable>>> $hookListeners
     */
    public function __construct(private readonly array $hookListeners)
    {
    }

    public function __invoke(): void
    {
        foreach ($this->hookListeners as $hookName => $priorities) {
            if (isset($GLOBALS['ISO_HOOKS'][$hookName]) && \is_array($GLOBALS['ISO_HOOKS'][$hookName])) {
                if (isset($priorities[0])) {
                    $priorities[0] = [...array_values($GLOBALS['ISO_HOOKS'][$hookName]), ...$priorities[0]];
                } else {
                    $priorities[0] = $GLOBALS['ISO_HOOKS'][$hookName];
                    krsort($priorities);
                }
            }

            $GLOBALS['ISO_HOOKS'][$hookName] = array_merge(...array_values($priorities));
        }
    }
}
