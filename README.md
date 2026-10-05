# Norma Hana Core (`nh-core`)

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-wordpress)
![WooCommerce](https://img.shields.io/badge/WooCommerce-HPOS-purple)
![Architecture](https://img.shields.io/badge/Architecture-PSR--4%20Modular-green)

Plugin central site-specific para la marca **Norma Hana** (Santa Marta, Colombia). Centraliza la lógica de negocio e-commerce, centro de preferencias de clientes, consentimiento y analítica, e integraciones externas.

---

## 🏛️ Arquitectura Modular (v2.0)

`nh-core` sigue una arquitectura moderna **PSR-4** bajo el namespace `NormaHana\Core\`:

- **Autoloading**: Nativo sin dependencias externas en producción.
- **Service Providers**: Registro explícito por módulo (`ServiceProviderInterface`).
- **Hook Subscribers**: Registro de acciones y filtros desacoplado (`HookSubscriberInterface`).
- **Repository Pattern**: Aislamiento de consultas SQL a base de datos (`PreferencesRepository`).

### Módulos Principales (`src/Modules/`)
- `Preferences/`: Centro de Comunicaciones y Privacidad (`/mi-cuenta/preferencias/`), tokens HMAC-SHA256 y API REST.
- `Tracking/`: Google Consent Mode v2 (`ad_storage: granted`, `analytics_storage: granted`) y DataLayer.
- `ECommerce/`: Abstracciones de WooCommerce HPOS (`wp_wc_orders`) e interceptor de CartFlows.
- `Integrations/`: Webhooks salientes a n8n con firmas HMAC, Phosphor Icons y Elementor.

---

## 🚀 Despliegue en Producción (VPS)

```bash
# Sincronizar cambios al contenedor PHP en el VPS
rsync -avz --exclude '.git' ./ root@2.25.85.177:/docker/normahana/html/wp-content/plugins/nh-core/

# Reiniciar OPcache
ssh root@2.25.85.177 "docker exec normahana-php-1 wp eval 'opcache_reset();' --allow-root"
```

Para contexto técnico completo de desarrollo por agentes de IA, consulta [`AGENTS.md`](AGENTS.md).
