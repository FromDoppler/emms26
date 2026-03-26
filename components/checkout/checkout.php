<div class="emms__checkout">
    <div class="loader-page--new" id="spinner">
        <img src="/src/img/logoemms-nobg.png" class="loader-goemms" alt="Loader goemms">
    </div>
    <div class="emms__checkout__container emms__checkout__card__container--form emms__fade-in">
        <div class="emms__checkout__card">
            <div id="checkout"></div>
        </div>
        <a href="./digital-trends-registrado.php" class="emms__checkout__back">← Volver al sitio</a>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
const events = JSON.parse(localStorage.getItem("events") || "[]");
if (events.includes(window.APP.EVENTS.EVENTCODES.DIGITALTRENDSVIP)) {
  window.location.href = "/";
}
const stripe = Stripe(`<?= STRIPE_PUBLIC_KEY; ?>`);
const spinner = document.getElementById('spinner');

function showSpinner(show) {
    spinner.classList.toggle('visible', show);
}

function hexToString(hex) {
    try {
        let str = '';
        for (let i = 0; i < hex.length; i += 2) {
            str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
        }
        return str;
    } catch {
        return null;
    }
}

function getCustomerEmail() {
    const urlParams = new URLSearchParams(window.location.search);
    const hex = localStorage.getItem('dplrid') || urlParams.get('dplrid');
    const decoded = hexToString(hex);
    if (!decoded) return null;
    try {
        return JSON.parse(decoded)?.userEmail || decoded;
    } catch {
        return decoded;
    }
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
        const customerEmail = getCustomerEmail();
        const { promotionCode, utmParams } = getUtmParams();

        const response = await fetch(`/services/create-checkout-session`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                customerEmail,
                ...(promotionCode && { promotionCode }),
                ...utmParams
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

(async () => {
    showSpinner(true);
    await initialize();
    showSpinner(false);
})();
</script>
