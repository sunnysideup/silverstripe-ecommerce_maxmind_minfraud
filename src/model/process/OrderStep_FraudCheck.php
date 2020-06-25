<?php

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 **/
class OrderStep_FraudCheck extends OrderStep implements OrderStepInterface
{
    public static $db = [
        'MinOrderValue' => 'Int',
        'MinFraudService' =>  'Enum("Score,Insights","Score")'
    ];

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Fraud Check for Order',
        'Code' => 'FRAUD_CHECK',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1
    ];

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = 'OrderStatusLog_MinFraudStatusLog';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            HeaderField::create('MinFraudHeader', 'MaxMind Min Fraud Settings')
        );

        $fields->addFieldToTab(
            'Root.Main',

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: NumericField::create (case sensitive)
  * NEW: NumericField::create (COMPLEX)
  * EXP: check the number of decimals required and add as ->setScale(2)
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            NumericField::create('MinOrderValue', 'Minimum Order Value', 0)->setRightTitle('The Risk Score will only be retrieved for orders with a total greater than the value in this field.')
        );

        $fields->addFieldToTab(
            'Root.Main',
            OptionsetField::create(
                'MinFraudService',
                'Min Fraud Service',
                $this->dbObject('MinFraudService')->enumValues()
            )->setRightTitle(
                '
                The MinFraud service that will be used to check if an order potentially fraudulent.<br>
                Compare the <a href="https://www.maxmind.com/en/minfraud-service-comparison" target="_blank">services</a> to decide which one you should use.
                '
            )
        );

        $fields->removeByName('DeferHeader');
        $fields->removeByName('DeferTimeInSeconds');
        $fields->removeByName('DeferFromSubmitTime');

        return $fields;
    }

    /**
     *initStep:
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
     *doStep:
     * should only be able to run this function once
     * (init stops you from running it twice - in theory....)
     * runs the actual step.
     *
     * @see Order::doNextStatus
     *
     * @param Order object
     *
     * @return bool - true if run correctly.
     **/
    public function doStep(Order $order)
    {
        if ($order->getTotal() < $this->MinOrderValue) {
            return true;
        }

        $className = $this->getRelevantLogEntryClassName();

        if (class_exists($className)) {
            $obj = $className::create();
            if (is_a($obj, Object::getCustomClass('OrderStatusLog'))) {
                $obj->OrderID = $order->ID;
                $obj->Title = $this->Name;
                $obj->ServiceType = $this->MinFraudService;
                $obj->write();
            }
        }

        return true;
    }

    /**
     *nextStep:
     * returns the next step (after it checks if everything is in place for the next step to run...).
     *
     * @see Order::doNextStatus
     *
     * @param Order $order
     *
     * @return OrderStep | Null (next step OrderStep object)
     **/
    public function nextStep(Order $order)
    {
        return parent::nextStep($order);
    }

    /**
     * For some ordersteps this returns true...
     *
     * @return bool
     **/
    protected function hasCustomerMessage()
    {
        return false;
    }

    /**
     * Explains the current order step.
     *
     * @return string
     */
    protected function myDescription()
    {
        return 'Checks for possible fraudulent orders using the minFraud API provided by MaxMind';
    }
}

