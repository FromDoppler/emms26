# Checkout Payments V1

## 1. Propósito

Checkout Payments V1 define el contrato funcional y técnico del flujo de compra del pase VIP de EMMS.

El objetivo de este documento es establecer:

- las responsabilidades de EMMS y de los servicios externos;
- la identidad durable de cada intento;
- los estados y las invariantes del ledger;
- la clasificación de resultados del proveedor;
- las condiciones necesarias para considerar un pago aprobado;
- el comportamiento de los endpoints públicos;
- los límites de recuperación y operación aceptados para V1.

Este documento es la fuente canónica del comportamiento de Checkout Payments V1.

---

## 2. Alcance

Checkout Payments V1 incluye:

- un checkout único para comprar el pase VIP;
- cálculo de precio, descuento y precio final;
- cupones aplicables al checkout;
- aprobación local mediante cupón del 100 %;
- tokenización de tarjeta con eProtect;
- pago mediante Doppler Payments API;
- ledger local identificado por `paymentId`;
- idempotencia local por intento;
- marker durable de aprobación remota;
- completion local atómica;
- aplicación del acceso VIP;
- persistencia de efectos post-checkout;
- consulta del estado local mediante `get-payment`;
- success page y tracking para pagos aprobados.

Quedan fuera de V1:

- carrito o múltiples productos;
- suscripciones;
- reembolsos;
- exclusión global de compras por email y evento;
- idempotencia downstream no garantizada por Doppler Payments API;
- reintentos automáticos de Authorization o Purchase;
- reconciliador financiero;
- worker o scheduler financiero;
- recuperación administrativa mediante SQL;
- endpoints o comandos de recuperación financiera;
- persistencia del instrumento de pago entre recargas del navegador.

---

## 3. Responsabilidades y fronteras

### Frontend de EMMS

El frontend:

- recopila la intención comercial;
- tokeniza la tarjeta mediante eProtect;
- genera la `paymentId`;
- envía la request completa a EMMS;
- consulta el estado local ante resultados no terminales;
- redirige a success exclusivamente cuando el ledger indica `approved`.

### eProtect

eProtect protege los datos sensibles de tarjeta y entrega un token de bajo valor.

EMMS no debe recibir ni almacenar:

- PAN;
- CVV;
- número de tarjeta sin tokenizar;
- información equivalente que permita reconstruir el instrumento.

### Backend de EMMS

EMMS:

- valida la request;
- resuelve el evento;
- calcula pricing;
- valida elegibilidad;
- crea y administra el ledger;
- reclama el intento para procesamiento;
- invoca Doppler Payments API cuando corresponde;
- clasifica el resultado;
- persiste la aprobación remota;
- aplica acceso VIP;
- crea los jobs post-checkout;
- expone el estado público del pago.

### Doppler Payments API

Doppler Payments API es la frontera backend de pagos consumida por EMMS.

EMMS no se integra directamente con Worldpay o Comerica.

```text
Frontend EMMS
    ↓
eProtect
    ↓
Backend EMMS
    ↓
Doppler Payments API
    ↓
Worldpay / Comerica
```

---

## 4. Identidad del intento

### `paymentId`

`paymentId` es la única identidad pública y durable de un intento de checkout.

Debe ser una UUID v4 criptográficamente segura.

Ejemplo:

```text
0b8dbecd-1657-4bf5-b84e-a2805da9f189
```

El backend normaliza la UUID a minúsculas antes de buscarla o persistirla.

Checkout Payments V1 no utiliza:

- `public_id`;
- `idempotency_key`;
- `checkout.idempotencyKey`;
- aliases alternativos para identificar el pago.

La base garantiza una sola fila por `paymentId` mediante una restricción única sobre `payment_transactions.payment_id`.

### Alcance de la idempotencia

La garantía de idempotencia es por `paymentId`.

Para una misma `paymentId`:

- sólo un claim puede habilitar la llamada al proveedor;
- una operación terminal se responde desde el ledger;
- una operación `processing` sin marker no vuelve a llamar al proveedor;
- una operación `processing` con marker ejecuta solamente completion local.

Dos UUID diferentes representan dos intentos distintos.

V1 no garantiza exclusión global por:

- email;
- evento;
- ticket;
- navegador;
- dispositivo.

Dos intentos diferentes pueden alcanzar al proveedor aunque pertenezcan al mismo usuario.

---

## 5. Request de creación

Todo POST a `create-payment` utiliza una intención completa.

Ejemplo para tarjeta:

```json
{
  "paymentId": "0b8dbecd-1657-4bf5-b84e-a2805da9f189",
  "checkout": {
    "origin": "checkout"
  },
  "customer": {
    "email": "user@example.com",
    "name": "User",
    "phone": "+541100000000",
    "acceptPolicies": true,
    "acceptPromotions": false
  },
  "couponCode": null,
  "payment": {
    "worldPayLowValueToken": "...",
    "ccExpMonth": 12,
    "ccExpYear": 2030,
    "ccType": 1
  }
}
```

