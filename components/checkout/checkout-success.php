<div class="loader-page--new visible" id="spinner">
  <img src="/src/img/logoemms-nobg.png" class="loader-goemms" alt="Loader goemms">
</div>

<div class="emms__checkout">
  <div class="emms__checkout__container emms__checkout__card__container--form emms__fade-in" id="checkout-container" style="display:none;">
    <div class="emms__checkout__card">
      <img src="/src/img/logos/logo-emms-gray.png" alt="Emms Digital Trends 25">
      <section id="success" class="hidden">
        <div class="emms__checkout__container emms__checkout__card__container--success emms__fade-in">
          <div class="emms__checkout__card">
            <div class="emms__checkout__card__main">
              <h2>¡Felicitaciones!</h2>
              <h2>Ya eres Asistente VIP 🚀</h2>
              <p>Mantente pendiente de tu Correo Electrónico porque en instantes recibirás un Email de Confirmación por la compra de tu acceso.</p>
            </div>
            <div class="emms__checkout__card__aside">
              <h3>Detalle de tu compra</h3>
              <table>
                <tr>
                  <td>Titular:</td>
                  <td>
                    <div id="customerName"></div>
                  </td>
                </tr>
                <tr>
                  <td>Categoría:</td>
                  <td>
                    <div id="ticketName"></div>
                  </td>
                </tr>
                <tr>
                  <td>Medio de pago:</td>
                  <td>Tarjeta de Crédito</td>
                </tr>
                <tr>
                  <td>Fecha de compra:</td>
                  <td>
                    <div id="date"></div>
                  </td>
                </tr>
                <tr>
                  <td>Monto:</td>
                  <td>
                    <div id="amount"></div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <a href="./digital-trends-registrado" class="emms__checkout__back">← Volver al sitio</a>
        </div>
      </section>
    </div>
    <a href="./digital-trends-registrado" class="emms__checkout__back">← Volver al sitio</a>
  </div>
</div>

<script>
  const toggleSpinner = (show) => {
    const spinner = document.getElementById('spinner');
    if (show) spinner.classList.add('visible');
    else spinner.classList.remove('visible');
  };

  const showCheckoutContainer = (show) => {
    document.getElementById('checkout-container').style.display = show ? 'block' : 'none';
  };

  const updateEvents = () => {
    try {
      const vipId = window.APP.EVENTS.CURRENT.vipId;
      const existingEvents = JSON.parse(localStorage.getItem('events')) || [];
      if (!existingEvents.includes(vipId)) {
        existingEvents.push(vipId);
        localStorage.setItem('events', JSON.stringify(existingEvents));
      }
    } catch {
      localStorage.clear();
    }
  };

  (async function initSuccess() {
    toggleSpinner(true);

    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session_id');


    try {
      const response = await fetch(`/services/fetch-session-status.php?${urlParams.toString()}`);
      if (!response.ok) throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
      const session = await response.json();

      if (session.status === 'complete') {
        document.getElementById('customerName').textContent = session.customer_details.customer_name;
        document.getElementById('date').textContent = session.customer_details.date;
        document.getElementById('amount').textContent = `${session.customer_details.currency} ${session.customer_details.final_price}`;
        document.getElementById('ticketName').textContent = session.customer_details.ticket_name;

        document.getElementById('success').classList.remove('hidden');
        updateEvents();
        showCheckoutContainer(true);
      } else {
        throw new Error('Sesión incompleta');
      }
    } finally {
      toggleSpinner(false);
    }
  })();
</script>
