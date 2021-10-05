<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

use Sunnysideup\Ecommerce\Config\EcommerceConfigClassNames;
use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

use SilverStripe\Forms\LiteralField;

class OrderStepRecordDeviceDetails extends OrderStep implements OrderStepInterface
{
    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLogDeviceDetails::class;

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanPay' => 0,
        'CustomerCanCancel' => 0,
        'Name' => 'Record Device Details',
        'Code' => 'RECORD_DEVICE_DETAILS',
        'ShowAsInProcessOrder' => 1,
    ];

    public function HideFromEveryone(): bool
    {
        return true;
    }

    /**
     * Can run this step once any items have been submitted.
     * makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
     * should be able to run this function many times to check if the step is ready.
     *
     * @see Order::doNextStatus
     *
     * @return bool - true if the current step is ready to be run...
     */
    public function initStep(Order $order): bool
    {
        return true;
    }


    /**
     * @return bool - true if run correctly
     */
    public function doStep(Order $order): bool
    {
        $className = $this->getRelevantLogEntryClassName();
        if (class_exists($className)) {
            $obj = $className::create();
            if (is_a($obj, EcommerceConfigClassNames::getName(OrderStatusLog::class))) {
                $obj->InternalUseOnly = true;
                $obj->OrderID = $order->ID;
                $obj->Title = $this->Name;
                $obj->write();
            }
        }

        return true;
    }

    /**
     * Explains the current order step.
     *
     * @return string
     */
    protected function myDescription()
    {
        return _t('OrderStep.RECORDDEVICEDETAILS_DESCRIPTION', 'Records the device details of the customer placing the order.');
    }
}
