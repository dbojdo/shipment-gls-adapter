<?php
/**
 * File GlsConsignmentMapper.php
 * Created at: 2014-12-21 17-19
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Mapper;


use Webit\GlsAde\Model\Consignment;
use Webit\Shipment\Consignment\ConsignmentInterface;

class GlsConsignmentMapper
{

    /**
     * @param Consignment $glsConsignment
     * @param ConsignmentInterface $consignment
     * @return ConsignmentInterface
     */
    public function mapGlsConsignment(Consignment $glsConsignment, ConsignmentInterface $consignment = null)
    {

    }

    /**
     * @param string $status
     * @return string
     */
    public function mapParcelStatus($status)
    {

    }
}