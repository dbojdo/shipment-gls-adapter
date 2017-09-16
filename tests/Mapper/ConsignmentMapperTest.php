<?php
/**
 * ConsignmentMapperTest.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 09:57
 */

namespace Webit\Shipment\GlsAdapter\Tests\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\GlsAde\Model\Consignment;
use Webit\Shipment\Address\DefaultSenderAddressProviderInterface;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\GlsAdapter\Mapper\ConsignmentMapper;
use Webit\Shipment\GlsAdapter\Mapper\ServiceOptionMapper;
use Webit\Shipment\Vendor\VendorOptionValueCollection;

/**
 * Class ConsignmentMapperTest
 * @package Webit\Shipment\GlsAdapter\Tests\Mapper
 */
class ConsignmentMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsignmentMapper
     */
    private $mapper;

    /**
     * @var ConsignmentInterface
     */
    private $consignment;

    /**
     * @var DefaultSenderAddressProviderInterface
     */
    private $defaultSenderAddressProvider;

    public function setUp()
    {
        $this->consignment = $this->createConsignment();
        $this->defaultSenderAddressProvider = $this->createDefaultSenderAddressProvider();

        $this->mapper = new ConsignmentMapper(new ServiceOptionMapper(), $this->defaultSenderAddressProvider);
    }

    /**
     * @test
     */
    public function shouldCreateGlsConsignmentIfNotExist()
    {
        $this->consignment->expects($this->any())->method('getVendorOptions')->willReturn(
            new VendorOptionValueCollection()
        );

        $this->consignment->expects($this->any())->method('getParcels')->willReturn(
            new ArrayCollection()
        );

        $glsConsignment = $this->mapper->mapConsignment($this->consignment);
        $this->assertInstanceOf('Webit\GlsAde\Model\Consignment', $glsConsignment);
    }

    /**
     * @test
     */
    public function shouldUpdatePassedGlsConsignment()
    {
        $this->consignment->expects($this->any())->method('getVendorOptions')->willReturn(
            new VendorOptionValueCollection()
        );

        $this->consignment->expects($this->any())->method('getParcels')->willReturn(
            new ArrayCollection()
        );

        $glsConsignment = new Consignment();
        $mappedGlsConsignment = $this->mapper->mapConsignment($this->consignment, $glsConsignment);
        $this->assertSame($glsConsignment, $mappedGlsConsignment);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConsignmentInterface
     */
    private function createConsignment()
    {
        $consignment = $this->getMock('Webit\Shipment\Consignment\ConsignmentInterface');

        return $consignment;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DefaultSenderAddressProviderInterface
     */
    private function createDefaultSenderAddressProvider()
    {
        $senderProvider = $this->getMock('Webit\Shipment\Address\DefaultSenderAddressProviderInterface');

        return $senderProvider;

    }
}
