# Ecommerce Maxmind Minfraud: Upgrade to Silverstripe CMS 6

This document outlines the necessary steps and breaking changes required to upgrade the `sunnysideup/ecommerce_maxmind_minfraud` module to be compatible with Silverstripe CMS 6.

## 🚨 CRITICAL REVIEW REQUIRED / RISKY

**Incomplete Dependency Update:**
- The `yet-to-update` section in `composer.json` indicates that `sunnysideup/ecommerce_security` has not been updated as there is no compatible stable release.
- **You must manually resolve this dependency. This may involve finding a compatible version, replacing the module, or waiting for an update.**

## ⚠️ BREAKING CHANGES

### Project Requirements

- **Silverstripe CMS Version:** Your project must now use `silverstripe/recipe-cms: ^6.0`.
- **Ecommerce Module Version:** The core ecommerce module dependency has been updated to `sunnysideup/ecommerce: ^33.0`.

### Configuration

- **Database Administration Class:** The deprecated `SilverStripe\ORM\DatabaseAdmin` class has been replaced. Update your `database.legacy.yml` or related configurations to use `SilverStripe\Dev\DbBuild` for class name remapping.

### API Updates

- **Data-Object Retrieval:** The deprecated `DataObject::get_by_id()` method has been removed. Update your code to use the new syntax: `MyClassName::get()->byID($id)`. The upgrade uses `$orderItem->BuyableClassName::get()->setUseCache(true)->byID($orderItem->BuyableID)` to retrieve the buyable product.
- **Controller Access:** The static `Controller::has_curr()` method is obsolete. To get the current controller and IP address, use `Controller::curr() instanceof Controller` and `Controller::curr()->getRequest()->getIP()`.

## Other Changes

### Code Structure
- **PHP `Override` Attribute:** The module now uses the native PHP 8 `#[Override]` attribute to mark methods that override parent implementations. This improves code clarity and maintainability.
- **Removed `DataObject` Import:** The unused `use SilverStripe\ORM\DataObject;` statement was removed from `MinFraudAPIConnector.php`.
