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
use Webit\Shipment\Consignment\ConsignmentStatusList;
use Webit\Shipment\GlsAdapter\Exception\UnsupportedOperationException;

class GlsConsignmentMapper
{

    /**
     * @param Consignment $glsConsignment
     * @param ConsignmentInterface $consignment
     * @return ConsignmentInterface
     */
    public function mapGlsConsignment(Consignment $glsConsignment, ConsignmentInterface $consignment = null)
    {
        throw new UnsupportedOperationException('Synchronization is not supported yet.');
    }

    /**
     * @param string $statusCode
     * @return string
     */
    public function mapParcelStatus($statusCode)
    {
        if ((float)$statusCode < 0) {
            return ConsignmentStatusList::STATUS_DISPATCHED;
        }

        if ((float)$statusCode == 3.0) {
            return ConsignmentStatusList::STATUS_DELIVERED;
        }

        return ConsignmentStatusList::STATUS_COLLECTED;
    }
}
