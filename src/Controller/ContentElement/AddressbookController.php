<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Codefog\HasteBundle\Form\Form;
use Contao\ContentModel;
use Contao\CoreBundle\Cache\CacheTagManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsContentElement('iso_addressbook', category: 'isotope')]
class AddressbookController extends AbstractContentElementController
{
    public function __construct(
        private readonly AddressFormatter $addressFormatter,
        private readonly Connection $connection,
        private readonly CacheTagManager $cacheTagManager,
        private readonly AddressValidator $addressValidator,
        private readonly TranslatorInterface $translator,
        #[Autowire(service: 'monolog.logger.contao.general')] private readonly LoggerInterface $logger,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof FrontendUser) {
            return new Response('');
        }

        $addressConfig = $this->getStoreContext()->getBillingAddressConfig()->mergeWith($this->getStoreContext()->getShippingAddressConfig());

        if ($this->isAction($request, $model, 'create')) {
            $address = new Address();
            $address->ptable = 'tl_member';
            $address->pid = $user->id;

            $template->form = $this->getAddressForm($request, 'create', $model, $address, $addressConfig);

            return $template->getResponse();
        }

        $addresses = Address::findByMemberAndStoreId($user->id, $this->getStoreContext()->getStoreId())?->getModels() ?? [];

        if ($this->isAction($request, $model, 'edit')) {
            $id = $request->request->getInt('address_id');

            foreach ($addresses as $k => $address) {
                if ((int) $address->id === $id) {
                    $template->form = $this->getAddressForm($request, 'edit', $model, $address, $addressConfig);

                    return $template->getResponse();
                }
            }

            throw new BadRequestHttpException();
        }

        if ($this->isAction($request, $model, 'delete')) {
            $id = $request->request->getInt('address_id');

            foreach ($addresses as $k => $address) {
                if ((int) $address->id === $id) {
                    $this->deleteAddress($address);
                    unset($addresses[$k]);
                    break;
                }
            }
        }

        $this->tagResponse($addresses);

        $template->addresses = array_map(
            fn ($address) => $this->addressFormatter->get($address, $addressConfig),
            $addresses,
        );

        return $template->getResponse();
    }


    /**
     * Replicate DC_Table::delete() behaviour.
     */
    private function deleteAddress(Address $address): void
    {
        $addressId = $address->id;

        // Add an undo record so a backend admin could undo deleting the address
        $this->connection->insert(
            'tl_undo',
            [
                'pid' => 0,
                'tstamp' => time(),
                'fromTable' => 'tl_iso_address',
                'query' => 'DELETE FROM tl_iso_address WHERE id='.$addressId,
                'affectedRows' => 1,
                'data' => serialize(['tl_iso_address' => [$address->row()]]),
                'preview' => (string) $this->addressFormatter->get($address, $this->getStoreContext()->getBillingAddressConfig()),
            ]
        );

        $this->cacheTagManager->invalidateTagsForModelInstance($address);

        $address->delete();

        $this->logger->info('DELETE FROM tl_iso_address WHERE id='.$addressId);
    }

    private function isAction(Request $request, ContentModel $model, string $action): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $action.'_address_'.$model->id === $request->request->get('FORM_SUBMIT');
    }

    private function getAddressForm(Request $request, string $action, ContentModel $model, Address $address, AddressConfig $addressConfig): \stdClass
    {
        $formId = $action.'_address_'.$model->id;

        $form = new Form($formId, 'POST', static fn () => $request->isMethod('POST') && $formId === $request->request->get('FORM_SUBMIT') && $request->request->has($action.'_address'));
        $form->setBoundModel($address);
        $form->addFieldsFromDca('tl_iso_address', fn (string $field, array &$config) => $this->addDcaField($field, $config, $addressConfig));
        $form->addSubmitFormField($this->translator->trans('MSC.submitLabel', [], 'contao_default'), $action.'_address');

        if ($address->id) {
            $form->addFormField('address_id', [
                'inputType' => 'hidden',
                'ignoreModelValue' => true,
                'value' => $address->id,
            ]);
        }

        $form->validate();
        $errors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $violations = $this->addressValidator->validate($address, $addressConfig);

            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
            } else {
                $address->save();

                throw new RedirectResponseException($request->getRequestUri());
            }
        }

        $templateObject = $form->getHelperObject();
        $templateObject->class = '';
        $templateObject->errors = $errors;
        $templateObject->formSubmit = $templateObject->formId;

        return $templateObject;
    }

    private function addDcaField(string $field, array &$config, AddressConfig $addressConfig): bool
    {
        if (!($config['eval']['feEditable'] ?? false) || !\in_array($field, $addressConfig->getFields(), true)) {
            return false;
        }

        $config['eval']['mandatory'] = $addressConfig->isMandatory($field);

        return true;
    }
}
