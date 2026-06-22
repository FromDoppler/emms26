# Checkout Payments V1

## Propósito

Checkout Payments V1 define el flujo unificado de compra del pase VIP para EMMS.

El objetivo de este documento es describir el contrato funcional y operativo del checkout: qué responsabilidades tiene EMMS, qué integración de pagos consume, cómo se interpreta el estado local de una compra y qué criterios mínimos deben respetar desarrollo, QA y operación.

---

## 1. Alcance

Checkout Payments V1 cubre la compra del pase VIP del evento actual de EMMS.

La V1 define:

- un checkout único para compra VIP;
- una success page única;
- cálculo de precio, descuento y precio final;
- soporte para cupones aplicables al flujo VIP;
- aprobación local cuando el precio final no requiere cobro;
- tokenización de tarjeta mediante eProtect;
- pago con tarjeta mediante Doppler Payments API;
- registro operativo local de intentos de pago;
- idempotencia local por intento;
- aplicación local del acceso VIP;
- efectos post-checkout persistidos y recuperables;
- tracking de conversión en success.

Checkout Payments V1 no modela carrito, múltiples productos seleccionables, suscripciones, reembolsos ni administración general de tickets.

---

## 2. Decisiones estables de V1

Estas decisiones forman parte del contrato de arquitectura de Checkout Payments V1.

### Checkout unificado

V1 unifica la experiencia de compra VIP en un único checkout.

El usuario compra desde:

```txt
/checkout
```

y, cuando corresponde mostrar confirmación, llega a:

```txt
/checkout-success?payment_id=...
```

La success page no reconstruye el pago desde el provider. Consulta el estado local de EMMS usando `payment_id`.

### EMMS es dueño del flujo de checkout

EMMS controla el flujo funcional:

- resuelve el evento actual;
- calcula precio;
- aplica cupones;
- valida elegibilidad;
- crea un intento local de pago;
- llama a la integración backend de pagos;
- interpreta el resultado;
- aplica acceso VIP;
- registra efectos post-checkout;
- expone el estado para success y operación.

EMMS no delega el checkout completo a un proveedor externo.

### EMMS no manipula tarjeta cruda

Los datos sensibles de tarjeta no deben llegar crudos al backend de EMMS.

El frontend usa eProtect para obtener un token de pago. EMMS trabaja con ese token y con datos no sensibles necesarios para completar la operación.

### EMMS consume Doppler Payments API

EMMS no se integra directamente con Worldpay/Comerica.

La integración backend de pagos de EMMS es Doppler Payments API.

Doppler Payments API encapsula la comunicación con Worldpay/Comerica.

```txt
Frontend EMMS
    ↓
eProtect tokeniza tarjeta
    ↓
Backend EMMS
    ↓
Doppler Payments API
    ↓
Worldpay/Comerica
```

Esta frontera es central: para EMMS, el contrato de pago backend es Doppler Payments API.

### EMMS guarda estado local del pago

EMMS mantiene un registro operativo local del intento de pago.

Ese registro permite:

- resolver la success page;
- responder retries idempotentes;
- auditar intentos;
- diagnosticar errores;
- conciliar casos ambiguos;
- evitar depender de consultas directas al provider desde la experiencia pública.

El estado público de la compra se deriva de `payment_transactions`, no del estado de los jobs ni de una consulta directa al provider.

### Los efectos externos no definen el pago

El pago aprobado no depende de que todos los efectos externos hayan terminado.

Email, spreadsheet, listas u otros efectos post-checkout pueden fallar o quedar pendientes sin cambiar el estado aprobado del pago.

---

## 3. Superficie pública

La superficie pública de Checkout Payments V1 es:

```txt
/checkout
/checkout-success?payment_id=...
/services/calculate-payment.php
/services/create-payment.php
/services/get-payment.php
```

`calculate-payment` permite calcular precio, descuento, precio final y datos públicos necesarios para preparar el checkout.

`create-payment` crea o continúa un intento de checkout y, si corresponde, ejecuta el pago.

`get-payment` devuelve el estado local de un pago a partir de `payment_id`.

Cualquier ruta o endpoint fuera de esta superficie queda fuera del contrato público de Checkout Payments V1.

---

## 4. Flujo conceptual

