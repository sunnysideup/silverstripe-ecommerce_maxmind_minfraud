<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\OptionsetField;
use Sunnysideup\Ecommerce\Config\EcommerceConfigClassNames;
use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 **/
class OrderStepFraudCheck extends OrderStep implements OrderStepInterface
{
    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLogMinFraudStatusLog::class;

    private static $table_name = 'OrderStepFraudCheck';

    private static $db = [
        'MinOrderValue' => 'Int',
        'MinFraudService' => 'Enum("Score,Insights","Score")',
    ];

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Fraud Check for Order',
        'Code' => 'FRAUD_CHECK',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1,
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            HeaderField::create('MinFraudHeader', 'MaxMind Min Fraud Settings')
        );

        $fields->addFieldToTab(
            'Root.Main',
            NumericField::create('MinOrderValue', 'Minimum Order Value', 0)->setScale(2)->setRightTitle('The Risk Score will only be retrieved for orders with a total greater than the value in this field.')
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
     * @param Order $order object
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
     * @param Order $order object
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
            if (is_a($obj, EcommerceConfigClassNames::getName(OrderStatusLog::class))) {
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
     * @return OrderStep|null (next step OrderStep object)
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
