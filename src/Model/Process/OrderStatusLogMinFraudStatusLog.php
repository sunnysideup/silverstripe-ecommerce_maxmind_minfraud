<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

use Exception;

use SilverStripe\Core\Injector\Injector;


use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
use Sunnysideup\EcommerceMaxmindMinfraud\Api\MinFraudAPIConnector;
use Sunnysideup\EcommerceSecurity\Interfaces\EcommerceSecurityLogInterface;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLogMinFraudStatusLog extends OrderStatusLog implements EcommerceSecurityLogInterface
{
    private static $table_name = 'OrderStatusLogMinFraudStatusLog';

    private static $db = [
        'ServiceType' => 'Enum("Score,Insights,Factors","Score")',
        'RiskScore' => 'Float',
        'IPRiskScore' => 'Float',
        'DetailedInfo' => 'HTMLText',
    ];

    private static $defaults = [
        'InternalUseOnly' => true,
    ];

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        $order = $this->Order();
        if ($order && $order->exists()) {
            $status = $order->MyStep();
            if ($status && $status->Code === 'FRAUD_CHECK') {
                return parent::canEdit($member);
            }
            return false;
        }
        return parent::canEdit($member);
    }

    /**
     * updates the db values for this status log based on the results of a getScore request
     *
     * @param $response  - minFraud Score model object
     */
    public function updateLogForScoreResponse($response)
    {
        $this->RiskScore = $response->riskScore;
        $this->IPRiskScore = $response->ipAddress->risk;
        $this->DetailedInfo = 'Risk Scores retrieved using the ' . $this->ServiceType . ' service from MinFraud API on ' . date('Y-m-d H:i:s') . '<br>';
        if ($response->warnings) {
            $this->DetailedInfo .= '<h2>Warnings</h2>';
            foreach ($response->warnings as $warning) {
                $this->DetailedInfo .= $warning->warning . '<br><br>';
            }
        }
    }

    /**
     * updates the db values for this status log based on the results of a getInsights request
     *
     * @param  $response  - minFraud Insights model object
     */
    public function updateLogForInsightsResponse($response)
    {
        $this->updateLogForScoreResponse($response);
        $this->DetailedInfo .= '<h2>Further Insights</h2>';
        if (property_exists($response, 'email') && $response->email !== null) {
            $this->DetailedInfo .= '<h5>Email Details</h5>';
            $this->DetailedInfo .= 'Email address first seen by MaxMind on ' . $response->email->firstSeen . '<br>';
            if ($response->email->isFree) {
                $this->DetailedInfo .= 'MaxMind believes that this email is hosted by a free email provider such as Gmail or Yahoo.<br>';
            }
            if ($response->email->isHighRisk) {
                $this->DetailedInfo .= 'MaxMind believes that this email is likely to be used for fraud!<br>';
            }
        }
        if (property_exists($response, 'billingAddress') && $response->billingAddress !== null) {
            $this->DetailedInfo .= '<h5>Billing Address Details</h5>';
            $this->DetailedInfo .= '<strong>Longitude: </strong>' . $response->billingAddress->longitude . '<br>';
            $this->DetailedInfo .= '<strong>Latitude: </strong>' . $response->billingAddress->latitude . '<br>';
            $this->DetailedInfo .= 'Address is located ' . $response->billingAddress->distanceToIpLocation . 'km from the IP Address<br>';
            if ($response->billingAddress->isInIpCountry) {
                $this->DetailedInfo .= 'The address is located within the country of the IP Address<br>';
            } else {
                $this->DetailedInfo .= 'The address is not located within the country of the IP Address<br>';
            }
        }
        if (property_exists($response, 'shippingAddress') && $response->shippingAddress !== null) {
            $this->DetailedInfo .= '<h5>Billing Address Details</h5>';
            $this->DetailedInfo .= '<strong>Longitude: </strong>' . $response->shippingAddress->longitude . '<br>';
            $this->DetailedInfo .= '<strong>Latitude: </strong>' . $response->shippingAddress->latitude . '<br>';
            $this->DetailedInfo .= 'Address is located ' . $response->shippingAddress->distanceToIpLocation . 'km from the IP Address<br>';
            if ($response->shippingAddress->isInIpCountry) {
                $this->DetailedInfo .= 'The address is located within the country of the IP Address<br>';
            } else {
                $this->DetailedInfo .= 'The address is not located within the country of the IP Address<br>';
            }
            $this->DetailedInfo .= 'The Shipping Address is located ' . $response->shippingAddress->distanceToBillingAddress . 'km from the Billing Address.<br>';
            if ($response->shippingAddress->isHighRisk === null) {
                $this->DetailedInfo .= 'The shipping address could not be parsed or was not provided or the IP address could not be geolocated.<br>';
            } elseif ($response->shippingAddress->isHighRisk) {
                $this->DetailedInfo .= 'The shipping is located in the IP country.<br>';
            } else {
                $this->DetailedInfo .= 'The shipping is not located in the IP country.<br>';
            }
        }
        if (property_exists($response, 'ipAddress') && $response->ipAddress !== null) {
            $this->DetailedInfo .= '<h5>IP Address Details</h5>';
            $this->DetailedInfo .= 'This IP Address belongs to a ' . $response->ipAddress->traits->userType . ' user.<br>';
            $this->DetailedInfo .= 'The ISP is ' . $response->ipAddress->traits->organization . ' - ' . $response->ipAddress->traits->isp . '.<br>';
        }
    }

    /**
     * updates the db values for this status log based on the results of a getFactors request
     *
     *  @param  $response  - minFraud Factors model object
     */
    public function updateLogForFactorsResponse($response)
    {
        $this->updateLogForInsightsResponse($response);
    }

    /**
     * if does not return NULL, then a tab will be created in ecom Sec. with the
     * actual OrderStatusLog entry or entries
     *
     * @param \Sunnysideup\Ecommerce\Model\Order $order
     *
     * @return \SilverStripe\Forms\FormField|null
     */
    public function getSecurityLogTable($order)
    {
        $html = null;
        $orderLog = OrderStatusLogMinFraudStatusLog::get()->filter(['OrderID' => $order->ID])->first();
        if ($orderLog && $orderLog->exists()) {
            $html = '<strong>Risk Score: </strong>' . $orderLog->RiskScore . '<br>';
            $html .= '<strong>IP Risk Score: </strong>' . $orderLog->IPRiskScore . '<br>';
            $html .= $orderLog->DetailedInfo . '<br>';
            return LiteralField::create('MinFraudSummary', $html);
        }
        return $html;
    }

    /**
     * the name of the where the SecurityLogTable will be added if getSecurityLogTable returns a formField
     * @return string
     */
    public function getSecurityLogTableTabName()
    {
        return 'MinFraudRiskScore';
    }

    /**
     * returns a summary without header for the Ecom Sec. Main summary Page
     *
     * @param \Sunnysideup\Ecommerce\Model\Order $order
     *
     * @return LiteralField (html)
     */
    public function getSecuritySummary($order)
    {
        $html = 'There is no MinFraud data for this order.';
        $orderLog = OrderStatusLogMinFraudStatusLog::get()->filter(['OrderID' => $order->ID])->first();
        if ($orderLog && $orderLog->exists()) {
            $html = '<strong>Risk Score: </strong>' . $orderLog->RiskScore . '<br>';
            $html .= '<strong>IP Risk Score: </strong>' . $orderLog->IPRiskScore . '<br>';
        }
        return LiteralField::create('MinFraudSummary', $html);
    }

    /**
     * returns the header to be used in TAB and in Summary Page (on the Ecom Security Module)
     * @return HeaderField
     */
    public function getSecurityHeader()
    {
        return HeaderField::create('MinFraudHeader', 'Min Fraud Risk Details');
    }

    /**
     * adding a sequential order number.
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $order = $this->Order();
        $this->InternalUseOnly = true;
        $api = Injector::inst()->get(MinFraudAPIConnector::class);
        try {
            switch ($this->ServiceType) {
                case 'Insights':
                    $insightsResponse = $api->getInsights($order);
                    $this->updateLogForInsightsResponse($insightsResponse);
                    break;
                case 'Factors':
                    $factorsResponse = $api->getFactors($order);
                    $this->updateLogForFactorsResponse($factorsResponse);
                    break;
                default:
                    $scoreResponse = $api->getScore($order);
                    $this->updateLogForScoreResponse($scoreResponse);
            }
        } catch (Exception $exception) {
            $this->DetailedInfo = $exception->getMessage();
        }
    }
}
