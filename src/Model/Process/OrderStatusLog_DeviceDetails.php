<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;



use SilverStripe\Control\Controller;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;




/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLog_DeviceDetails extends OrderStatusLog
{


        /**
         * standard SS variable.
         *
         * @var string
         */
    private static $singular_name = 'Device Details Record';
    public function i18n_singular_name()
    {
        return _t('OrderStatusLog_DeviceDetails.SINGULAR_NAME', 'Device Details Record');
    }

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Device Details Record';
    public function i18n_plural_name()
    {
        return _t('OrderStatusLog_DeviceDetails.PLURAL NAME', 'Device Details Record');
    }

    private static $table_name = 'OrderStatusLog_DeviceDetails';

    private static $db = array(
        'IPAddress' => 'Varchar(255)',
        'UserAgent' => 'Varchar(255)',
        'AcceptLanguage' => 'Varchar(255)',
        'SessionAge' => 'Decimal',
        'SessionID' => 'Varchar(255)'
    );


    private static $defaults = array(
        'InternalUseOnly' => true
    );

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        $order = $this->Order();
        if ($order && $order->exists()) {
            $status = $order->MyStep();
            if ($status && $status->Code == 'RECORD_DEVICE_DETAILS') {
                return parent::canEdit($member);
            } else {
                return false;
            }
        } else {
            return parent::canEdit($member);
        }
    }

    /**
     * adding a sequential order number.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->InternalUseOnly = true;
        if (! $this->exists()) {
            $order = $this->Order();
            $this->SessionID = $order->SessionID;

            $sessionTime = @fileatime(session_save_path()."/sess_".session_id());
            if ($sessionTime) {
                $sessionTime = time() - $sessionTime;
                $this->SessionAge = $sessionTime;
            }

            if (Controller::has_curr()) {
                $this->IPAddress = Controller::curr()->getRequest()->getIP();
            }
            
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

