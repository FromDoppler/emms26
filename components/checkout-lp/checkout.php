<div class="emms__checkout">
    <div class="loader-page--new" id="spinner">
        <img src="/src/img/logoemms-nobg.png" class="loader-goemms" alt="Loader goemms">
    </div>
    <div class="emms__checkout__container emms__checkout__card__container--form emms__fade-in">
        <div class="emms__checkout__card">
            <div id="checkout"></div>
        </div>
        <a href="./checkout-lp-landing" class="emms__checkout__back">← Volver al sitio</a>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
(async () => {
function showSpinner(show) {
    spinner.classList.toggle('visible', show);
}

function getUtmParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const promotionCode = urlParams.get('promotionCode');
    const utmParams = {};

    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'origin']
        .forEach(param => {
            const value = urlParams.get(param);
            if (value) utmParams[param] = value;
        });

    return { promotionCode, utmParams };
}

async function initialize() {
    try {
        const { promotionCode, utmParams } = getUtmParams();

        const response = await fetch(`/services/create-checkout-session`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ...(promotionCode && { promotionCode }),
                ...utmParams,
                source: 'LP'
            })
        });

        if (!response.ok) {
            throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
        }

        const { clientSecret } = await response.json();

        const checkout = await stripe.initEmbeddedCheckout({ clientSecret });
        checkout.mount('#checkout');
    } catch (error) {
        console.error("Error al inicializar el checkout:", error);
        window.location.href = '/';
    }
}

function verifyUser(event) {
  const events = JSON.parse(localStorage.getItem("events") || "[]");
  if (events.includes(event)) {
    window.location.href = "/";
    return false;
  }
  return true;
}

function devMode() {
  document.getElementById('checkout').innerHTML = `
    <div style="padding:40px;text-align:center;">
      <h2 style="color:#666;margin-bottom:20px;">Checkout temporalmente no disponible</h2>
      <p style="color:#999;margin-bottom:30px;">Somos el evento mas grande de LATAM.</p>
      <a href="/checkout-lp-success" style="display:inline-block;padding:12px 24px;background:#5469d4;color:white;text-decoration:none;border-radius:4px;margin:10px;">Ver página de éxito</a>
    </div>
  `;
  return true;
}


    // return devMode();
    if (!verifyUser(window.APP.EVENTS.EVENTCODES.DIGITALTRENDSVIP)) return;
    const stripe = Stripe(`<?= STRIPE_PUBLIC_KEY; ?>`);
    const spinner = document.getElementById('spinner');
    showSpinner(true);
    await initialize();
    showSpinner(false);
})();
</script>
