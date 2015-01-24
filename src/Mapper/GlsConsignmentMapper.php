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
        if (empty($statusCode)) {
            return ConsignmentStatusList::STATUS_DISPATCHED;
        }

        switch ($statusCode) {
            case '2011':
            case '2012':
                return ConsignmentStatusList::STATUS_DELIVERED;
                break;
            case '3010':
                return ConsignmentStatusList::STATUS_CANCELED;
                break;
        }

        if ($this->isConcerned($statusCode)) {
            return ConsignmentStatusList::STATUS_CONCERNED;
        }

        return ConsignmentStatusList::STATUS_COLLECTED;
    }

    /**
     * @param string $statusCode
     * @return bool
     */
    private function isConcerned($statusCode)
    {
        return in_array($statusCode,
            array(
                '1024', '2106', '2110', '2112', '2113', '2114', '2115', '2203', '2204', '2205', '2303', '2304', '2404',
                '2900', '2901', '2902', '2911', '2912', '2913', '2914', '2915', '2918', '2933', '2934', '2939', '2950',
                '2951', '2952', '2953', '2954', '2955', '2956', '2957','2958', '3010'
            )
        );
    }
}
