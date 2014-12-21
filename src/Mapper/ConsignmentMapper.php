<?php
/**
 * File ConsignmentMapper.php
 * Created at: 2014-12-21 15-42
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Mapper;


use Webit\GlsAde\Model\Consignment;
use Webit\Shipment\Consignment\ConsignmentInterface;

class ConsignmentMapper
{
    /**
     * @param ConsignmentInterface $consignment
     * @param Consignment $glsConsignment
     * @return Consignment
     */
    public function mapConsignment(ConsignmentInterface $consignment, Consignment $glsConsignment = null)
    {

    }

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

    /**
     * @param string $service
     * @return string
     */
    public function getServiceOptionName($service)
    {
        return sprintf('service.%s', $service);
    }
}
