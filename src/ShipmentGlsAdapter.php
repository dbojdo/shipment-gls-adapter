<?php
/**
 * File ShipmentGlsAdapter.php
 * Created at: 2014-12-08 05-58
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter;

use Webit\GlsAde\Api\ConsignmentPrepareApi;
use Webit\GlsAde\Api\Exception\GlsAdeApiException;
use Webit\GlsAde\Api\PickupApi;
use Webit\GlsAde\Model\ConsignmentLabelModes;
use Webit\GlsAde\Model\PickupReceiptModes;
use Webit\GlsTracking\Api\TrackingApi;
use Webit\GlsTracking\Model\Event;
use Webit\GlsTracking\UrlProvider\TrackingUrlProvider;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\Consignment\ConsignmentStatusList;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\GlsAdapter\Exception\InvalidStateException;
use Webit\Shipment\GlsAdapter\Exception\UnsupportedOperationException;
use Webit\Shipment\GlsAdapter\Mapper\ConsignmentMapper;
use Webit\Shipment\GlsAdapter\Mapper\GlsConsignmentMapper;
use Webit\Shipment\GlsAdapter\Mapper\PickupMapper;
use Webit\Shipment\Manager\VendorAdapterInterface;
use Webit\Shipment\Parcel\ParcelInterface;
use Webit\Tools\Data\FilterCollection;
use Webit\Tools\Data\SorterCollection;

class ShipmentGlsAdapter implements VendorAdapterInterface
{
    const VENDOR_CODE = 'gls';

    private static $supportedLanguages = array(
        'EN', 'PL'
    );

    /**
     * @var ConsignmentPrepareApi
     */
    private $prepareConsignmentApi;

    /**
     * @var PickupApi
     */
    private $pickupApi;

    /**
     * @var TrackingApi
     */
    private $trackingApi;

    /**
     * @var TrackingUrlProvider
     */
    private $urlProvider;

    /**
     * @var VendorFactory
     */
    private $vendorFactory;

    /**
     * @var ConsignmentMapper
     */
    private $consignmentMapper;

    /**
     * @var GlsConsignmentMapper
     */
    private $glsConsignmentMapper;

    /**
     * @var PickupMapper
     */
    private $pickupMapper;

    /**
     * @param ConsignmentPrepareApi $prepareConsignmentApi
     * @param PickupApi $pickupApi
     * @param TrackingApi $trackingApi
     * @param TrackingUrlProvider $urlProvider
     * @param VendorFactory $vendorFactory
     * @param ConsignmentMapper $consignmentMapper
     * @param GlsConsignmentMapper $glsConsignmentMapper
     * @param PickupMapper $pickupMapper
     */
    public function __construct(
        ConsignmentPrepareApi $prepareConsignmentApi,
        PickupApi $pickupApi,
        TrackingApi $trackingApi,
        TrackingUrlProvider $urlProvider,
        VendorFactory $vendorFactory,
        ConsignmentMapper $consignmentMapper,
        GlsConsignmentMapper $glsConsignmentMapper,
        PickupMapper $pickupMapper
    ) {
        $this->prepareConsignmentApi = $prepareConsignmentApi;
        $this->pickupApi = $pickupApi;
        $this->trackingApi = $trackingApi;
        $this->urlProvider = $urlProvider;
        $this->vendorFactory = $vendorFactory;
        $this->consignmentMapper = $consignmentMapper;
        $this->glsConsignmentMapper = $glsConsignmentMapper;
        $this->pickupMapper = $pickupMapper;
    }

    /**
     * @inheritdoc
     */
    public function getConsignments(
        FilterCollection $filters = null,
        SorterCollection $sorters = null,
        $limit = 50,
        $offset = 0
    ) {
        throw new UnsupportedOperationException('Getting consignments list is not supported yet.');
    }

    /**
     * @inheritdoc
     */
    public function dispatch(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $ids = array();

        /** @var ConsignmentInterface $consignment */
        foreach ($dispatchConfirmation->getConsignments() as $consignment) {
            $this->saveConsignment($consignment);
            $ids[] = $consignment->getVendorId();
        }

        $now = new \DateTime();
        $pickupId = $this->pickupApi->createPickup($ids, 'Pickup: ' . $now->format('Y-m-d H:i:s'));
        $pickup = $this->pickupApi->getPickup($pickupId);

        $this->pickupMapper->mapPickup(
            $pickup,
            $pickupId,
            $dispatchConfirmation
        );
    }

    /**
     * @inheritdoc
     */
    public function saveConsignment(ConsignmentInterface $consignment)
    {
        $glsConsignment = $this->getGlsConsignment($consignment);
        if ($glsConsignment) {
            if ($glsConsignment->isDispatched()) {
                throw new InvalidStateException('Cannot modify dispatched consignment');
            }

            $this->prepareConsignmentApi->deleteConsignment($glsConsignment->getId());
            $glsConsignment = null;
        }

        $glsConsignment = $this->consignmentMapper->mapConsignment($consignment, $glsConsignment);

        $vendorId = $this->prepareConsignmentApi->insertConsignment($glsConsignment);
        $consignment->setVendorId($vendorId);
    }

    /**
     * @inheritdoc
     */
    public function removeConsignment(ConsignmentInterface $consignment)
    {
        $glsConsignment = $this->getGlsConsignment($consignment);
        if ($glsConsignment && $glsConsignment->isDispatched()) {
            throw new InvalidStateException('Cannot remove dispatched consignment');
        }

        if ($glsConsignment) {
            $this->prepareConsignmentApi->deleteConsignment($glsConsignment->getId());
        }
    }

    /**
     * @inheritdoc
     */
    public function cancelConsignment(ConsignmentInterface $consignment)
    {
        $glsConsignment = $this->getGlsConsignment($consignment);
        if ($glsConsignment->isDispatched()) {
            throw new UnsupportedOperationException('Cannot cancel dispatched consignment');
        }

        $this->prepareConsignmentApi->deleteConsignment($glsConsignment->getId());
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentLabel(ConsignmentInterface $consignment, $mode = null)
    {
        $mode = $mode ?: ConsignmentLabelModes::MODE_FOUR_LABELS_ON_A4_PDF;

        $glsConsignment = $this->getGlsConsignment($consignment);
        if ($glsConsignment->isDispatched()) {
            return $this->pickupApi->getConsignmentLabels($glsConsignment->getId(), $mode);
        }

        return $this->prepareConsignmentApi->getConsignmentLabels($glsConsignment->getId(), $mode);
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentDispatchConfirmationLabel(
        DispatchConfirmationInterface $dispatchConfirmation,
        $mode = null
    ) {
        $mode = $mode ?: ConsignmentLabelModes::MODE_FOUR_LABELS_ON_A4_PDF;

        return $this->pickupApi->getPickupLabels($dispatchConfirmation->getNumber(), $mode);
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentDispatchConfirmationReceipt(
        DispatchConfirmationInterface $dispatchConfirmation,
        $mode = null
    ) {
        $mode = $mode ?: PickupReceiptModes::MODE_CONDENSED;

        $this->pickupApi->getPickupReceipt($dispatchConfirmation->getNumber(), $mode);
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentTrackingUrl(ConsignmentInterface $consignment)
    {
        $arReference = array();
        /** @var ParcelInterface $parcel */
        foreach ($consignment->getParcels() as $parcel) {
            $arReference[] = $parcel->getNumber();
        }

        $country = $consignment->getDeliveryAddress() ? $consignment->getDeliveryAddress()->getCountry() : null;
        if (! $country) {
            throw new \UnexpectedValueException('Cannot determinate consignment delivery country.');
        }

        $language = in_array($country->getIsoCode(), self::$supportedLanguages) ? $country->getIsoCode() : 'EN';

        return $this->urlProvider->getStandardTrackingUrl(implode(',', $arReference), $country->getIsoCode(), $language);
    }

    /**
     * @inheritdoc
     */
    public function createVendor()
    {
        return $this->vendorFactory->createVendor();
    }

    /**
     * @param ConsignmentInterface $consignment
     * @return \Webit\GlsAde\Model\Consignment
     * @throws GlsAdeApiException
     * @throws \Exception
     */
    private function getGlsConsignment(ConsignmentInterface $consignment)
    {
        if (! $consignment->getStatus() || ! $consignment->getVendorId()) {
            return null;
        }

        if ($consignment->getStatus() == ConsignmentStatusList::STATUS_NEW) {
            try {
                return $this->prepareConsignmentApi->getConsignment($consignment->getVendorId());
            } catch (GlsAdeApiException $e) {
                if ($e->getApiErrorCode() == GlsAdeApiException::ERROR_CONSIGNMENT_NOT_FOUND) {
                    return null;
                }

                throw $e;
            }
        }

        try {
            $glsConsignment = $this->pickupApi->getConsignment($consignment->getVendorId());
        } catch (GlsAdeApiException $e) {
            if ($e->getApiErrorCode() == GlsAdeApiException::ERROR_PICKUP_NOT_FOUND) {
                return null;
            }

            throw $e;
        }

        return $glsConsignment;
    }

    /**
     * @inheritdoc
     */
    public function synchronizeConsignment(ConsignmentInterface $consignment)
    {
        $glsConsignment = $this->getGlsConsignment($consignment);
        $this->glsConsignmentMapper->mapGlsConsignment($glsConsignment, $consignment);
    }

    /**
     * @inheritdoc
     */
    public function synchronizeParcelStatus(ParcelInterface $parcel)
    {
        $details = $this->trackingApi->getParcelDetails($parcel->getReference());

        if (! $details) {
            return;
        }

        /** @var Event $event */
        $event = $details->getHistory()->first();
        if (! $event) {
            return;
        }

        $parcel->setVendorStatus($event->getCode());
        $status = $this->glsConsignmentMapper->mapParcelStatus($event->getCode());
        $parcel->setStatus($status);
    }
}