La intención durable se identifica mediante:

- email normalizado;
- `couponCode` canónico o `null`.

El ticket se resuelve en backend a partir de la configuración activa del evento.

`couponCode` ausente, `null` o vacío se normaliza a `null`.

`acceptPolicies` y `acceptPromotions` deben viajar como booleanos JSON reales.
`acceptPolicies=true` es obligatorio cuando la elegibilidad de un usuario nuevo lo requiere.
`acceptPromotions` es opcional y `false` por defecto.
El frontend debe enviar el estado booleano real del checkbox y no convertir strings
arbitrarios mediante `Boolean("false")`, porque eso produce `true` en JavaScript.

Requests con forma inválida producen `422 validation_error` antes de cualquier lookup, pricing o INSERT.

La request pública valida explícitamente:

- `paymentId` como UUID v4 string;
- `customer` como objeto;
- `customer.email` como string email válido y de hasta 250 caracteres;
- `customer.name` de hasta 150 caracteres;
- `customer.lastname` de hasta 150 caracteres;
- `customer.phone` de hasta 300 caracteres;
- `customer.company` de hasta 300 caracteres;
- `customer.jobPosition` de hasta 150 caracteres;
- `customer.website` de hasta 150 caracteres;
- `customer.emailPlatform` de hasta 150 caracteres;
- `customer.utm_source`, `customer.utm_medium`, `customer.utm_campaign`,
  `customer.utm_content`, `customer.utm_term` y `customer.emms_ref` como
  string o `null`; sus valores normalizados admiten hasta 2048 bytes por
  campo;
- `customer.acceptPolicies` y `customer.acceptPromotions` como booleanos JSON reales cuando están presentes;
- `couponCode` como string o `null`;
- `checkout`, si existe, como objeto;
- `checkout.origin`, si existe, como escalar no nulo aceptado;
- `origin`, si existe, como escalar no nulo aceptado;
- `payment`, si existe, como objeto.

El límite de `2048` bytes se aplica después de validar UTF-8 y normalizar el
valor mediante `trim()`. Los valores mayores se rechazan con
`422 validation_error` antes de crear el ledger y antes de invocar al
proveedor.

Cuando `checkout.origin` y `origin` conviven, `checkout.origin` tiene prioridad. Un valor vacío normaliza a `checkout`.

Cuando `payment` está presente:

- `worldPayLowValueToken` como string;
- `ccExpMonth` como integer o digit-string;
- `ccExpYear` como integer o digit-string;
- `ccType` como integer o digit-string; valores admitidos: `1` Visa, `2` Mastercard, `3` Amex;
- cualquier tipo incorrecto produce `422 validation_error` antes de lookup, pricing o INSERT.

Si una `paymentId` existente se reutiliza con otra intención, el backend responde:

```text
409 payment_intent_conflict
```

La respuesta no debe exponer los datos de la intención previamente registrada.

No existe un formato de POST mínimo basado solamente en `paymentId`.

GET es la única operación pública que recibe exclusivamente la identidad del pago.

---

## 6. Tickets, cupones y pricing

Checkout Payments V1 vende un único ticket VIP por evento.

El ticket se resuelve en backend a partir de la configuración activa del evento.

El backend requiere exactamente un ticket activo y un precio base positivo para el evento:

- cero tickets activos → `ticket_unavailable`;
- un ticket activo → continuar;
- más de un ticket activo → configuración inconsistente y fail closed.

`couponCode` es el único código público opcional.

El ticket no forma parte de la request pública.

### Cupones

Los cupones se resuelven exclusivamente mediante:

```text
payment_coupons.code
```

Checkout Payments V1 no utiliza `payment_coupons.link_code` como alias público.

Un cupón válido puede reducir el precio final.

Cuando el precio final es `0.00`:

- no se llama al proveedor;
- el cupón fue validado para el evento y el ticket;
- la transacción utiliza `payment_method = coupon`;
- el provider local es `coupon`;
- el cupón aplicado queda persistido;
- la completion local aplica VIP y crea los jobs correspondientes.

Un ticket con precio base `0.00` o negativo falla closed antes del INSERT.

### Importes

Checkout Payments V1 opera exclusivamente en USD.

Los importes se mantienen como valores decimales canónicos de dos posiciones:

```text
10.00
99.50
0.00
```

El cálculo de descuentos se realiza sin utilizar aritmética binaria de punto flotante para las decisiones monetarias.

El importe enviado a Doppler Payments API se serializa como JSON number conservando exactamente dos decimales.

Una moneda diferente de USD falla antes de crear el intento y nunca alcanza al proveedor.

---

## 7. Ledger local

`payment_transactions` es la autoridad durable para:

- identificar el intento;
- decidir si puede llamarse al proveedor;
- resolver replays;
- completar aprobaciones;
- construir respuestas públicas;
- diagnosticar el estado local.

