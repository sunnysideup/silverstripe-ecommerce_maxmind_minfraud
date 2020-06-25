2020-06-26 07:50

# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_maxmind_minfraud
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src  --root-dir=/var/www/upgrades/ecommerce_maxmind_minfraud --write -vvv
Array
(
    [0] => Running post-upgrade on "/var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src"
    [1] => [2020-06-26 07:50:35] Applying ApiChangeWarningsRule to MinFraudAPIConnector.php...
    [2] => PHP Warning:  Declaration of Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLog_MinFraudStatusLog::canCreate($member = NULL) should be compatible with Sunnysideup\Ecommerce\Model\Process\OrderStatusLog::canCreate($member = NULL, $context = Array) in /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src/Model/Process/OrderStatusLog_MinFraudStatusLog.php on line 26
    [3] => PHP Warning:  Declaration of Sunnysideup\EcommerceMaxmindMinfraud\Model\Process\OrderStatusLog_MinFraudStatusLog::canEdit($member = NULL) should be compatible with Sunnysideup\Ecommerce\Model\Process\OrderStatusLog::canEdit($member = NULL, $context = Array) in /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src/Model/Process/OrderStatusLog_MinFraudStatusLog.php on line 26
    [4] => 
    [5] => In Broker.php line 215:
    [6] => 
    [7] =>   [PHPStan\Broker\ClassAutoloadingException]
    [8] =>   Class EcommerceSecurityLogInterface not found and could not be autoloaded.
    [9] => 
    [10] => 
    [11] => Exception trace:
    [12] =>   at /var/www/upgrader/vendor/phpstan/phpstan/src/Broker/Broker.php:215
    [13] =>  PHPStan\Broker\Broker->PHPStan\Broker\{closure}() at n/a:n/a
    [14] =>  spl_autoload_call() at /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src/Model/Process/OrderStatusLog_MinFraudStatusLog.php:26
    [15] =>  require_once() at /var/www/upgrader/vendor/silverstripe/upgrader/src/Autoload/CollectionAutoloader.php:159
    [16] =>  SilverStripe\Upgrader\Autoload\CollectionAutoloader->loadItem() at /var/www/upgrader/vendor/silverstripe/upgrader/src/Autoload/CollectionAutoloader.php:100
    [17] =>  SilverStripe\Upgrader\Autoload\CollectionAutoloader->loadClass() at n/a:n/a
    [18] =>  spl_autoload_call() at n/a:n/a
    [19] =>  class_exists() at /var/www/upgrader/vendor/phpstan/phpstan/src/Broker/Broker.php:220
    [20] =>  PHPStan\Broker\Broker->hasClass() at /var/www/upgrader/vendor/phpstan/phpstan/src/Rules/ClassCaseSensitivityCheck.php:27
    [21] =>  PHPStan\Rules\ClassCaseSensitivityCheck->checkClassNames() at /var/www/upgrader/vendor/phpstan/phpstan/src/Rules/Namespaces/ExistingNamesInUseRule.php:115
    [22] =>  PHPStan\Rules\Namespaces\ExistingNamesInUseRule->checkClasses() at /var/www/upgrader/vendor/phpstan/phpstan/src/Rules/Namespaces/ExistingNamesInUseRule.php:62
    [23] =>  PHPStan\Rules\Namespaces\ExistingNamesInUseRule->processNode() at /var/www/upgrader/vendor/silverstripe/upgrader/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php:80
    [24] =>  SilverStripe\Upgrader\UpgradeRule\PHP\Visitor\PHPStanScopeVisitor->SilverStripe\Upgrader\UpgradeRule\PHP\Visitor\{closure}() at /var/www/upgrader/vendor/phpstan/phpstan/src/Analyser/NodeScopeResolver.php:316
    [25] =>  PHPStan\Analyser\NodeScopeResolver->processNode() at /var/www/upgrader/vendor/phpstan/phpstan/src/Analyser/NodeScopeResolver.php:176
    [26] =>  PHPStan\Analyser\NodeScopeResolver->processNodes() at /var/www/upgrader/vendor/phpstan/phpstan/src/Analyser/NodeScopeResolver.php:699
    [27] =>  PHPStan\Analyser\NodeScopeResolver->processNode() at /var/www/upgrader/vendor/phpstan/phpstan/src/Analyser/NodeScopeResolver.php:176
    [28] =>  PHPStan\Analyser\NodeScopeResolver->processNodes() at /var/www/upgrader/vendor/silverstripe/upgrader/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php:82
    [29] =>  SilverStripe\Upgrader\UpgradeRule\PHP\Visitor\PHPStanScopeVisitor->enterNode() at /var/www/upgrader/vendor/nikic/php-parser/lib/PhpParser/NodeTraverser.php:159
    [30] =>  PhpParser\NodeTraverser->traverseArray() at /var/www/upgrader/vendor/nikic/php-parser/lib/PhpParser/NodeTraverser.php:85
    [31] =>  PhpParser\NodeTraverser->traverse() at /var/www/upgrader/vendor/silverstripe/upgrader/src/UpgradeRule/PHP/PHPUpgradeRule.php:28
    [32] =>  SilverStripe\Upgrader\UpgradeRule\PHP\PHPUpgradeRule->transformWithVisitors() at /var/www/upgrader/vendor/silverstripe/upgrader/src/UpgradeRule/PHP/ApiChangeWarningsRule.php:88
    [33] =>  SilverStripe\Upgrader\UpgradeRule\PHP\ApiChangeWarningsRule->mutateSourceWithVisitors() at /var/www/upgrader/vendor/silverstripe/upgrader/src/UpgradeRule/PHP/ApiChangeWarningsRule.php:60
    [34] =>  SilverStripe\Upgrader\UpgradeRule\PHP\ApiChangeWarningsRule->upgradeFile() at /var/www/upgrader/vendor/silverstripe/upgrader/src/Upgrader.php:61
    [35] =>  SilverStripe\Upgrader\Upgrader->upgrade() at /var/www/upgrader/vendor/silverstripe/upgrader/src/Console/InspectCommand.php:88
    [36] =>  SilverStripe\Upgrader\Console\InspectCommand->execute() at /var/www/upgrader/vendor/symfony/console/Command/Command.php:255
    [37] =>  Symfony\Component\Console\Command\Command->run() at /var/www/upgrader/vendor/symfony/console/Application.php:1000
    [38] =>  Symfony\Component\Console\Application->doRunCommand() at /var/www/upgrader/vendor/symfony/console/Application.php:271
    [39] =>  Symfony\Component\Console\Application->doRun() at /var/www/upgrader/vendor/symfony/console/Application.php:147
    [40] =>  Symfony\Component\Console\Application->run() at /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code:55
    [41] => 
    [42] => inspect [-d|--root-dir ROOT-DIR] [-w|--write] [--skip-visibility] [--] <path>
    [43] => 
)


