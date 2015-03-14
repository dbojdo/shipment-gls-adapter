<?php
/**
 * PickupMapper.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:14
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

use Webit\GlsAde\Model\Pickup;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\Consignment\DispatchConfirmationRepositoryInterface;
use Webit\Shipment\Vendor\VendorInterface;

/**
 * Class PickupMapper
 * @package Webit\Shipment\GlsAdapter\Mapper
 */
class PickupMapper
{

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

        return $dispatchConfirmation;
    }
}
