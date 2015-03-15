<?php
/**
 * File ParcelMapper.php
 * Created at: 2015-03-15 07-00
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

use Webit\GlsAde\Model\Consignment;
use Webit\GlsAde\Model\Parcel;

class ParcelMapper
{
    /**
     * @param Consignment $glsConsignment
     * @param \Webit\Shipment\Consignment\Consignment $consignment
     * @throws MappingException
     */
    public function updateParcelsNumbers(Consignment $glsConsignment, \Webit\Shipment\Consignment\Consignment $consignment)
    {
        /** @var Parcel $glsParcel */
        foreach ($glsConsignment->getParcels() as $glsParcel) {
            $parcel = $consignment->findParcel($glsParcel->getReference());
            if (! $parcel) {
                throw new MappingException(
                    sprintf(
                        'Can not find Parcel for GlsParcel (reference: "%s", number: "%s") in given Consignment (ID: "%s")',
                        $glsParcel->getReference(),
                        $glsParcel->getNumber(),
                        $consignment->getId()
                    )
                );
            }

            $parcel->setNumber($glsParcel->getNumber());
        }
    }
}
