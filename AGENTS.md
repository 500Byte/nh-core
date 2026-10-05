# AGENTS.md — nh-core

> **Master System Context & Execution Rules for AI Coding Agents**  
> **Plugin:** `nh-core` (Norma Hana E-Commerce Core Plugin)  
> **Namespace:** `NormaHana\Core\` (PSR-4)  
> **Target Environment:** CachyOS Linux (Local) / Docker PHP 8.2+ Ubuntu (VPS Production `root@2.25.85.177`)

---

## 1. Quick Orientation for AI Agents

`nh-core` is the site-specific core plugin for **Norma Hana** (a luxury Colombian slow-fashion e-commerce brand based in Santa Marta, Colombia).

### Primary Responsibilities
1. **Customer Preferences Hub (`/mi-cuenta/preferencias/`)**: Customer communication channels (WhatsApp/Email), cart reminder toggles, and Habeas Data (Colombia Ley 1581 de 2012) compliance.
2. **Global Analytics & Tracking**: Google Consent Mode v2 (`ad_storage: granted`, `analytics_storage: granted`, `url_passthrough: true`), Meta Pixel/CAPI event orchestration, and dataLayer hygiene.
3. **WooCommerce & HPOS Extensions**: High-Performance Order Storage abstractions (`wp_wc_orders`), CartFlows abandoned cart email sanitation, and checkout customizations.
4. **Integrations**: Asynchronous outgoing webhooks to n8n with HMAC-SHA256 signatures, Phosphor Icons for Elementor, and custom Elementor widgets.

---

## 2. Directory & Architecture Map (PSR-4 `NormaHana\Core\`)

```
nh-core/
├── nh-core.php                      # Main entry point & native PSR-4 autoloader
├── composer.json                    # Composer PSR-4 definition
├── AGENTS.md                        # Master AI Agent Context (this file)
├── README.md                        # Developer Overview
├── llms.txt                         # Machine-readable summary for LLM tools
│
├── src/                             # All modern PSR-4 classes live here
│   ├── Plugin.php                   # Central Kernel Bootstrapper Singleton
│   │
│   ├── Common/                      # Core Contracts & Interfaces
│   │   ├── ServiceProviderInterface.php   # Interface for module service providers (register())
│   │   ├── HookSubscriberInterface.php    # Interface for hook subscribers (register_hooks())
│   │   └── AbstractRepository.php         # Base database repository abstraction
│   │
│   └── Modules/                     # Domain-Driven Functional Modules
│       ├── Preferences/             # Communication Preferences & Habeas Data Hub
│       │   ├── PreferencesServiceProvider.php   # Module Bootstrapper
│       │   ├── PreferencesRepository.php        # DB table wp_nh_communication_preferences
│       │   ├── PreferencesTokenService.php      # HMAC-SHA256 security & URL generator
│       │   ├── PreferencesRestController.php    # REST API /wp-json/nh/v1/preferences
│       │   └── PreferencesFrontendHooks.php     # WooCommerce account tab, title, AJAX
│       │
│       ├── Tracking/                # Analytics & Consent Mode v2
│       │   ├── TrackingServiceProvider.php
│       │   └── ConsentModeSnippet.php           # Google Consent Mode v2 snippet (-10002)
│       │
│       ├── ECommerce/               # WooCommerce & HPOS Abstractions
│       │   ├── ECommerceServiceProvider.php
│       │   ├── HPOSOrderHelper.php
│       │   └── CartFlowsInterceptor.php
│       │
│       └── Integrations/            # n8n, Elementor & Phosphor Icons
│           ├── IntegrationsServiceProvider.php
│           ├── N8nWebhookClient.php
│           └── PhosphorIcons.php
│
├── inc/                             # Legacy files (wrapped by PSR-4 for backwards compatibility)
│   ├── class-nh-core-loader.php     # Legacy loader
│   ├── class-nh-core-preferences.php# Compatibility wrapper delegating to PSR-4
│   └── class-nh-core-woocommerce.php# Legacy WooCommerce controller
│
├── templates/                       # Frontend HTML Templates (No business logic)
│   └── myaccount/preferences.php
└── assets/                          # Static Frontend Assets
    ├── css/nh-preferences.css
    ├── js/nh-preferences.js
    └── images/
