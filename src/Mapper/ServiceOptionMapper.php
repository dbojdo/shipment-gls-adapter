<?php
/**
 * File ServiceOptionMapper.php
 * Created at: 2014-12-21 17-21
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Mapper;

class ServiceOptionMapper
{
    /**
     * @param string $optionCode
     * @return string
     */
    public function mapOptionCode($optionCode)
    {
        if (preg_match('/^service\.(.+)/', $optionCode, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    /**
     * @param string $service
     * @return string
     */
    public function mapService($service)
    {
        return sprintf('service.%s', strtolower($service));
    }
}
