<?php
/**
 * PickupMapper.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:14
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

use Webit\GlsAde\Model\Pickup;
use Webit\Shipment\Consignment\DispatchConfirmationRepositoryInterface;
use Webit\Shipment\Vendor\VendorInterface;

/**
 * Class PickupMapper
 * @package Webit\Shipment\GlsAdapter\Mapper
 */
class PickupMapper
{
    /**
     * @var DispatchConfirmationRepositoryInterface
     */
    private $dispatchConfirmationRepository;

    /**
     * @param DispatchConfirmationRepositoryInterface $dispatchConfirmationRepository
     */
    public function __construct(DispatchConfirmationRepositoryInterface $dispatchConfirmationRepository)
    {
        $this->dispatchConfirmationRepository = $dispatchConfirmationRepository;
    }


    public function mapPickup(VendorInterface $vendor, Pickup $pickup, $pickupId)
    {
        $dispatchConfirmation = $this->dispatchConfirmationRepository->getDispatchConfirmation($vendor, $pickupId);
        $dispatchConfirmation = $dispatchConfirmation ?: $this->dispatchConfirmationRepository->createDispatchConfirmation();
        $dispatchConfirmation->setNumber($pickupId);
        $dispatchConfirmation->setDispatchedAt($pickup->getCreatedAt());

        return $dispatchConfirmation;
    }
}
 