La definición física del schema se mantiene en `.docker/db/EMMS26.sql`.

### Información durable principal

El ledger conserva:

- `payment_id`;
- `correlation_id`;
- estado;
- provider;
- método de pago;
- origen;
- datos allowlisted del cliente;
- ticket;
- cupón;
- importes;
- moneda;
- familia y edición del evento;
- marker de aprobación;
- evidencia estructurada de resultados terminales;
- `registered_id`;
- timestamps.

`raw_request` contiene únicamente una proyección allowlisted del contexto necesario para completion.

No contiene:

- token de eProtect;
- tarjeta;
- CVV;
- tokenized PAN;
- request completa al proveedor;
- response completa del proveedor.

---

## 8. Estados del pago

Los estados permitidos son:

```text
pending
processing
approved
rejected
error
```

### `pending`

El intento fue creado localmente y todavía no fue reclamado.

Un intento `pending` no tiene:

- marker;
- `registered_id`;
- resultado público;
- evidencia del proveedor.

### `processing`

El intento fue reclamado.

Puede representar dos situaciones válidas.

#### Tarjeta sin marker

```text
status = processing
provider_approved_at = NULL
```

No existe evidencia financiera durable en el ledger.

El backend responde `202` y no vuelve a llamar al proveedor para esa `paymentId`.

Este estado puede permanecer indefinidamente si la operación se interrumpió en una ventana ambigua.

#### Tarjeta con marker

```text
status = processing
provider_approved_at != NULL
response_code = provider_approved
```

La aprobación remota está confirmada localmente y sólo falta ejecutar o confirmar la completion.

Un replay puede ejecutar completion, pero nunca vuelve a llamar al proveedor.

### `approved`

`approved` significa que el commit local completo terminó correctamente.

Para tarjeta implica:

- marker durable válido;
- Authorization aprobada;
- Purchase aprobada;
- número de autorización presente;
- acceso VIP aplicado;
- `registered_id` persistido;
- jobs post-checkout creados;
- estado local actualizado a `approved`.

Para cupón implica:

- precio final `0.00`;
- cupón durable;
- acceso VIP aplicado;
- `registered_id` persistido;
- jobs post-checkout creados;
- estado local actualizado a `approved`.

`approved` no significa que todos los efectos externos hayan finalizado correctamente.

### `rejected`

Representa una condición terminal conocida.

Puede corresponder a:

- usuario ya VIP antes del claim;
- rechazo contractual del proveedor.

Un rechazo del proveedor debe:

- no tener marker;
- conservar el código estructurado aplicable;
- utilizar una categoría pública incluida en el catálogo contractual.

### `error`

Representa una falla técnica demostrablemente anterior al intento remoto.

Debe:

- no tener marker;
- no tener evidencia del proveedor;
- utilizar `response_code = payment_error`.

Una falla posterior al inicio de una llamada remota no se transforma en `error`; se conserva como `processing`.

---

## 9. Consistencia del ledger

Toda lectura relevante valida las invariantes del estado.

Una fila inconsistente falla de forma cerrada:

- no habilita una llamada al proveedor;
- no habilita completion;
- no se publica como resultado terminal válido;
- produce una respuesta interna controlada.

### Invariantes de tarjeta

```text
approved
→ marker válido
→ authorization_response_code = 000
→ purchase_response_code = 000
→ authorization_number no vacío
→ registered_id no vacío
→ response_code = approved
```

```text
processing con marker
→ evidencia APPROVED completa
→ response_code = provider_approved
```

```text
processing sin marker
→ sin evidencia del proveedor
→ sin response_code
```

```text
rejected
→ sin marker
→ authorization_number vacío
→ rechazo contractual consistente
   o response_code = already_vip sin evidencia del proveedor
```

```text
error
→ sin marker
→ authorization_number vacío
→ sin evidencia del proveedor
→ response_code = payment_error
```

### Invariantes de cupón

```text
provider = coupon
payment_method = coupon
final_amount = 0.00
coupon_id no vacío
sin marker
sin evidencia de tarjeta
```

### Invariantes de importes

```text
amount > 0
0 <= discount_amount <= amount
final_amount = amount - discount_amount
```

Para cupón:

```text
discount_amount = amount
final_amount = 0.00
```

---

## 10. Creación y resolución ledger-first

### Payment existente

El backend resuelve primero el ledger:

```text
normalizar paymentId
→ buscar payment
→ validar consistencia
→ validar intención
→ clasificar estado
```

Comportamiento:

```text
terminal
→ responder desde ledger
```

```text
processing sin marker
→ responder 202
```

```text
processing con marker
→ ejecutar solamente completion
```

```text
pending
→ validar instrumento y elegibilidad
→ intentar claim
```

Un replay nunca recalcula ni reemplaza:

- ticket;
- cupón;
- pricing;
- evento;
- importes;
- identidad del cliente.

