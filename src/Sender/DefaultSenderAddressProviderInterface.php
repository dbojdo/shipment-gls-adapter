<?php
/**
 * File DefaultSenderAddressProviderInterface.php
 * Created at: 2015-01-24 04-08
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Sender;


use Webit\GlsAde\Model\SenderAddress;

interface DefaultSenderAddressProviderInterface
{
    /**
     * @return SenderAddress
     */
    public function getDefaultSenderAddress();
}