```txt
Usuario inicia checkout VIP
        ↓
EMMS calcula precio y elegibilidad
        ↓
Si requiere tarjeta, eProtect tokeniza datos sensibles
        ↓
EMMS crea o continúa un intento local de pago
        ↓
EMMS reclama el intento para procesamiento
        ↓
EMMS llama a Doppler Payments API
        ↓
Doppler Payments API procesa detrás de Worldpay/Comerica
        ↓
EMMS normaliza el resultado
        ↓
EMMS actualiza el estado local
        ↓
Si corresponde, EMMS aplica acceso VIP
        ↓
EMMS registra efectos post-checkout
        ↓
EMMS intenta ejecutar efectos recuperables
        ↓
Si el pago está aprobado, el usuario puede ver confirmación en success
```

El flujo puede variar en detalles de implementación, pero la frontera de responsabilidades se mantiene:

```txt
eProtect protege tarjeta
EMMS orquesta checkout
Doppler Payments API ejecuta pagos
Worldpay/Comerica procesa detrás de Doppler Payments API
```

---

## 5. Modelo operativo

Checkout Payments V1 se apoya en dos registros operativos principales.

### Pagos

`payment_transactions` funciona como ledger local del checkout.

Guarda la información necesaria para identificar, auditar y recuperar un intento de pago:

- identificador público del pago;
- correlación;
- idempotencia;
- cliente;
- ticket;
- cupón;
- importes;
- estado;
- provider usado;
- evidencia sanitizada del provider.

El identificador público es el valor que se usa como `payment_id` en success.

El estado público de la compra se deriva de este registro, no del estado de los efectos post-checkout ni de una consulta directa al provider.

### Efectos post-checkout

`user_event_jobs` registra efectos post-checkout recuperables.

Los efectos esperados del flujo incluyen email, guardado en spreadsheet y agregado a listas. Para ciertos casos, como usuarios sin inscripción previa completa, el checkout puede necesitar emitir efectos asociados a más de una variante del evento.

El documento no depende de la cantidad exacta de jobs generados. Lo importante es la regla operativa:

> Los efectos post-checkout se registran para trazabilidad y recuperación, y no definen si el pago fue aprobado.

---

## 6. Estados del pago

Un pago representa un intento de checkout VIP.

Estados conceptuales:

- `pending`;
- `processing`;
- `approved`;
- `rejected`;
- `error`.

### `pending`

El intento fue creado localmente, pero todavía no fue reclamado para ejecución.

### `processing`

El intento fue reclamado y está en ejecución.

Un retry con la misma idempotency key debe reconocer que el intento ya está en proceso.

### `approved`

El pago quedó aprobado para Checkout Payments V1.

En V1, `approved` significa que:

- el provider aprobó la compra, o el precio final no requirió cobro;
- el estado local quedó aprobado;
- el acceso VIP fue aplicado localmente;
- los efectos post-checkout fueron registrados;
- el commit local terminó.

`approved` no significa que todos los efectos externos terminaron correctamente.

### `rejected`

El pago fue rechazado por una causa esperada de negocio o validación terminal.

El usuario puede iniciar otro intento de pago.

### `error`

Hubo un error técnico o ambiguo.

Un `error` no debe tratarse automáticamente como rechazo de negocio.

Si existe evidencia del provider, el caso debe conciliarse antes de permitir otro intento.

---

## 7. Idempotencia

Checkout Payments V1 usa idempotencia local por intento de pago.

El frontend envía:

```txt
checkout.idempotencyKey
```

Esa key identifica el intento actual.

Retries, doble click y reintentos por error de red deben reutilizar la misma key.

Si llega otra request con la misma key:

- si el intento está terminal, EMMS responde desde el estado local;
- si el intento está `processing`, EMMS responde como intento en proceso;
- si el intento está `pending`, EMMS puede continuarlo.

Una nueva `idempotencyKey` representa un nuevo intento.

EMMS puede propagar identificadores hacia Doppler Payments API para trazabilidad, por ejemplo:

- `Idempotency-Key`;
- `X-Correlation-Id`;
- `X-Checkout-Public-Id`.

Esos identificadores ayudan a correlacionar sistemas, pero la garantía principal de V1 es local. EMMS no asume una garantía downstream de no doble cobro salvo que Doppler Payments API la defina explícitamente como parte de su contrato (Hoy no lo hace).

