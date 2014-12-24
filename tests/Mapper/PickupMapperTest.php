<?php
/**
 * PickupMapperTest.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:56
 */

namespace Webit\Shipment\GlsAdapter\Tests\Mapper;

use Webit\GlsAde\Model\Pickup;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\Consignment\DispatchConfirmationRepositoryInterface;
use Webit\Shipment\GlsAdapter\Mapper\PickupMapper;
use Webit\Shipment\Vendor\VendorInterface;

/**
 * Class PickupMapperTest
 * @package Webit\Shipment\GlsAdapter\Tests\Mapper
 */
class PickupMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DispatchConfirmationRepositoryInterface
     */
    private $dispatchConfirmationRepository;

    /**
     * @var Pickup
     */
    private $pickup;

    /**
     * @var string
     */
    private $pickupId = '1234';

    /**
     * @var VendorInterface
     */
    private $vendor;

    /**
     * @var PickupMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->dispatchConfirmationRepository = $this->createDispatchConfirmationRepository();
        $this->mapper = new PickupMapper($this->dispatchConfirmationRepository);
        $this->pickup = $this->createPickup();
        $this->vendor = $this->createVendor();
    }

    /**
     * @test
     */
    public function shouldMapNumberAndDate()
    {
        $createdAt = $this->getMock('\DateTime');
        $this->pickup->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);

        $confirmation = $this->createConfirmation();
        $confirmation->expects($this->once())->method('setNumber')->with($this->equalTo($this->pickupId));
        $confirmation->expects($this->once())->method('setDispatchedAt')->with($this->equalTo($createdAt));
        $this->dispatchConfirmationRepository->expects($this->once())->method('getDispatchConfirmation')->willReturn($confirmation);

        $this->mapper->mapPickup($this->vendor, $this->pickup, $this->pickupId);
    }

    /**
     * @test
     */
    public function shouldMapToExistentConfirmation()
    {
        $createdAt = $this->getMock('\DateTime');
        $this->pickup->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);

        $confirmation = $this->createConfirmation();
        $this->dispatchConfirmationRepository->expects($this->once())->method('getDispatchConfirmation')->willReturn($confirmation);
        $this->dispatchConfirmationRepository->expects($this->never())->method('createDispatchConfirmation');

        $this->mapper->mapPickup($this->vendor, $this->pickup, $this->pickupId);
    }

    /**
     * @test
     */
    public function shouldMapToNewConfirmation()
    {
        $createdAt = $this->getMock('\DateTime');
        $this->pickup->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);

        $confirmation = $this->createConfirmation();
        $this->dispatchConfirmationRepository->expects($this->once())->method('getDispatchConfirmation')->willReturn(null);
        $this->dispatchConfirmationRepository->expects($this->once())->method('createDispatchConfirmation')->willReturn($confirmation);

        $this->mapper->mapPickup($this->vendor, $this->pickup, $this->pickupId);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DispatchConfirmationRepositoryInterface
     */
    private function createDispatchConfirmationRepository()
    {
        return $this->getMock('Webit\Shipment\Consignment\DispatchConfirmationRepositoryInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DispatchConfirmationInterface
     */
    private function createConfirmation()
    {
        return $this->getMock('Webit\Shipment\Consignment\DispatchConfirmationInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Pickup
     */
    private function createPickup()
    {
        return $this->getMock('Webit\GlsAde\Model\Pickup');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|VendorInterface
     */
    private function createVendor()
    {
        return $this->getMock('Webit\Shipment\Vendor\VendorInterface');
    }
}
