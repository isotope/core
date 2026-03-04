<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Contao\CoreBundle\Cache\CacheTagManager;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Address\FormBuilder;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddressManager
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
        private readonly AddressFormatter $addressFormatter,
        private readonly FormBuilder $formBuilder,
        private readonly Connection $connection,
        private readonly CacheTagManager $cacheTagManager,
        #[Autowire(service: 'monolog.logger.contao.general')] private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function generate(Request $request, int $memberId, int $storeId, AddressConfig $addressConfig, string $identifier): array
    {
        if ($this->isAction($request, $identifier, 'create')) {
            $address = new Address();
            $address->ptable = 'tl_member';
            $address->pid = $memberId;

            return $this->generateForm($request, $address, $addressConfig, $identifier, 'create');
        }

        $addresses = $this->getAddresses($memberId, $addressConfig, $storeId);

        if ($this->isAction($request, $identifier, 'edit')) {
            $id = $request->request->getInt('edit_address_'.$identifier);
            $address = array_find($addresses, fn (Address $address) => (int) $address->id === $id);

            if (!$address) {
                throw new BadRequestHttpException();
            }

            return $this->generateForm($request, $address, $addressConfig, $identifier, 'edit');
        }

        if ($this->isAction($request, $identifier, 'delete')) {
            $id = $request->request->getInt('delete_address_'.$identifier);
            $address = array_find($addresses, fn (Address $address) => (int) $address->id === $id);

            if (!$address) {
                throw new BadRequestHttpException();
            }

            $this->deleteAddress($address, $addressConfig);

            throw new RedirectResponseException($request->getRequestUri());
        }

        $this->cacheTagManager->tagWith($addresses);

        return [
            'identifier' => $identifier,
            'addresses' => array_map(
                fn ($address) => $this->addressFormatter->toHtml($address, $addressConfig),
                $addresses,
            ),
        ];
    }

    private function generateForm(Request $request, Address $address, AddressConfig $addressConfig, string $identifier, string $action): array
    {
        $form = $this->formBuilder->build($request, $address, $addressConfig, $identifier.'_');
        $form->addFormField($action.'_address_'.$identifier, [
            'inputType' => 'hidden',
            'ignoreModelValue' => true,
            'value' => $address->id ?? 1,
        ]);
        $form->addSubmitFormField($this->translator->trans('MSC.submitLabel', [], 'contao_default'));

        $result = $this->formBuilder->validate($form, $addressConfig);

        if (true === $result) {
            $address->save();

            throw new RedirectResponseException($request->getRequestUri());
        }

        return [
            'form' => $this->formBuilder->render($form),
        ];
    }

    /**
     * Replicate DC_Table::delete() behaviour.
     */
    private function deleteAddress(Address $address, AddressConfig $addressConfig): void
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
                'preview' => (string) $this->addressFormatter->toHtml($address, $addressConfig),
            ]
        );

        $this->cacheTagManager->invalidateTagsForModelInstance($address);

        $address->delete();

        $this->logger->info('DELETE FROM tl_iso_address WHERE id='.$addressId);
    }

    private function isAction(Request $request, string $identifier, string $action): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->request->has($action.'_address_'.$identifier);
    }

    /**
     * @return array<Address>
     */
    private function getAddresses(int $memberId, AddressConfig $addressConfig, int $storeId): array
    {
        $addresses = Address::findByMemberAndStoreId($memberId, $storeId)?->getModels() ?? [];

        foreach ($addresses as $k => $address) {
            if (!$this->addressValidator->isValid($address, $addressConfig)) {
                unset($addresses[$k]);
            }
        }

        return $addresses;
    }
}