La primera fila creada define la intención durable.

### Payment inexistente

Antes del INSERT se validan:

- UUID;
- request completa;
- cliente;
- evento;
- pricing;
- cupón;
- moneda;
- instrumento, cuando corresponda.
- `customer_ip`, cuando se persiste, como IPv4 o IPv6 válida; en otro caso `null`.

Después:

```text
INSERT pending
→ ante duplicate payment_id, cargar la fila ganadora
→ validar intención
→ validar already_vip
→ claim pending a processing
→ procesar tarjeta o completar cupón
```

---

## 11. Claim de procesamiento

El claim es una transición atómica:

```text
pending → processing
```

Sólo la request cuyo UPDATE confirma una fila afectada puede llamar al proveedor.

Las requests perdedoras deben recargar el ledger y responder según el estado durable.

No se mantiene una transacción de base de datos abierta durante la llamada remota.

Cuando el claim gana, el processor recibe la fila ya marcada como `processing`
sin una segunda recarga.

Una falla posterior al claim y anterior a la llamada puede dejar el intento en `processing`. V1 acepta ese comportamiento conservador y no incorpora lease, TTL ni reclaim automático.

---

## 12. Contrato con Doppler Payments API

Desde el primer intento de llamada remota, el cliente normaliza el resultado como:

```text
APPROVED
REJECTED
ERROR
UNKNOWN
```

`UNKNOWN` es un resultado del cliente del proveedor, no un estado del ledger.

### Authorization

Authorization se interpreta normalmente mediante HTTP 200 con `responseCode`.

Para `responseCode = 000`, la respuesta además debe incluir `tokenizedPan` no
vacío. `transactionLinkID` es opcional; cuando está presente debe ser un string
y se conserva para correlacionar Authorization con Purchase.

En outcomes con `responseCode` distinto de `000`, un `transactionLinkID` string
se conserva para observabilidad. Un valor opcional inválido se omite sin cambiar
la clasificación financiera del código.

EMMS también acepta defensivamente un HTTP 400 con un `PaymentError`
estructurado si el proveedor lo devuelve. Esta tolerancia no implica que ese
camino sea producido actualmente por Authorization en Doppler Payments API.

La implementación actual del proveedor entrega normalmente los rechazos
financieros de Authorization mediante HTTP 200 con `responseCode`.

Una respuesta HTTP `401` o `403` se clasifica como `ERROR`.

Un `responseCode` distinto de `000`, o el `errorCode` de un `PaymentError`
HTTP 400, sólo se considera `REJECTED` cuando está incluido en el catálogo
contractual.

Cualquier otro resultado es `UNKNOWN`.

### Purchase

Purchase sólo se interpreta mediante:

- HTTP 200 con `responseCode`; o
- HTTP 400 con un `PaymentError` estructurado.

Una aprobación exige:

```text
authorization responseCode = 000
purchase responseCode = 000
authorizationNumber no vacío
```

Si falta cualquiera de esos elementos, el resultado es `UNKNOWN`.
`authorizationNumber` sólo puede existir para una aprobación completa.

### Respuestas ambiguas

Se consideran `UNKNOWN`, entre otros casos:

- timeout;
- error de conexión después de iniciar cURL;
- cualquier falla durante Purchase después de `Authorization 000`;
- redirect;
- HTTP no contractual;
- JSON inválido;
- respuesta sin campos obligatorios;
- código no incluido en el catálogo;
- aprobación sin número de autorización.

El cliente:

- no sigue redirects;
- no realiza retries HTTP;
- no reintenta Authorization;
- no reintenta Purchase;
- no persiste bodies remotos completos.

La frontera del transporte distingue entre fallas que demuestran que Authorization no pudo ejecutarse y fallas ambiguas posteriores:

- DNS, URL malformada o conexión imposible antes de Authorization → `ERROR`;
- `Authorization 401/403` → `ERROR`;
- timeout, send/receive incierto, `5xx`, redirect o JSON inválido → `UNKNOWN`;
- cualquier falla durante Purchase después de `Authorization 000` → `UNKNOWN`.

---

## 13. Catálogo de rechazos

`CheckoutProviderRejectionCatalog` es la fuente de verdad para convertir un código remoto en una categoría pública.

```text
004 → card_invalid_expiration_date
005 → card_declined
013 → card_invalid_security_code
016 → card_declined
017 → card_suspected_fraud
018 → card_invalid_number
025 → card_suspected_fraud
039 → card_insufficient_funds
045 → card_invalid_expiration_date
078 → card_invalid_number

DeclinedPaymentTransaction → card_declined
DoNotHonorPaymentResponse → card_declined
FraudPaymentTransaction → card_suspected_fraud
```

El código `078` conserva la categoría de producto utilizada por Doppler WebApp.

No se deriva una categoría terminal a partir de cualquier código RAFT existente.

Un código no incluido en el catálogo es `UNKNOWN`, aunque sea numérico.

