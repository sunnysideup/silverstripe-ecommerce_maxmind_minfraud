<?php

class OrderStep_RecordDeviceDetails extends OrderStep implements OrderStepInterface
{
    public function HideFromEveryone()
    {
        return true;
    }

    private static $defaults = array(
        'CustomerCanEdit' => 0,
        'CustomerCanPay' => 0,
        'CustomerCanCancel' => 0,
        'Name' => 'Record Device Details',
        'Code' => 'RECORD_DEVICE_DETAILS',
        'ShowAsInProcessOrder' => 1,
    );

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = 'OrderStatusLog_DeviceDetails';

    /**
     * Can run this step once any items have been submitted.
     * makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
     * should be able to run this function many times to check if the step is ready.
     *
     * @see Order::doNextStatus
     *
     * @param Order object
     *
     * @return bool - true if the current step is ready to be run...
     **/
    public function initStep(Order $order)
    {
        return true;
    }

    /**
    *
    * @param Order object
    *
    * @return bool - true if run correctly.
    **/
    public function doStep(Order $order)
    {
        $className = $this->getRelevantLogEntryClassName();
        if (class_exists($className)) {
            $obj = $className::create();
            if (is_a($obj, Object::getCustomClass('OrderStatusLog'))) {
                $obj->OrderID = $order->ID;
                $obj->Title = $this->Name;
                $obj->write();
            }
        }
        return true;
    }

    /**
     * go to next step if order has been submitted.
     *
     * @param Order $order
     *
     * @return OrderStep | Null	(next step OrderStep)
     **/
    public function nextStep(Order $order)
    {
        return parent::nextStep($order);
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
