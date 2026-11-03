<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use Sunnysideup\Ecommerce\Model\Address\EcommerceCountry;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;

/**
 * Class \Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLogDeviceDetails
 *
 * @property string $IPAddress
 * @property string $UserAgent
 * @property string $AcceptLanguage
 * @property float $SessionAge
 * @property string $SessionID
 */
class OrderStatusLogDeviceDetails extends OrderStatusLog
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Device Details Record';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Device Details Record';

    private static $table_name = 'OrderStatusLogDeviceDetails';

    private static $db = [
        'IPAddress' => 'Varchar(255)',
        'UserAgent' => 'Varchar(255)',
        'AcceptLanguage' => 'Varchar(255)',
        'SessionAge' => 'Decimal',
        'SessionID' => 'Varchar(255)',
    ];

    private static $defaults = [
        'InternalUseOnly' => true,
    ];

    /**
     * The order session ID is a secret (it grants access to the order),
     * so it must never be stored in a log or sent to a third party.
     * This returns a stable, one-way identifier instead.
     */
    public static function session_id_to_safe_id(?string $sessionId, $orderId = 0): string
    {
        $sessionId = (string) $sessionId;
        if ('' === $sessionId) {
            return '';
        }

        $key = (string) Environment::getEnv('SS_DEFAULT_ADMIN_PASSWORD');
        $key .= '|' . self::class . '|' . (int) $orderId;

        return substr(hash_hmac('sha256', $sessionId, $key), 0, 32);
    }

    public function i18n_singular_name()
    {
        return _t('OrderStatusLogDeviceDetails.SINGULAR_NAME', 'Device Details Record');
    }

    public function i18n_plural_name()
    {
        return _t('OrderStatusLogDeviceDetails.PLURAL NAME', 'Device Details Record');
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        $order = $this->getOrderCached();
        if ($order && $order->exists()) {
            $status = $order->MyStep();
            if ($status && 'RECORD_DEVICE_DETAILS' === $status->Code) {
                return parent::canEdit($member);
            }

            return false;
        }

        return parent::canEdit($member);
    }

    /**
     * adding a sequential order number.
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->InternalUseOnly = true;
        if (! $this->exists()) {
            $order = $this->getOrderCached();
            $this->SessionID = self::session_id_to_safe_id($order->SessionID, $order->ID);

            $sessionTime = @fileatime(session_save_path() . '/sess_' . session_id());
            if ($sessionTime) {
                $sessionTime = time() - $sessionTime;
                $this->SessionAge = $sessionTime;
            }

            $ip = '';
            if (class_exists(EcommerceCountry::class)) {
                $ip = EcommerceCountry::get_ip();
            }
            if ('' === $ip && Controller::has_curr()) {
                $ip = (string) Controller::curr()->getRequest()->getIP();
            }
            $this->IPAddress = $ip;

            $session = Controller::curr()->getRequest()->getSession()->getAll();
            if (isset($session['HTTP_USER_AGENT'])) {
                $this->UserAgent = $session['HTTP_USER_AGENT'];
            }

            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $this->AcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            }
        }
    }
}
