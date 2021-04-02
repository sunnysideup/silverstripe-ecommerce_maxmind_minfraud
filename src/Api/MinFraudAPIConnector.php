<?php

namespace Sunnysideup\EcommerceMaxmindMinfraud\Api;

use MaxMind\MinFraud;




use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DataObject;

class MinFraudAPIConnector
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * REQUIRED!
     * @var string
     */
    private static $account_id = '';

    /**
     * REQUIRED!
     * @var string
     */
    private static $license_key = '';

    public function getConnection()
    {
        return new MinFraud(
            Config::inst()->get(MinFraudAPIConnector::class, 'account_id'),
            Config::inst()->get(MinFraudAPIConnector::class, 'license_key')
        );
    }

    /**
     * Creates the `MinFraud` object and builds the request with all the data available in the order
     *
     * @param  \Sunnysideup\Ecommerce\Model\Order $order - the order to be assessed
     *
     * @return MinFraud
     */
    public function buildRequest($order)
    {
        $shippingAddress = $order->ShippingAddress()->exists() ? $order->ShippingAddress() : $order->BillingAddress();

        $mf = $this->getConnection();

        $request = $mf->withAccount(
            [
                'user_id' => $order->MemberID,
                'username_md5' => md5($order->Member()->Email),
            ]
        )->withEvent(
            [
                'transaction_id' => (string) $order->ID,
                'time' => date('c', strtotime($order->Created)), //see: https://stackoverflow.com/questions/22296712/convert-datetime-to-rfc-3339  should we use Created or LastEdited?
                'type' => 'purchase',
            ]
        )->withEmail(
            [
                'address' => $order->Member()->Email,
                'domain' => substr(strrchr($order->Member()->Email, '@'), 1),
            ]
        )->withBilling(
            [
                'first_name' => $order->BillingAddress()->FirstName,
                'last_name' => $order->BillingAddress()->Surname,
                'address' => $order->BillingAddress()->Address,
                'address_2' => $order->BillingAddress()->Address2,
                'city' => $order->BillingAddress()->City,
                //'region'             => '',  // see: https://en.wikipedia.org/wiki/ISO_3166-2
                'country' => $order->BillingAddress()->Country,
                'postal' => $order->BillingAddress()->PostalCode,
                'phone_number' => $order->BillingAddress()->Phone,
            ]
        )->withShipping(
            [
                'first_name' => $shippingAddress->FirstName,
                'last_name' => $shippingAddress->Surname,
                'address' => $shippingAddress->Address,
                'address_2' => $shippingAddress->Address2,
                'city' => $shippingAddress->City,
                //'region'             => '',  // see: https://en.wikipedia.org/wiki/ISO_3166-2
                'country' => $shippingAddress->Country,
                'postal' => $shippingAddress->PostalCode,
                'phone_number' => $shippingAddress->Phone,
            ]
        )->withOrder(
            [
                'amount' => $order->getTotal(),
                'currency' => $order->getTotalAsMoney()->currency,
                //'discount_code'    => '', //do we want to use this?
                'is_gift' => false,
                'has_gift_message' => false,
                'referrer_uri' => Director::absoluteURL('/'),
            ]
        );

        $deviceDetails = OrderStatusLogDeviceDetails::get()->filter(['OrderID' => $order->ID])->first();
        if ($deviceDetails && $deviceDetails->exists()) {
            $request = $request->withDevice(
                [
                    'ip_address' => $deviceDetails->IPAddress,
                    'user_agent' => $deviceDetails->UserAgent,
                    'accept_language' => $deviceDetails->AcceptLanguage,
                    'session_id' => $deviceDetails->SessionID,
                ]
            );
        }

        foreach ($order->Items() as $orderItem) {
            $itemID = $orderItem->BuyableID;
            $product = DataObject::get_by_id($orderItem->BuyableClassName, $orderItem->BuyableID);
            if ($product && $product->exists()) {
                $itemID = $product->InternalItemID;
            }
            $request = $request->withShoppingCartItem(
                [
                    'item_id' => (string) $itemID,
                    'quantity' => (int) $orderItem->Quantity,
                    'price' => $orderItem->CalculatedTotal,
                ]
            );
        }

        return $request;
    }

    /**
     * minFraud Score provides the risk assessment of the transaction with the riskScore and the IP address risk as expressed in the IP Risk Score.
     * Use minFraud Score to assess risk with these data points or use it as part of your own risk modeling.
     *
     * @param  \Sunnysideup\Ecommerce\Model\Order $order - the order to be assessed
     *
     * @return MinFraud\Model\Score minFraud Score model object
     */
    public function getScore($order)
    {
        $request = $this->buildRequest($order);
        return $request->score();
    }

    /**
     * minFraud Insights provides a wide range of data points in addition to the riskScore and the IP Risk Score.
     *
     * Use minFraud Insights to score transactions and to get the data points you need for manual review, advanced rule creation, and internal risk modeling.
     *
     * @param  \Sunnysideup\Ecommerce\Model\Order $order - the order to be assessed
     *
     * @return MinFraud\Model\Insights minFraud Insights model object
     */
    public function getInsights($order)
    {
        $request = $this->buildRequest($order);
        return $request->insights();
    }

    /**
     * minFraud Factors provides detail on the specific components used to determine the riskScore. These subscores provide insight into how we arrived at a riskScore for a given transaction.
     *
     * Such detail on the factors contributing to the riskScore help you better assess the risk of a transaction as part of manual review. Use subscores as parameters in rules to disposition transactions, or as part of internal risk modeling.
     *
     * In addition to the subscores, minFraud Factors includes all the data of minFraud Insights.
     *
     * @param  \Sunnysideup\Ecommerce\Model\Order $order - the order to be assessed
     *
     * @return MinFraud\Model\Factors minFraud Factors model object
     */
    public function getFactors($order)
    {
        $request = $this->buildRequest($order);
        return $request->factors();
    }
}
