# Architecture & Extension Guide — `nh-core`

This guide explains how to add new features, modules, or services to `nh-core` following our **Senior PSR-4 Architecture**.

---

## 1. How to Add a New Module

To create a new functional domain module (e.g. `Modules/GiftCards/`):

1. **Create Directory**: `src/Modules/GiftCards/`
2. **Create Classes**:
   - `GiftCardsRepository.php` (DB calls using `$wpdb`)
   - `GiftCardsService.php` (Business logic)
   - `GiftCardsHooks.php` (WordPress `add_action` / `add_filter` bindings implementing `HookSubscriberInterface`)
   - `GiftCardsServiceProvider.php` (Implementing `ServiceProviderInterface`)
3. **Register Service Provider**:
   In `src/Plugin.php`, add your provider class to `$providers`:
   ```php
   private function register_services(): void {
       $providers = [
           Modules\Preferences\PreferencesServiceProvider::class,
           Modules\GiftCards\GiftCardsServiceProvider::class, // <-- New Module
       ];
       ...
   }
   ```

---

## 2. Example Module Blueprint

### `GiftCardsServiceProvider.php`
```php
<?php
namespace NormaHana\Core\Modules\GiftCards;

use NormaHana\Core\Common\ServiceProviderInterface;

class GiftCardsServiceProvider implements ServiceProviderInterface {

    public function register(): void {
        $repository = new GiftCardsRepository();
        $service    = new GiftCardsService( $repository );
        
        ( new GiftCardsHooks( $service ) )->register_hooks();
    }
}
```

---

## 3. Mandatory Rules for Agents
- **No Global Functions**: Put logic inside namespace classes (`NormaHana\Core\...`).
- **Always Validate Syntax**: `php -l path/to/file.php`.
- **Always Deploy & Test**: rsync to VPS, reset OPcache, and curl test endpoint.
