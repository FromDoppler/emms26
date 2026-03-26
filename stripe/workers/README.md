# Stripe Workers

Sistema de workers basado en **Redis Streams** para procesar jobs de suscripciones Stripe de forma asíncrona.
Desacopla integraciones externas (email, listas, spreadsheet) del flujo del checkout.

---

## 📂 Estructura

```
core/                 # Lógica común
  BaseWorker.php      # Clase abstracta: retries, idempotencia, ack
  RedisManager.php    # Operaciones Redis Streams
EmailWorker.php       # Envía emails (Relay API)
ListWorker.php        # Agrega a Doppler
SpreadsheetWorker.php # Registra en Google Sheets
```

### Core

- **BaseWorker.php**: Clase abstracta con lógica común de procesamiento, retries con backoff, detección de jobs ya procesados, y ACK de mensajes.
- **RedisManager.php**: Singleton que encapsula operaciones Redis Streams (`xAdd`, `xReadGroup`, `xPending`, `xClaim`, `xAck`).

### Workers

Cada worker hereda de `BaseWorker` e implementa:

- `processJob($job)`: Lógica específica (enviar email, agregar a lista, etc.)
- `isJobProcessed($job)`: Verifica flag en DB (`email_sent`, `list_added`, `spreadsheet_saved`)
- `markJobAsProcessed($jobId)`: Actualiza flag en DB
- `getWorkerName()`: Nombre para logs

---

## ⚙️ Flujo

1. **Producción**: El controlador (`StripeCustomersController`) crea un job en DB y lo publica en los 3 streams (`stripe_jobs_email`, `stripe_jobs_list`, `stripe_jobs_spreadsheet`).

2. **Consumo**: Cada worker:
   - Lee mensajes de su stream con `XREADGROUP`
   - Busca el job en DB
   - Verifica si ya fue procesado (idempotencia)
   - Ejecuta la integración externa
   - Marca el job como procesado en DB
   - Envía `ACK` al stream

3. **Retries**:
   - Si falla, el mensaje queda en _pending_
   - Se reintenta con backoff exponencial: `min(base * 2^(retry-1), max)`
   - Mensajes huérfanos (de workers caídos) se reclaman automáticamente
   - Tras `WORKERS_MAX_RETRIES` (default: 5), se marca como fallo permanente y se ACKea

---

## ⚙️ Configuración

**Variables de entorno**:

- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
- `WORKERS_MAX_RETRIES` (default: 5)

**Timeouts y backoff**: Ver `config/redis.php` → `WORKERS_CONFIG`

```php
'email' => [
    'read_timeout' => 2000,   // 2s
    'backoff_base' => 60000,  // 1min
    'backoff_max'  => 120000  // 2min cap
]
```

---

## 🚀 Ejecución

**Desarrollo**:

```bash
php stripe/workers/EmailWorker.php
php stripe/workers/ListWorker.php
php stripe/workers/SpreadsheetWorker.php
```

**Producción**: Usar `systemd` o `docker-compose` con `restart: always`.

---

## 📌 Notas

- Los logs están separados por worker (`EMAIL_WORKER`, `LIST_WORKER`, `SPREADSHEET_WORKER`).
- Si Redis no está disponible, el controlador puede ejecutar las integraciones directo (fallback).
- Los workers detectan jobs ya procesados (idempotencia) antes de ejecutar la integración.