Para resultados terminales aprobados o rechazados, los códigos originales aplicables se guardan en columnas estructuradas.

Los mensajes del proveedor no se exponen al frontend.

---

## 14. Marker de aprobación

Cuando una Purchase es observada como `APPROVED`, el backend intenta persistir inmediatamente un marker durable.

El marker incluye:

```text
provider_approved_at
provider
authorization_number
transaction_link_id
authorization_response_code
purchase_response_code
response_code = provider_approved
```

El marker:

- es write-once;
- sólo puede escribirse sobre una tarjeta `processing`;
- requiere evidencia de aprobación completa;
- nunca puede transformarse posteriormente en `rejected` o `error`;
- es la única autoridad local de aprobación remota.

### Escritura incierta del marker

El marker y los resultados terminales conocidos permiten recovery interno acotado, respetando las invariantes propias de cada transición:

```text
primer CAS no confirmado
→ abandonar la conexión
→ lectura con conexión nueva
```

Si la lectura encuentra un marker válido:

```text
→ ejecutar completion
```

Si encuentra `processing` sin marker y sin evidencia:

```text
→ ejecutar un único segundo CAS con la misma evidencia
```

Si el segundo CAS tampoco puede confirmarse:

```text
→ última lectura con conexión nueva
→ marker válido: completion
→ marker ausente: responder 202
```

No existe:

- recursividad;
- tercer intento;
- regreso al proveedor;
- inferencia terminal basada únicamente en memoria.

---

## 15. Completion local

La completion se ejecuta dentro de una única transacción de base de datos.

Orden de locks y operaciones:

```text
BEGIN
→ payment FOR UPDATE
→ registered FOR UPDATE
→ aplicar acceso VIP
→ marcar payment approved
→ crear user_event_jobs
→ COMMIT
```

El orden de locks es siempre:

```text
payment → registered
```

La transacción garantiza que:

- acceso VIP;
- `registered_id`;
- estado `approved`;
- jobs post-checkout;

se confirmen o reviertan juntos.

Una tarjeta no puede completarse sin marker.

Un cupón sólo puede completarse con:

- precio final `0.00`;
- cupón persistido;
- claim previo confirmado.

### Contexto del evento

El ledger conserva:

- `event_key`;
- `event_free_id`;
- `event_vip_id`;
- `event_phase`.

`event_key` identifica una familia estable dentro del catálogo de eventos configurado por EMMS.

Los IDs y la fase concreta provienen del payment durable.
La fase durable debe ser exactamente `pre`, `during` o `post`; cualquier valor
inválido falla antes del ledger y del proveedor. Completion y replay usan
exclusivamente la fase durable persistida.

`CheckoutTransactionStatus::isConsistent()` también valida `event_phase`; una
fila durable con una fase distinta de `pre`, `during` o `post` se considera
inconsistente y no puede publicarse mediante `get-payment`.

Los jobs de email generados por checkout persisten esa fase durable en
`form_id`. La resolución del subject y de la plantilla usa ese mismo snapshot,
de modo que una ejecución posterior no vuelve a depender de la fase global
activa del sistema.

Para `checkout_free_approved` y `checkout_vip_approved`, el handler de email
exige que `form_id` sea exactamente `pre`, `during` o `post`.

Para `checkout_free_approved`, `ticketType` debe estar ausente, ser `null` o
estar vacío. Un valor no vacío se rechaza antes del envío para impedir que
`EmailTemplateManager` seleccione una plantilla VIP por precedencia.

Para `checkout_vip_approved`, `ticketType` debe coincidir con `type` y fase:

- `type = ECOMMERCE`, `form_id = pre` → `ecommerceVipPre`;
- `type = ECOMMERCE`, `form_id = during` → `ecommerceVipDuring`;
- `type = ECOMMERCE`, `form_id = post` → `ecommerceVipPost`;
- `type = DIGITALTRENDS`, `form_id = pre` → `digitalTrendsVipPre`;
- `type = DIGITALTRENDS`, `form_id = during` → `digitalTrendsVipDuring`;
- `type = DIGITALTRENDS`, `form_id = post` → `digitalTrendsVipPost`.

Un job de checkout sin una fase durable válida, con un `ticketType` inesperado
para FREE o con un `ticketType` inconsistente para VIP falla antes de enviar el
email.

El subject ya se persiste como parte del snapshot producido por checkout y no
se recalcula en el handler. Así, la validación no vuelve a depender del mapping
de subjects vigente al momento de ejecutar un job diferido.

El fallback a la fase global de `EmailTemplateManager` queda reservado para
payloads de compatibilidad ajenos a esos eventos de checkout.

El significado de una familia, sus columnas de registro y su routing no deben modificarse mientras existan payments no terminales o payments con marker pendientes de completion.

Los cambios de evento se realizan con el checkout deshabilitado y sin operaciones activas.

---

## 16. Fallas e incertidumbre

