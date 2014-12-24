<?php
/**
 * ServiceOptionMapper.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 09:58
 */

namespace Webit\Shipment\GlsAdapter\Tests\Mapper;

use Webit\Shipment\GlsAdapter\Mapper\ServiceOptionMapper;

/**
 * Class ServiceOptionMapperTest
 * @package Webit\Shipment\GlsAdapter\Tests\Mapper
 */
class ServiceOptionMapperTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @test
     * @dataProvider getOptionsCodes
     * @param string $optionCode
     * @param string $expectedService
     */
    public function shouldMapOptionCodeToAdeServiceName($optionCode, $expectedService)
    {
        $mapper = new ServiceOptionMapper();
        $service = $mapper->mapOptionCode($optionCode);
        $this->assertEquals($expectedService, $service);
    }

    public function getOptionsCodes()
    {
        return array(
            array('service.srs', 'srs'),
            array('service.srs_2', 'srs_2'),
            array('nonervice.srs', null),
        );
    }

    /**
     * @test
     * @dataProvider getServices
     * @param $service
     * @param $expectedOption
     */
    public function shouldMapAdeServiceNameToOptionCode($service, $expectedOption)
    {
        $mapper = new ServiceOptionMapper();
        $option = $mapper->mapService($service);
        $this->assertEquals($expectedOption, $option);
    }

    public function getServices()
    {
        return array(
            array('srs', 'service.srs'),
            array('exc', 'service.exc'),
        );
    }
}
 