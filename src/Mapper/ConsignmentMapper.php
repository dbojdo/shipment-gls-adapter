<?php
/**
 * File ConsignmentMapper.php
 * Created at: 2014-12-21 15-42
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

use Webit\GlsAde\Model\Consignment;
use Webit\GlsAde\Model\Parcel;
use Webit\GlsAde\Model\SenderAddress;
use Webit\GlsAde\Model\ServicesBool;
use Webit\Shipment\Address\DefaultSenderAddressProviderInterface;
use Webit\Shipment\Address\SenderAddressInterface;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\GlsAdapter\Exception\UnsupportedOperationException;
use Webit\Shipment\Parcel\ParcelInterface;
use Webit\Shipment\Vendor\VendorOptionValueInterface;

class ConsignmentMapper
{
    /**
     * @var ServiceOptionMapper
     */
    private $serviceOptionMapper;

    /**
     * @var \Webit\Shipment\Address\DefaultSenderAddressProviderInterface
     */
    private $defaultSenderProvider;

    /**
     * @param ServiceOptionMapper $serviceOptionMapper
     * @param DefaultSenderAddressProviderInterface $defaultSenderAddressProvider
     */
    public function __construct(
        ServiceOptionMapper $serviceOptionMapper,
        DefaultSenderAddressProviderInterface $defaultSenderAddressProvider
    ) {
        $this->serviceOptionMapper = $serviceOptionMapper;
        $this->defaultSenderProvider = $defaultSenderAddressProvider;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     * @return Consignment
     */
    public function mapConsignment(ConsignmentInterface $consignment, Consignment $glsConsignment = null)
    {
        $glsConsignment = $glsConsignment ?: new Consignment();

        $this->mapDeliveryAddress($consignment, $glsConsignment);
        $this->mapSenderAddress($consignment, $glsConsignment);
        $this->mapServices($consignment, $glsConsignment);
        $this->mapParcels($consignment, $glsConsignment);

        if ($dispatchConfirmation = $consignment->getDispatchConfirmation()) {
            $glsConsignment->setDate($dispatchConfirmation->getPickUpAt());
        }

        return $glsConsignment;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapServices(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        $servicesBool = new ServicesBool();
        $servicesBool->setCod($consignment->isCod() ? 1 : 0);
        $servicesBool->setCodAmount($consignment->isCod() ? $consignment->getCodAmount() : null);

        /**
         * @var string $code
         * @var VendorOptionValueInterface $optionValue
         */
        foreach ($consignment->getVendorOptions() as $code => $optionValue) {
            $service = $this->serviceOptionMapper->mapOptionCode($code);
            if (!$service) {
                continue;
            }

            $setter = sprintf('set%s', ucfirst($service));
            call_user_func(array($servicesBool, $setter), $optionValue->getValue());

            $optionValue->getValue();
        }

        if ($servicesBool->getPr() || $servicesBool->getPs() || $servicesBool->getExc() || $servicesBool->getSrs()) {
            $this->mapPpeData($consignment, $glsConsignment);
        }

        if ($servicesBool->getIdent()) {
            $this->mapIdentData($consignment, $glsConsignment);
        }

        if ($servicesBool->getDaw()) {
            $this->mapDawData($consignment, $glsConsignment);
        }
        $glsConsignment->setServicesBool($servicesBool);
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapDeliveryAddress(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        $deliveryAddress = $consignment->getDeliveryAddress();

        if (! $deliveryAddress) {
            return;
        }

        $glsConsignment->setName1($deliveryAddress->getName());
        $glsConsignment->setStreet($deliveryAddress->getAddress());
        $glsConsignment->setZipCode($deliveryAddress->getPostCode());
        $glsConsignment->setCity($deliveryAddress->getPost());
        $glsConsignment->setCountry(
            $deliveryAddress->getCountry() ? $deliveryAddress->getCountry()->getIsoCode() : null
        );

        $contact = sprintf('%s / %s', $deliveryAddress->getContactEmail(), $deliveryAddress->getContactPerson());
        $contact = trim($contact, ' /');
        $glsConsignment->setContact($contact);

        $glsConsignment->setPhone($deliveryAddress->getContactPhoneNo());
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapSenderAddress(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        $senderAddress = $consignment->getSenderAddress() ?: $this->defaultSenderProvider->getSender();
        $glsSenderAddress = $this->senderAddress($senderAddress);

        $glsConsignment->setSenderAddress($glsSenderAddress);
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapParcels(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        foreach ($glsConsignment->getParcels() as $glsParcel) {
            if (!$glsParcel->getNumber()) {
                $glsConsignment->removeParcel($glsParcel);
            }
        }

        /** @var ParcelInterface $parcel */
        foreach ($consignment->getParcels() as $parcel) {
            $glsParcel = $glsConsignment->getParcels()->filter(function (Parcel $glsParcel) use ($parcel) {
                return $parcel->getNumber() && $glsParcel->getNumber() == $parcel->getNumber();
            });

            $glsParcel = $glsParcel->first();
            $glsParcel = $glsParcel ?: new Parcel();

            $this->mapParcel($parcel, $glsParcel);

            $glsParcel->setServicesBool($glsConsignment->getServicesBool());

            $glsConsignment->addParcel($glsParcel);
        }
    }

    /**
     * @param ParcelInterface $parcel
     * @param Parcel $glsParcel
     */
    private function mapParcel(ParcelInterface $parcel, Parcel $glsParcel)
    {
        $glsParcel->setReference($parcel->getReference());
        $glsParcel->setWeight($parcel->getWeight());
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapPpeData(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        throw new UnsupportedOperationException('Services PR, PS, EXC and SRS are not supported by this adapter yet.');
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapIdentData(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        throw new UnsupportedOperationException('Service IDENT is not supported by this adapter yet.');
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     */
    private function mapDawData(ConsignmentInterface $consignment, Consignment $glsConsignment)
    {
        throw new UnsupportedOperationException('Service DAW is not supported by this adapter yet.');
    }

    /**
     * @param SenderAddressInterface $senderAddress
     * @return SenderAddress
     */
    private function senderAddress(SenderAddressInterface $senderAddress = null)
    {
        $glsSender = new SenderAddress();
        if (! $senderAddress) {
            return $glsSender;
        }

        $glsSender->setName1($senderAddress->getName());
        $glsSender->setZipCode($senderAddress->getPostCode());
        $glsSender->setCity($senderAddress->getPost());
        $glsSender->setStreet($senderAddress->getAddress());
        $glsSender->setCountry($senderAddress->getCountry() ? $senderAddress->getCountry()->getIsoCode() : 'PL');

        return $glsSender;
    }
}