Una conexión nueva o `fresh DB` significa una conexión independiente al mismo primary financiero, sin cache ni réplica eventual.

### Failure handler

Ante una excepción no resuelta, el failure handler:

```text
abre una conexión nueva
→ busca por paymentId
→ valida consistencia
→ responde desde el ledger
```

No ejecuta:

- rollback de una transacción ajena;
- provider;
- marker;
- completion;
- jobs;
- transiciones financieras.

### Terminales conocidos

`REJECTED` y `ERROR` realizan un recovery acotado dentro del processor:

```text
primer CAS terminal
→ recarga del ledger
→ si el ledger ya es terminal consistente, gana el ledger
→ si sigue processing sin marker ni evidencia, segundo CAS único
→ última recarga
```

El recovery no vuelve al proveedor y no convierte un marker aprobado en `rejected` o `error`.

### Resultado remoto ambiguo

Cuando el resultado es `UNKNOWN`:

- el payment permanece `processing`;
- no se persiste evidencia financiera parcial en el ledger;
- no se vuelve a llamar al proveedor para esa `paymentId`;
- no se convierte en `rejected`;
- no se convierte en `error`;
- la respuesta pública es `202`.

La investigación puede utilizar:

- `paymentId`;
- `correlationId`;
- ledger;
- logs de EMMS;
- logs de Doppler Payments API.

El evento estructurado `payment_provider_call_finished` conserva, cuando están
disponibles, `authorization_response_code`, `purchase_response_code` y
`transaction_link_id`. Estos campos permiten distinguir un código financiero
no catalogado de un error técnico genérico sin persistir evidencia parcial en
el ledger ni convertir el resultado en terminal.

La investigación es read-only y no autoriza reconstruir o terminalizar el outcome mediante heurísticas.

Mientras una `paymentId` permanezca ambigua, soporte no debe crear ni recomendar otra `paymentId` para reemplazar automáticamente esa misma intención.

V1 no incorpora:

- escritura manual del marker;
- completion manual;
- mutación SQL del outcome;
- consulta contractual de outcome remoto;
- retry financiero administrativo.

---

## 17. Efectos post-checkout

Los efectos post-checkout incluyen:

- envío de email;
- registro en spreadsheet;
- agregado a listas.

Los jobs se crean dentro del mismo commit que:

- aplica VIP;
- persiste `registered_id`;
- marca el payment como `approved`.

Por lo tanto, un payment `approved` garantiza que los jobs fueron persistidos.

### Ejecución inline

Después del commit, el sistema registra un `shutdown function` que intenta ejecutar los jobs pendientes mediante una conexión nueva.

La ejecución es best effort.

Una falla del runner:

- no revierte el pago;
- no revierte el acceso VIP;
- no cambia el estado `approved`;
- no vuelve a llamar al proveedor.

### Límite de recuperación

Los jobs son durables y auditables, pero V1 no garantiza su reejecución posterior.

V1 no incluye:

- worker;
- cron;
- scheduler;
- CLI de reejecución;
- endpoint administrativo;
- reclaim automático de jobs `processing`;
- retry automático de jobs `failed`.

Si el proceso termina después del commit y antes de ejecutar el shutdown, los jobs pueden permanecer `pending`.

Este riesgo no redefine el estado financiero del payment.

---

## 18. Superficie pública

La superficie pública es:

```text
/checkout
/checkout-success?payment_id=...
/services/calculate-payment.php
/services/create-payment.php
/services/get-payment.php
```

### `calculate-payment`

Resuelve:

- ticket;
- cupón;
- precio base;
- descuento;
- precio final;
- moneda;
- necesidad de pago con tarjeta.

No crea un intento financiero.

La request de `calculate-payment` valida y normaliza:

- `couponCode` como string o `null`;
- `customerEmail` como string o `null` y de hasta 250 caracteres;
- `origin` como string o scalar compatible como hasta ahora; si no viene o viene vacío, se normaliza a `checkout`; si viene `null`, se rechaza con `422 validation_error`.

Valores con tipos inválidos producen `422 validation_error` antes de lookup o pricing.

### `create-payment`

| Resultado              | HTTP |
| ---------------------- | ---: |
| `approved`             |  200 |
| `rejected`             |  200 |
| `error`                |  200 |
| `processing`           |  202 |
| `already_vip` durable  |  200 |
| intención incompatible |  409 |
| request inválida       |  422 |
| inconsistencia interna |  500 |

### `get-payment`

`get-payment`:

- recibe `payment_id`;
- responde `400 payment_id_required` cuando `payment_id` falta o está vacío;
- responde `422 validation_error` cuando `payment_id` existe pero no es un string;
- valida que sea UUID v4;
- normaliza la UUID;
- busca solamente en el ledger;
- no llama al proveedor;
- no ejecuta completion;
- no crea jobs;
- no modifica el payment;
- utiliza `Cache-Control: no-store`.

Respuestas:

