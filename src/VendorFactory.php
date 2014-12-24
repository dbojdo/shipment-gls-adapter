<?php
/**
 * VendorFactory.php
 *
 * @author dbojdo - Daniel Bojdo <daniel.bojdo@web-it.eu>
 * Created on Dec 24, 2014, 10:23
 */

namespace Webit\Shipment\GlsAdapter;

use Webit\GlsAde\Api\ServiceApi;
use Webit\GlsAde\Model\ConsignmentLabelModes;
use Webit\GlsAde\Model\PickupReceiptModes;
use Webit\Shipment\GlsAdapter\Mapper\ServiceOptionMapper;
use Webit\Shipment\Vendor\VendorInterface;
use Webit\Shipment\Vendor\VendorOption;

/**
 * Class VendorFactory
 * @package Webit\Shipment\GlsAdapter
 */
class VendorFactory
{
    /**
     * @var string
     */
    private $vendorClass;

    /**
     * @var ServiceOptionMapper
     */
    private $serviceOptionMapper;

    /**
     * @var ServiceApi
     */
    private $serviceApi;

    /**
     * @param $vendorClass
     * @param ServiceOptionMapper $serviceOptionMapper
     * @param ServiceApi $serviceApi
     */
    public function __construct($vendorClass, ServiceOptionMapper $serviceOptionMapper, ServiceApi $serviceApi)
    {
        $this->vendorClass = $vendorClass;
        $this->serviceOptionMapper = $serviceOptionMapper;
        $this->serviceApi = $serviceApi;
    }


    /**
     * @return VendorInterface
     */
    public function createVendor()
    {
        $refClass = new \ReflectionClass($this->vendorClass);

        /** @var VendorInterface $vendor */
        $vendor = $refClass->newInstanceArgs(array(ShipmentGlsAdapter::VENDOR_CODE));
        $vendor->setName('GLS');
        $vendor->setActive(true);
        $vendor->setDescription('GLS Europe');

        foreach (ConsignmentLabelModes::getLabelModes() as $mode) {
            $vendor->getLabelPrintModes()->add($mode);
        }

        foreach (PickupReceiptModes::getLabelModes() as $mode) {
            $vendor->getDispatchConfirmationPrintModes()->add($mode);
        }

        $list = $this->serviceApi->getAllowedServices();
        foreach ($list as $service => $allowed) {
            $optionName = $this->serviceOptionMapper->mapService($service);
            $option = new VendorOption();
            $option->setCode($optionName);
            $option->setName(sprintf('Service %s', $service));

            if ($service != 'cod_amount') {
                $option->addAllowedValue(true);
                $option->addAllowedValue(false);
            }

            $vendor->getConsignmentOptions()->addOption($option);
            $vendor->getParcelOptions()->addOption($option);
        }

        return $vendor;
    }
}
