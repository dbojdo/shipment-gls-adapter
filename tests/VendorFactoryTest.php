<?php
/**
 * VendorFactoryTest.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:30
 */

namespace Webit\Shipment\GlsAdapter\Tests;

use Webit\GlsAde\Api\ServiceApi;
use Webit\GlsAde\Model\ServicesBool;
use Webit\Shipment\GlsAdapter\Mapper\ServiceOptionMapper;
use Webit\Shipment\GlsAdapter\VendorFactory;
use Webit\Shipment\Vendor\VendorInterface;

/**
 * Class VendorFactoryTest
 * @package Webit\Shipment\GlsAdapter\Tests
 */
class VendorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $vendorClass;

    /**
     * @var VendorInterface
     */
    private $vendor;

    public function setUp()
    {
        $this->vendorClass = 'Webit\Shipment\Vendor\Vendor';
        $mapper = $this->createServiceOptionMapper();
        $serviceApi = $this->createServiceApi();


        $factory = new VendorFactory($this->vendorClass, $mapper, $serviceApi);
        $this->vendor = $factory->createVendor();
    }

    /**
     * @test
     */
    public function shouldBeInstanceOfVendor()
    {
        $this->assertInstanceOf('Webit\Shipment\Vendor\VendorInterface', $this->vendor);
        $this->assertInstanceOf($this->vendorClass, $this->vendor);
    }

    /**
     * @test
     */
    public function shouldHaveNameAndDescription()
    {
        $this->assertNotEmpty($this->vendor->getName());
        $this->assertNotEmpty($this->vendor->getDescription());
    }

    /**
     * @test
     */
    public function shouldBeActive()
    {
        $this->assertTrue($this->vendor->isActive());
    }

    /**
     * @test
     */
    public function shouldContainLabelPrintModes()
    {
        $labelPrintModes = $this->vendor->getLabelPrintModes();
        $this->assertGreaterThan(0, $labelPrintModes->count());
    }

    /**
     * @test
     */
    public function shouldContainDispatchConfirmationPrintModes()
    {
        $dispatchConfirmationPrintModes = $this->vendor->getDispatchConfirmationPrintModes();
        $this->assertGreaterThan(0, $dispatchConfirmationPrintModes->count());
    }

    /**
     * @test
     */
    public function shouldContainConsignmentAndParcelOption()
    {
        $options = $this->vendor->getConsignmentOptions();
        $this->assertGreaterThan(0, count($options));

        $options = $this->vendor->getParcelOptions();
        $this->assertGreaterThan(0, count($options));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceOptionMapper
     */
    private function createServiceOptionMapper()
    {
        $mapper = $this->getMock('Webit\Shipment\GlsAdapter\Mapper\ServiceOptionMapper');
        $mapper->expects($this->any())->method('mapService')->willReturnCallback(function($serviceName) {
            return 'service.'.$serviceName;
        });

        return $mapper;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceApi
     */
    private function createServiceApi()
    {
        $mapper = $this->getMockBuilder('Webit\GlsAde\Api\ServiceApi')->disableOriginalConstructor()->getMock();

        $servicesBool = new ServicesBool();
        $mapper->expects($this->any())->method('getAllowedServices')->willReturn($servicesBool);
        return $mapper;
    }
}