| Resultado           | HTTP |
| ------------------- | ---: |
| fila consistente    |  200 |
| UUID inválida       |  422 |
| payment inexistente |  404 |
| fila inconsistente  |  500 |

---

## 19. Proyección pública del payment

Las respuestas construidas desde una fila válida pueden exponer:

```json
{
  "success": true,
  "status": "approved",
  "payment": {
    "paymentId": "0b8dbecd-1657-4bf5-b84e-a2805da9f189",
    "status": "approved",
    "finalAmount": "100.00",
    "currency": "USD",
    "ticketName": "VIP",
    "paymentMethod": "card",
    "createdAt": "2026-07-31 10:00:00"
  },
  "correlationId": "corr_..."
}
```

Cuando corresponde, la respuesta incluye un error público allowlisted.

No se exponen:

- email;
- nombre;
- teléfono;
- IP;
- token de pago;
- códigos internos de Authorization o Purchase;
- número de autorización;
- transaction link;
- raw requests;
- raw responses;
- mensajes del proveedor;
- jobs internos;
- stack traces.

---

## 20. Comportamiento del frontend

### Creación del intento

El frontend mantiene en memoria un `activeAttempt`:

```js
{
  paymentId,
  serializedBody,
  customerEmail,
  correlationId,
  approvedFinished,
}
```

Para tarjeta:

1. adquiere el lock local del submit;
2. tokeniza mediante eProtect;
3. genera una UUID con `crypto.randomUUID()`;
4. construye y serializa la request completa;
5. ejecuta un único POST automático.

Dos submits concurrentes comparten la misma promesa activa.

### Recuperación automática

Ante:

- timeout;
- error de red;
- HTTP 5xx;
- HTTP 202;

el frontend consulta `get-payment` de forma acotada.

No ejecuta un segundo POST automático.

### Retry manual

Mientras `activeAttempt` permanezca en memoria, una acción manual reutiliza:

- la misma `paymentId`;
- el mismo body serializado;
- el mismo token;
- la misma intención.

No vuelve a tokenizar ni genera otra operación.

Una recarga completa del navegador pierde `activeAttempt`. V1 acepta ese límite y no persiste el instrumento ni la request completa en storage.

### Resultado terminal

Sólo `approved` permite redirigir a success.

`rejected` y `error` liberan el intento activo y muestran un mensaje local correspondiente a la categoría pública.

`processing` mantiene la referencia de la operación y no genera una compra nueva automáticamente.

---

## 21. Success y tracking

La success page recibe:

```text
payment_id
```

y consulta `get-payment`.

La página:

- muestra comprobante sólo si el ledger indica `approved`;
- no consulta Doppler Payments API;
- no ejecuta completion;
- no modifica el estado financiero;
- utiliza `Cache-Control: no-store`;
- utiliza `Referrer-Policy: no-referrer`.

Después de confirmar `approved`:

- renderiza el comprobante;
- actualiza los eventos locales correspondientes;
- ejecuta tracking de conversión deduplicado por `paymentId`.

El tracking es best effort.

Una falla de analytics, storage o tracking no modifica el estado del payment ni debe impedir mostrar una aprobación durable.

---

## 22. Seguridad y privacidad

Checkout Payments V1 aplica las siguientes reglas:

- no persistir PAN ni CVV;
- no persistir tokens de eProtect;
- no persistir tokenized PAN;
- no persistir bodies completos del proveedor;
- no exponer evidencia financiera en respuestas públicas;
- no registrar secretos ni instrumentos en logs;
- limitar `raw_request` a campos allowlisted;
- mantener mensajes públicos desacoplados de mensajes remotos;
- utilizar `correlationId` y `paymentId` para trazabilidad.

`Logger::event()` es best effort: una falla de observabilidad no debe alterar el resultado financiero ni romper el flujo principal.

---

## 23. Cutover

Checkout Payments V1 reemplaza destructivamente el ledger anterior del checkout no publicado.

No existe:

- migración incremental;
- dual-read;
- dual-write;
- compatibilidad temporal entre ambos schemas;
- preservación de transacciones de una versión anterior no activa.

Procedimiento obligatorio:

```text
deshabilitar checkout
→ drenar requests PHP
→ verificar cero operaciones activas
→ desplegar código y configuración compatibles sin tráfico
→ eliminar user_event_jobs de aggregate_type checkout_transaction
→ recrear payment_transactions
→ reaplicar seeds
→ ejecutar validaciones y smoke controlado
→ habilitar checkout
```

La limpieza de `user_event_jobs` debe ocurrir antes de reiniciar la secuencia de `payment_transactions`, porque la identidad idempotente de esos jobs utiliza el ID interno de la transacción.

Después del reset no puede ejecutarse código anterior contra el schema nuevo.

Ante una falla de despliegue, el checkout permanece deshabilitado hasta restaurar una pareja compatible de código y schema.

---

## 24. Riesgos aceptados

V1 acepta explícitamente los siguientes límites:

