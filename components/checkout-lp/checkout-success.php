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
              <p>En instantes recibirás un Email de Confirmación con los <br> detalles de tu compra.</p>
              <h4>Con tu pase premium accedes <br>
                a estos beneficios:</h4>
              <ul class="emms__checkout__card__main__list">
                <li>Workshops en vivo con referentes internacionales.</li>
                <li>Talleres prácticos on-demand de EMMS anteriores.</li>
                <li>Cuenta gratuita en Doppler por 6 meses (válido para <br> cuentas nuevas).</li>
                <li>Recursos y materiales exclusivos para tu negocio.</li>
              </ul>
              <small>¡Y mucho más!</small>
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

<script type="module">
  import { trackMetaPixel } from '/src/<?= VERSION ?>/js/common/submitHelpers.js';

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
      const {
        vipId,
        freeId
      } = window.APP.EVENTS.CURRENT;
      const existingEvents = JSON.parse(localStorage.getItem('events')) || [];

      // Usamos un Set para evitar duplicados
      const updatedEvents = new Set(existingEvents);

      if (vipId) updatedEvents.add(vipId);
      if (freeId) updatedEvents.add(freeId);

      localStorage.setItem('events', JSON.stringify([...updatedEvents]));
    } catch (error) {
      console.error('Error updating events:', error);
      localStorage.clear();
    }
  };


  // Genera dplrid en localStorage si no existe, basado en email y eventos actuales
  function toHex(str) {
    return Array.from(new TextEncoder().encode(str))
      .map(b => b.toString(16).padStart(2, '0'))
      .join('');
  }

  function getCustomerEmailFromSession(session) {
    return session.customer_details.customer_email;
  }

  function createDplrIdIfNeeded(email) {
    try {
      if (!email) return;
      if (localStorage.getItem('dplrid')) return;
      const encodeEmail = toHex(email);
      localStorage.setItem('dplrid', encodeEmail);
    } catch (e) {
      console.error('Error creating dplrid:', e);
    }
  }

  const devMode = () => {
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('customerName').textContent = 'Usuario de Prueba';
    document.getElementById('date').textContent = new Date().toLocaleDateString('es-AR');
    document.getElementById('amount').textContent = 'USD 99';
    document.getElementById('ticketName').textContent = 'VIP Pass';

    document.getElementById('success').classList.remove('hidden');
    updateEvents();
    createDplrIdIfNeeded('test@example.com');

    showCheckoutContainer(true);
    toggleSpinner(false);
    return;
  }

  (async function initSuccess() {
    // return devMode();
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
        const customerEmail = getCustomerEmailFromSession(session);
        createDplrIdIfNeeded(customerEmail);

        if (session.is_first_time) {
          trackMetaPixel("EMMS_VIP");
        }

        showCheckoutContainer(true);
      } else {
        throw new Error('Sesión incompleta');
      }
    } catch (error) {
      console.error('Error en la inicialización del checkout:', error);
      showCheckoutContainer(false);
    } finally {
      toggleSpinner(false);
    }
  })();
</script>
