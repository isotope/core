<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Util\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsContentElement('iso_configswitcher', category: 'isotope')]
class ConfigSwitcherController extends AbstractContentElementController
{
    public function __construct(
        private readonly CartManager $cartManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $formId = 'iso_configswitcher_'.$model->id;
        $configIds = StringUtil::csv($model->iso_config_ids, intval(...));
        $configTotal = \count($configIds);

        // If the module config has only one config, always override the current cart
        if (1 === $configTotal && $this->overrideConfig(array_first($configIds), $configIds)) {
            throw new RedirectResponseException($request->getRequestUri());
        }

        if ($configTotal < 2) {
            return new Response('');
        }

        if ($request->isMethod(Request::METHOD_POST) && $formId === $request->request->get('FORM_SUBMIT')) {
            $this->overrideConfig($request->request->getInt('config_id'), $configIds);

            throw new RedirectResponseException($request->getRequestUri());
        }

        $configs = [];
        $configModels = ConfigModel::findMultipleByIds($configIds);
        $currentId = $this->getStoreContext()->getConfigId();

        if (null !== $configModels) {
            foreach ($configModels as $config) {
                $configs[] = [
                    'config' => $config,
                    'id' => $config->id,
                    'label' => $this->translator->trans($config->label ?: $config->name, [], 'isotope'),
                    'active' => $currentId === (int) $config->id,
                ];
            }
        }

        $template->form_id = $formId;
        $template->configs = $configs;

        return $template->getResponse();
    }

    private function overrideConfig(int $id, array $configIds, bool $force = false): bool
    {
        $context = $this->getStoreContext();

        if (
            (!$force && $context->getConfigId() === $id)
            || !\in_array($id, $configIds, true)
            || !ConfigModel::findById($id)
        ) {
            return false;
        }

        $context->setConfigId($id);

        $cart = $context->getCart();
        $cart->setModified();

        $this->cartManager->recalculate($cart, $context);

        return true;
    }
}
