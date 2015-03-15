<?php
/**
 * PickupMapper.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:14
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

use Webit\GlsAde\Api\PickupApi;
use Webit\GlsAde\Model\Consignment;
use Webit\GlsAde\Model\Pickup;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\GlsAdapter\Exception\MappingException;

/**
 * Class PickupMapper
 * @package Webit\Shipment\GlsAdapter\Mapper
 */
class PickupMapper
{

    /**
     * @var PickupApi
     */
    private $pickupApi;

    /**
     * @var ParcelMapper
     */
    private $parcelMapper;

    /**
     * @param PickupApi $pickupApi
     * @param ParcelMapper $parcelMapper
     */
    public function __construct(PickupApi $pickupApi, ParcelMapper $parcelMapper)
    {
        $this->pickupApi = $pickupApi;
        $this->parcelMapper = $parcelMapper;
    }

    /**
     * @param Pickup $pickup
     * @param $pickupId
     * @param DispatchConfirmationInterface $dispatchConfirmation
     * @return DispatchConfirmationInterface
     */
    public function mapPickup(Pickup $pickup, $pickupId, DispatchConfirmationInterface $dispatchConfirmation)
    {
        $dispatchConfirmation->setNumber($pickupId);
        $dispatchConfirmation->setDispatchedAt($pickup->getCreatedAt());

        $this->mapConsignments($dispatchConfirmation);

        return $dispatchConfirmation;
    }

    /**
     * @param DispatchConfirmationInterface $dispatchConfirmation
     */
    private function mapConsignments(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $glsConsignmentsIds = $this->pickupApi->getAllConsignmentIds($dispatchConfirmation->getNumber());
        foreach ($glsConsignmentsIds as $glsConsignmentId) {
            $glsConsignment = $this->pickupApi->getConsignment($glsConsignmentId);
            $this->mapConsignment($dispatchConfirmation, $glsConsignment);
        }
    }

    private function mapConsignment(DispatchConfirmationInterface $dispatchConfirmation, Consignment $glsConsignment)
    {
        $consignment = $this->findConsignment($dispatchConfirmation, $glsConsignment);
        if (! $consignment) {
            throw new MappingException(
                sprintf(
                    'Can not find Consignment for GlsConsignment (%s) in given DispatchConfirmation (%s)',
                    $glsConsignment->getId(),
                    $dispatchConfirmation->getNumber()
                )
            );
        }

        $consignment->setVendorId($glsConsignment->getId());
        $this->parcelMapper->updateParcelsNumbers($glsConsignment, $consignment);
    }

    /**
     * @param DispatchConfirmationInterface $dispatchConfirmation
     * @param Consignment $glsConsignment
     * @return \Webit\Shipment\Consignment\Consignment
     */
    private function findConsignment(DispatchConfirmationInterface $dispatchConfirmation, Consignment $glsConsignment)
    {
        $glsParcel = $glsConsignment->getParcels()->first();
        /** @var \Webit\Shipment\Consignment\Consignment $consignment */
        foreach ($dispatchConfirmation->getConsignments() as $consignment) {
            $parcel = $consignment->findParcel($glsParcel->getReference());
            if ($parcel) {
                return $consignment;
            }
        }

        return null;
    }
}
