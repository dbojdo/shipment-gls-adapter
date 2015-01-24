<?php
/**
 * GlsConsignmentMapperTest.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 09:58
 */

namespace Webit\Shipment\GlsAdapter\Tests\Mapper;

use Webit\Shipment\Consignment\ConsignmentStatusList;
use Webit\Shipment\GlsAdapter\Mapper\GlsConsignmentMapper;

/**
 * Class GlsConsignmentMapperTest
 * @package Webit\Shipment\GlsAdapter\Tests\Mapper
 */
class GlsConsignmentMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getStatusMap
     * @param $glsStatus
     * @param $expectedStatus
     */
    public function shouldMapParcelStatus($glsStatus, $expectedStatus)
    {
        $mapper = new GlsConsignmentMapper();
        $status = $mapper->mapParcelStatus($glsStatus);

        $this->assertEquals($expectedStatus, $status);
    }

    public function getStatusMap()
    {
        return array(
            array('2011', ConsignmentStatusList::STATUS_DELIVERED),
            array('2012', ConsignmentStatusList::STATUS_DELIVERED),
            array('1024', ConsignmentStatusList::STATUS_CONCERNED),
            array('2900', ConsignmentStatusList::STATUS_CONCERNED),
            array('2918', ConsignmentStatusList::STATUS_CONCERNED),
            array('', ConsignmentStatusList::STATUS_DISPATCHED),
            array('2258', ConsignmentStatusList::STATUS_COLLECTED)
        );
    }
}
