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
     * @param string $statusCode
     * @return string
     */
    public function mapParcelStatus($statusCode)
    {

        switch ($statusCode) {
            case '2011':
            case '2012':
            case '2900':
                return ConsignmentStatusList::STATUS_DELIVERED;
                break;
            case '3010':
                return ConsignmentStatusList::STATUS_CANCELED;
                break;
        }

        if ($this->isConcerned($statusCode)) {
            return ConsignmentStatusList::STATUS_CONCERNED;
        }

        return ConsignmentStatusList::STATUS_DISPATCHED;
    }

    /**
     * @param $statusCode
     * @return bool
     */
    private function isConcerned($statusCode)
    {
        // TODO: implement
        return false;
    }
}