La idempotencia de V1 es por intento de pago, no una garantía global por email.

Casos como dos pestañas, dos navegadores o dos dispositivos con keys distintas se tratan como edge operativo.

---

## 8. Pricing, cupones y elegibilidad

EMMS resuelve el evento actual y calcula el precio del pase VIP.

La V1 trabaja con el pase VIP del evento actual. El usuario no elige múltiples tickets ni arma un carrito.

El cálculo de precio determina:

- precio base;
- cupón aplicable;
- descuento;
- precio final;
- moneda;
- si requiere pago con provider o no.

### Cupones

El checkout soporta cupones aplicables al flujo VIP.

La API pública usa:

```txt
couponCode
```

El cupón puede llegar como código directo o como código de link, según el flujo de adquisición.

Si el cálculo deja precio final cero, EMMS aprueba localmente el checkout sin llamar al provider de pagos.

En V1, el caso esperado para precio final cero es un cupón de descuento total.

El detalle interno de validación de cupones puede evolucionar, pero el contrato funcional es:

- cupón válido modifica el precio final;
- cupón inválido no permite usar descuento;
- precio final cero no requiere provider;
- la transacción guarda evidencia suficiente del cupón aplicado.

### Elegibilidad

Antes de cobrar, EMMS valida que el usuario pueda comprar VIP.

Reglas funcionales:

- email requerido;
- nombre requerido;
- teléfono requerido;
- privacidad requerida cuando no existe inscripción previa suficiente;
- un usuario ya VIP no debe volver a comprar VIP;
- un usuario free existente puede hacer upgrade a VIP;
- un usuario nuevo puede comprar VIP si completa los datos requeridos.

Para usuarios free existentes, se considera que ya existe consentimiento operativo suficiente para continuar el upgrade VIP.

---

## 9. Tokenización y pago con tarjeta

Cuando el precio final requiere pago con tarjeta, el frontend debe obtener un token mediante eProtect.

El backend de EMMS espera recibir:

```txt
payment.worldPayLowValueToken
```

Ese token representa la tarjeta de forma segura para el flujo de pago.

EMMS no debe recibir ni almacenar PAN, CVV ni datos crudos equivalentes.

### Integración backend

EMMS consume Doppler Payments API para ejecutar el pago.

El flujo de pago con tarjeta se entiende en dos momentos conceptuales:

- autorización;
- compra.

Doppler Payments API encapsula los detalles de Worldpay/Comerica.

Desde la perspectiva de EMMS, el resultado se normaliza a:

- `approved`;
- `rejected`;
- `error`.

EMMS guarda evidencia sanitizada suficiente para diagnóstico y conciliación, sin exponer detalles internos del provider en respuestas públicas.

---

## 10. Jobs post-checkout

Después de un pago aprobado, EMMS aplica el acceso correspondiente y registra efectos post-checkout.

Los efectos esperados incluyen:

- envío de email;
- guardado en spreadsheet;
- agregado a listas.

Según el tipo de usuario y su estado previo, EMMS puede registrar efectos asociados a inscripción free, acceso VIP o ambos.

El documento no fija una cantidad exacta de jobs como contrato. Lo importante es que los efectos necesarios queden representados de forma recuperable.

### Ejecución en V1

En V1, los efectos post-checkout se ejecutan dentro del flujo online, después del commit local, de forma best-effort.

Si un efecto falla:

- el pago sigue aprobado;
- el acceso VIP no se revierte;
- el efecto queda disponible para revisión o recuperación operativa;
- no se vuelve a llamar al provider de pagos.

`user_event_jobs` funciona como registro recuperable mínimo de efectos post-checkout.

No se requiere para V1 un worker async completo como parte del contrato funcional del checkout.

---

## 11. Success page y tracking

La success page es el destino principal para pagos aprobados.

La success page recibe:

```txt
payment_id
```

y consulta el estado local mediante `get-payment`.

En V1, la success page muestra confirmación cuando el estado local es `approved`.

Si el pago no existe o no está aprobado, debe mostrar un estado controlado de error o pago no confirmado.

La success page no consulta directamente a Doppler Payments API ni a Worldpay/Comerica.

### Tracking

El tracking de conversión se ejecuta desde success cuando el pago está aprobado.

Reglas:

- solo debe ejecutarse para pagos aprobados;
- debe deduplicarse usando el identificador público del pago;
- debe ser best-effort;
- un refresh no debe duplicar conversión;
- una falla de tracking o almacenamiento local no debe romper la pantalla.

El tracking no define el estado del pago.

---

## 12. Información pública e interna

Las respuestas públicas del checkout deben exponer solo la información necesaria para la experiencia de usuario:

- éxito o no éxito;
- estado público del pago;
- identificador público;
- importes públicos;
- moneda;
- correlation id cuando sea útil para soporte.

No deben exponer:

- respuestas crudas del provider;
- payloads internos;
- jobs internos;
- detalles internos de autorización o compra;
- datos sensibles de tarjeta.

La información operativa completa queda en los registros internos del checkout.

---

## 13. Operación mínima

`payment_transactions` es el punto de partida para diagnosticar pagos.

`user_event_jobs` es el punto de partida para diagnosticar efectos post-checkout.

La success page no consulta directamente al provider.

Los efectos post-checkout no definen si el pago fue aprobado.

Un pago aprobado no debe volver a cobrarse por fallas de efectos externos.

Un error técnico no debe tratarse automáticamente como rechazo de negocio.

Un caso con evidencia del provider debe conciliarse antes de permitir otro intento.

### Consultas mínimas

Buscar pago por `payment_id`:

```sql
SELECT *
FROM payment_transactions
WHERE public_id = '<payment_id>';
```

Buscar pago por `correlation_id`:

```sql
SELECT *
FROM payment_transactions
WHERE correlation_id = '<correlation_id>';
```

Buscar efectos post-checkout de un pago:

```sql
SELECT *
FROM user_event_jobs
WHERE aggregate_type = 'checkout_transaction'
  AND aggregate_id = <payment_transaction_id>
ORDER BY id ASC;
```

### Pago aprobado con efectos fallidos

Si el pago está aprobado y existen efectos fallidos o pendientes:

- no volver a cobrar;
- no crear otro pago;
- revisar los efectos asociados;
- reintentar o resolver operativamente esos efectos;
- mantener el pago aprobado.

### Error con evidencia del provider

Si el pago quedó en error pero tiene evidencia del provider, por ejemplo autorización, transaction link, provider transaction id o response codes internos:

- no tratar automáticamente como rechazo;
- no forzar un nuevo intento sin revisar evidencia;
- conciliar con información local y del provider;
- decidir después de la conciliación si el usuario puede intentar nuevamente.

---

## 14. QA sugerido

QA debe validar el comportamiento del checkout desde la perspectiva funcional y operativa, sin depender de la granularidad interna de la implementación.

### Usuarios

- usuario nuevo compra VIP;
- usuario free compra VIP;
- usuario ya VIP intenta comprar.

### Cupones y precio final

- cupón válido aplica descuento;
- cupón inválido no permite usar descuento;
- precio final cero aprueba sin llamar al provider;
- precio final mayor a cero requiere pago con tarjeta.

### Tokenización y pago

- falta de token no llama al provider;
- provider aprueba;
- provider rechaza;
- provider devuelve error;
- provider aprobado con falla local posterior conserva evidencia para conciliación.

### Idempotencia

- doble click no crea un nuevo intento;
- retry con la misma key reutiliza el intento;
- intento en proceso se responde como en proceso;
- intento terminal se responde desde estado local;
- nueva key representa nuevo intento.

### Success y tracking

- pago aprobado muestra confirmación;
- pago inexistente o no aprobado muestra estado controlado;
- tracking solo corre con pago aprobado;
- refresh no duplica tracking;
- falla de tracking no rompe success.

### Efectos post-checkout

- pago aprobado registra efectos post-checkout;
- efectos esperados se pueden ejecutar;
- efecto fallido no cambia el pago aprobado;
- recuperación de efectos no vuelve a llamar al provider;
- recuperación de efectos no modifica el estado del pago.

### Conciliación

- caso ambiguo con evidencia del provider requiere revisión antes de permitir otro intento.

Si estos escenarios pasan, Checkout Payments V1 queda validado para su alcance: checkout VIP unificado, tokenización con eProtect, pago mediante Doppler Payments API, estado local en EMMS, idempotencia por intento, success page, tracking y efectos post-checkout recuperables.