------------------------------------------------------------------------
To continue, please use the following parameter: startFrom=InspectAPIChanges-1
e.g. php runme.php startFrom=InspectAPIChanges-1
------------------------------------------------------------------------
            
# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_maxmind_minfraud
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src  --root-dir=/var/www/upgrades/ecommerce_maxmind_minfraud --write -vvv
Writing changes for 0 files
Running post-upgrade on "/var/www/upgrades/ecommerce_maxmind_minfraud/ecommerce_maxmind_minfraud/src"
[2020-06-26 10:51:37] Applying ApiChangeWarningsRule to MinFraudAPIConnector.php...
[2020-06-26 10:51:37] Applying UpdateVisibilityRule to MinFraudAPIConnector.php...
[2020-06-26 10:51:37] Applying ApiChangeWarningsRule to OrderStep_FraudCheck.php...
[2020-06-26 10:51:37] Applying UpdateVisibilityRule to OrderStep_FraudCheck.php...
[2020-06-26 10:51:37] Applying ApiChangeWarningsRule to OrderStep_RecordDeviceDetails.php...
[2020-06-26 10:51:37] Applying UpdateVisibilityRule to OrderStep_RecordDeviceDetails.php...
[2020-06-26 10:51:37] Applying ApiChangeWarningsRule to OrderStatusLog_MinFraudStatusLog.php...
[2020-06-26 10:51:38] Applying UpdateVisibilityRule to OrderStatusLog_MinFraudStatusLog.php...
[2020-06-26 10:51:38] Applying ApiChangeWarningsRule to OrderStatusLog_DeviceDetails.php...
[2020-06-26 10:51:38] Applying UpdateVisibilityRule to OrderStatusLog_DeviceDetails.php...
unchanged:	Model/Process/OrderStep_FraudCheck.php
Warnings for Model/Process/OrderStep_FraudCheck.php:
 - Model/Process/OrderStep_FraudCheck.php:54 SilverStripe\Forms\HeaderField: Requires an explicit $name constructor argument (in addition to $title)
unchanged:	Model/Process/OrderStatusLog_MinFraudStatusLog.php
Warnings for Model/Process/OrderStatusLog_MinFraudStatusLog.php:
 - Model/Process/OrderStatusLog_MinFraudStatusLog.php:227 SilverStripe\Forms\HeaderField: Requires an explicit $name constructor argument (in addition to $title)
Writing changes for 0 files
✔✔✔