```

---

## 3. Strict Development Guardrails & Non-Negotiable Rules

1. **Zero Modifications Outside `nh-core`**:
   - NEVER modify WordPress core, WooCommerce core, or third-party vendor plugins (`pixelyoursite-pro`, `pressidium-cookie-consent`, etc.). All business logic MUST live inside `ecommerce/wp-content/plugins/nh-core/`.
2. **PSR-4 Class Convention**:
   - Every new PHP class MUST be placed in `src/` under `NormaHana\Core\...` with proper namespace declarations.
   - Filename MUST match class name exactly (e.g., `src/Modules/Preferences/PreferencesRepository.php` -> `class PreferencesRepository`).
3. **Mandatory Syntax Verification**:
   - ALWAYS run `php -l` on every created or modified PHP file before claiming completion.
4. **Security & Cryptography Standards**:
   - HMAC signatures MUST use `hash_hmac('sha256', $data, $secret)`.
   - Token comparisons MUST use `hash_equals($expected, $provided)` to prevent timing attacks.
   - Input sanitization MUST use native WP functions (`sanitize_email`, `sanitize_text_field`, `sanitize_key`).
5. **Deployment & Cache Purge Protocol**:
   - Sync target: `rsync -avz --exclude '.git' ecommerce/wp-content/plugins/nh-core/ root@2.25.85.177:/docker/normahana/html/wp-content/plugins/nh-core/`.
   - VPS container: `normahana-php-1`.
   - OPcache flush: `ssh root@2.25.85.177 "docker exec normahana-php-1 wp eval 'opcache_reset();' --allow-root"`.

---

## 4. Module Contracts & Database Schemas

### 4.1 Database Table: `wp_nh_communication_preferences`

```sql
CREATE TABLE wp_nh_communication_preferences (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    cart_reminders TINYINT(1) NOT NULL DEFAULT 1,
    atelier_news TINYINT(1) NOT NULL DEFAULT 1,
    preferred_channel VARCHAR(20) NOT NULL DEFAULT 'both', -- 'whatsapp' | 'email' | 'both'
    habeas_data_optout TINYINT(1) NOT NULL DEFAULT 0,
    optout_reason VARCHAR(255) NULL DEFAULT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'email_link',
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent VARCHAR(255) NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_email (email),
    KEY idx_user_id (user_id),
    KEY idx_cart_reminders (cart_reminders)
);
```

### 4.2 REST API Specifications (`/wp-json/nh/v1/preferences`)

- **Authentication Header**: `X-NH-API-Key: nh_telegram_bot_sec_2026_x871a` (or `api_key` query param).

#### `GET /wp-json/nh/v1/preferences?email=user@example.com`
- **Response**:
```json
{
  "success": true,
  "data": {
    "email": "user@example.com",
    "user_id": 1,
    "cart_reminders": 1,
    "atelier_news": 1,
    "preferred_channel": "both",
    "habeas_data_optout": 0,
    "is_new": false
  }
}
```

#### `POST /wp-json/nh/v1/preferences`
- **Body**:
```json
{
  "email": "user@example.com",
  "cart_reminders": 1,
  "atelier_news": 0,
  "preferred_channel": "whatsapp",
  "habeas_data_optout": 0,
  "source": "n8n_flow"
}
```

---

## 5. Verification Commands Reference

```bash
# 1. PHP Syntax Check Across All PSR-4 Classes
php -l ecommerce/wp-content/plugins/nh-core/nh-core.php
find ecommerce/wp-content/plugins/nh-core/src/ -name "*.php" -exec php -l {} \;

# 2. Test PSR-4 Class Instantiation via PHP CLI
php -r 'function plugin_dir_path($f){return dirname($f)."/";}; function add_action(){}; define("ABSPATH", "/tmp/"); require_once "ecommerce/wp-content/plugins/nh-core/nh-core.php"; $service = new \NormaHana\Core\Modules\Preferences\PreferencesTokenService(); echo $service->generate_token("test@example.com", time() + 86400) . "\n";'

# 3. Deploy to VPS
rsync -avz --exclude '.git' ecommerce/wp-content/plugins/nh-core/ root@2.25.85.177:/docker/normahana/html/wp-content/plugins/nh-core/

# 4. Flush OPcache on VPS
ssh root@2.25.85.177 "docker exec normahana-php-1 wp eval 'opcache_reset();' --allow-root"

# 5. Live REST API Test
curl -s -H 'X-NH-API-Key: nh_telegram_bot_sec_2026_x871a' 'https://www.normahana.com/wp-json/nh/v1/preferences?email=diegolnr3@gmail.com'
```