- Dos `paymentId` diferentes pueden representar y cobrar dos intentos del mismo usuario.
- Un refresh puede perder el intento activo del navegador.
- Un payment puede quedar indefinidamente `processing` sin marker.
- Una falla después del claim y antes de llamar al proveedor no tiene reclaim automático.
- Un resultado remoto ambiguo no se terminaliza mediante heurísticas.
- Los jobs pueden quedar `pending` si el proceso termina después del commit y antes del runner.
- La completion tardía depende de que la familia y las columnas del evento permanezcan estables.
- Doppler Payments API no ofrece para V1 una garantía downstream de idempotencia que EMMS pueda asumir.
- V1 prioriza no duplicar un cobro antes que recuperar automáticamente un intento ambiguo.

Estos límites deben tratarse como decisiones de producto y operación, no como autorización para modificar el ledger manualmente.

---

## 25. Criterios de validación

Antes de habilitar Checkout Payments V1 debe verificarse, como mínimo:

### Identidad e idempotencia

- una `paymentId` crea una sola fila;
- dos requests concurrentes con la misma `paymentId` no llaman dos veces al proveedor;
- una intención incompatible devuelve `409`;
- una operación terminal se responde desde el ledger;
- una operación `processing` sin marker devuelve `202`.

### Provider

- Authorization `401` o `403` terminan en `ERROR`;
- Authorization aprobada permite Purchase;
- Authorization HTTP 200 con código de rechazo catalogado y
  `transactionLinkID` string termina en `rejected` y conserva el transaction
  link en observabilidad;
- Authorization HTTP 200 con código no catalogado y `transactionLinkID` string
  termina en `UNKNOWN` y conserva el transaction link en observabilidad;
- Authorization HTTP 200 con rechazo catalogado y `transactionLinkID` no string
  conserva `rejected` y omite el transaction link inválido;
- Authorization HTTP 200 con `responseCode = 000` y `transactionLinkID` no
  string termina en `UNKNOWN`;
- Authorization HTTP 400 con `PaymentError` catalogado se acepta
  defensivamente como `rejected`;
- Authorization HTTP 400 con `PaymentError` no catalogado se acepta
  defensivamente como `UNKNOWN`;
- rechazo incluido en el catálogo termina en `rejected`;
- código no incluido termina en `UNKNOWN`;
- DNS o conexión fallida antes de Authorization terminan en `ERROR`;
- timeout, redirect, `5xx` y JSON inválido terminan en `UNKNOWN`;
- cualquier falla durante Purchase después de Authorization `000` termina en `UNKNOWN`;
- Purchase `000` sin número de autorización no se considera aprobada;
- no existen retries automáticos de cURL.
- `ccType` fuera de `1`, `2` o `3` falla antes del intento financiero.

### Marker y completion

- una tarjeta no completa sin marker;
- el marker es write-once;
- el recovery del marker realiza como máximo un segundo CAS;
- `REJECTED` conocido hace un segundo CAS acotado si la persistencia inicial falla;
- `ERROR` demostrado antes de cualquier operación financiera remota hace un segundo CAS acotado si la persistencia inicial falla;
- el recovery nunca vuelve al proveedor;
- VIP, payment y jobs participan del mismo commit;
- una falla de commit no deja `approved`;
- un replay con marker ejecuta únicamente completion.

### Cupones

- un cupón válido aplica el descuento;
- un cupón del 100 % no llama al proveedor;
- `couponCode` se canonicaliza como `trim + uppercase`;
- la request inválida de cupón responde `422` y no crea ledger;
- exactamente un ticket activo por evento habilita el checkout;
- el ticket activo tiene precio base estrictamente positivo;
- una completion de cupón exige precio final cero y cupón durable.

### Efectos post-checkout

- `checkout_free_approved` sin `form_id` válido falla antes del envío;
- `checkout_free_approved` con `ticketType` no vacío falla antes del envío;
- `checkout_vip_approved` sin `form_id` válido falla antes del envío;
- `checkout_vip_approved` con `ticketType` contradictorio respecto de `type` y
  fase falla antes del envío;
- un job VIP con `type`, `form_id` y `ticketType` coherentes se envía
  normalmente;
- un email ajeno a checkout sin `form_id` conserva el fallback a la fase global.

### Frontend y success

- el doble click comparte una sola operación;
- existe un único POST automático;
- la recuperación automática utiliza GET;
- el retry manual conserva UUID y body;
- sólo `approved` redirige a success;
- success no ejecuta acciones financieras;
- las respuestas públicas no exponen PII ni evidencia del proveedor.

### Operación

- el checkout se deshabilita antes del reset;
- no existen operaciones activas durante el cambio;
- se limpian los jobs de checkout antes de recrear el ledger;
- código y schema se habilitan como una única unidad compatible.

Este documento concentra el contrato funcional y técnico de V1, incluyendo sus límites operativos y de cutover.
