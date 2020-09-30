2020-06-26 07:50

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_maxmind_minfraud
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud  --root-dir=/var/www/upgrades/ecommerce_maxmind_minfraud --write -vvv
Writing changes for 6 files
Running upgrades on "/var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud"
[2020-06-26 07:50:13] Applying RenameClasses to EcommerceMaxmindMinfraudTest.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to EcommerceMaxmindMinfraudTest.php...
[2020-06-26 07:50:13] Applying RenameClasses to MinFraudAPIConnector.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to MinFraudAPIConnector.php...
[2020-06-26 07:50:13] Applying RenameClasses to OrderStep_FraudCheck.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to OrderStep_FraudCheck.php...
[2020-06-26 07:50:13] Applying RenameClasses to OrderStep_RecordDeviceDetails.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to OrderStep_RecordDeviceDetails.php...
[2020-06-26 07:50:13] Applying RenameClasses to OrderStatusLog_MinFraudStatusLog.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to OrderStatusLog_MinFraudStatusLog.php...
[2020-06-26 07:50:13] Applying RenameClasses to OrderStatusLog_DeviceDetails.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to OrderStatusLog_DeviceDetails.php...
[2020-06-26 07:50:13] Applying RenameClasses to _config.php...
[2020-06-26 07:50:13] Applying ClassToTraitRule to _config.php...
modified:	tests/EcommerceMaxmindMinfraudTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class EcommerceMaxmindMinfraudTest extends SapphireTest
 {

modified:	src/Api/MinFraudAPIConnector.php
@@ -2,12 +2,19 @@

 namespace Sunnysideup\EcommerceMaxmindMinfraud\Api;

-use ViewableData;
+
 use MinFraud;
-use Config;
-use Director;
-use OrderStatusLog_DeviceDetails;
-use DataObject;
+
+
+
+
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\EcommerceMaxmindMinfraud\Api\MinFraudAPIConnector;
+use SilverStripe\Control\Director;
+use Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLog_DeviceDetails;
+use SilverStripe\ORM\DataObject;
+use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
+



@@ -39,8 +46,8 @@
     public function getConnection()
     {
         $mf = new MinFraud(
-            Config::inst()->get('MinFraudAPIConnector', 'account_id'),
-            Config::inst()->get('MinFraudAPIConnector', 'license_key')
+            Config::inst()->get(MinFraudAPIConnector::class, 'account_id'),
+            Config::inst()->get(MinFraudAPIConnector::class, 'license_key')
         );
         return $mf;
     }

modified:	src/Model/Process/OrderStep_FraudCheck.php
@@ -2,12 +2,21 @@

 namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

-use OrderStep;
-use OrderStepInterface;
-use HeaderField;
-use NumericField;
-use OptionsetField;
-use Order;
+
+
+
+
+
+
+use Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLog_MinFraudStatusLog;
+use SilverStripe\Forms\HeaderField;
+use SilverStripe\Forms\NumericField;
+use SilverStripe\Forms\OptionsetField;
+use Sunnysideup\Ecommerce\Model\Order;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+use Sunnysideup\Ecommerce\Model\Process\OrderStep;
+use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
+


 /**
@@ -37,7 +46,7 @@
      *
      * @var string
      */
-    protected $relevantLogEntryClassName = 'OrderStatusLog_MinFraudStatusLog';
+    protected $relevantLogEntryClassName = OrderStatusLog_MinFraudStatusLog::class;

     public function getCMSFields()
     {
@@ -122,7 +131,7 @@

         if (class_exists($className)) {
             $obj = $className::create();
-            if (is_a($obj, Object::getCustomClass('OrderStatusLog'))) {
+            if (is_a($obj, Object::getCustomClass(OrderStatusLog::class))) {
                 $obj->OrderID = $order->ID;
                 $obj->Title = $this->Name;
                 $obj->ServiceType = $this->MinFraudService;

Warnings for src/Model/Process/OrderStep_FraudCheck.php:
 - src/Model/Process/OrderStep_FraudCheck.php:124 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 124

modified:	src/Model/Process/OrderStep_RecordDeviceDetails.php
@@ -2,9 +2,15 @@

 namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

-use OrderStep;
-use OrderStepInterface;
-use Order;
+
+
+
+use Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLog_DeviceDetails;
+use Sunnysideup\Ecommerce\Model\Order;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+use Sunnysideup\Ecommerce\Model\Process\OrderStep;
+use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
+


 class OrderStep_RecordDeviceDetails extends OrderStep implements OrderStepInterface
@@ -28,7 +34,7 @@
      *
      * @var string
      */
-    protected $relevantLogEntryClassName = 'OrderStatusLog_DeviceDetails';
+    protected $relevantLogEntryClassName = OrderStatusLog_DeviceDetails::class;

     /**
      * Can run this step once any items have been submitted.
@@ -57,7 +63,7 @@
         $className = $this->getRelevantLogEntryClassName();
         if (class_exists($className)) {
             $obj = $className::create();
-            if (is_a($obj, Object::getCustomClass('OrderStatusLog'))) {
+            if (is_a($obj, Object::getCustomClass(OrderStatusLog::class))) {
                 $obj->InternalUseOnly = true;
                 $obj->OrderID = $order->ID;
                 $obj->Title = $this->Name;

Warnings for src/Model/Process/OrderStep_RecordDeviceDetails.php:
 - src/Model/Process/OrderStep_RecordDeviceDetails.php:59 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 59

modified:	src/Model/Process/OrderStatusLog_MinFraudStatusLog.php
@@ -2,12 +2,18 @@

 namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

-use OrderStatusLog;
+
 use EcommerceSecurityLogInterface;
-use Injector;
+
 use Exception;
-use LiteralField;
-use HeaderField;
+
+
+use SilverStripe\Core\Injector\Injector;
+use Sunnysideup\EcommerceMaxmindMinfraud\Api\MinFraudAPIConnector;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Forms\HeaderField;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+



@@ -60,7 +66,7 @@

         $order = $this->Order();
         $this->InternalUseOnly = true;
-        $api = Injector::inst()->get('MinFraudAPIConnector');
+        $api = Injector::inst()->get(MinFraudAPIConnector::class);
         try {
             switch ($this->ServiceType) {
                 case 'Insights':

modified:	src/Model/Process/OrderStatusLog_DeviceDetails.php
@@ -2,8 +2,11 @@

 namespace Sunnysideup\EcommerceMaxmindMinfraud\Model\Process;

-use OrderStatusLog;
-use Controller;
+
+
+use SilverStripe\Control\Controller;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+




Writing changes for 6 files
✔✔✔