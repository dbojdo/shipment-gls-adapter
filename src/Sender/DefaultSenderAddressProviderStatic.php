<?php
/**
 * File DefaultSenderAddressProviderStatic.php
 * Created at: 2015-01-24 04-12
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\GlsAdapter\Sender;

use Webit\GlsAde\Model\SenderAddress;

class DefaultSenderAddressProviderStatic implements DefaultSenderAddressProviderInterface
{
    /**
     * @var SenderAddress
     */
    private $senderAddress;

    public function __construct(SenderAddress $senderAddress)
    {
        $this->senderAddress = $senderAddress;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultSenderAddress()
    {
        return $this->senderAddress;
    }
}