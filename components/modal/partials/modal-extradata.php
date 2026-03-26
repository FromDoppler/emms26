<div class="popup-modal__form-inner">
  <img
    class="popup-modal__form-image"
    src="/src/img/modals/jump-women.png"
    alt="Mujer saltando"
    aria-hidden="true" />
  <div class="popup-modal__form-content">

    <header class="popup-modal__form-head">
      <h3 id="<?= $titleId ?>" class="popup-modal__title">¡Ya eres parte del Evento! 🚀</h3>
      <p class="popup-modal__text">
        Queremos que vivas una experiencia 100% personalizada. Completa los siguientes datos para que podamos ofrecerte contenidos pensados para ti.
      </p>
    </header>

    <form class="popup-modal__form-fields" id="formExtraData">
      <label class="popup-modal__f-group">
        <span class="popup-modal__f-label">Cargo</span>
        <input class="popup-modal__input" type="text" name="jobPosition" placeholder="Escribe aquí" />
      </label>

      <label class="popup-modal__f-group">
        <span class="popup-modal__f-label">Industria</span>
        <div class="popup-modal__select-wrap">
          <select class="popup-modal__select" name="industry">
            <option disabled value="" selected>Seleccione una opción</option>

            <optgroup label="Retail (Venta Minorista)">
              <option value="retail_alimentos">Retail (Venta Minorista) | Alimentos</option>
              <option value="retail_electrodomesticos">Retail (Venta Minorista) | Electrodomésticos y Electrónica</option>
              <option value="retail_indumentaria">Retail (Venta Minorista) | Indumentaria, Belleza y Accesorios</option>
              <option value="retail_hogar">Retail (Venta Minorista) | Hogar y Decoración</option>
              <option value="retail_juguetes">Retail (Venta Minorista) | Juguetes</option>
              <option value="retail_supermercado">Retail (Venta Minorista) | Supermercado</option>
              <option value="retail_otros">Retail (Venta Minorista) | Otros</option>
            </optgroup>

            <optgroup label="Educación">
              <option value="educacion">Educación</option>
            </optgroup>

            <optgroup label="Servicios Profesionales">
              <option value="servicios_profesionales">Servicios Profesionales</option>
            </optgroup>

            <optgroup label="Salud y Deportes">
              <option value="salud_servicios_instituciones">Salud y Deportes | Servicios e Instituciones</option>
              <option value="salud_medicina">Salud y Deportes | Medicina</option>
              <option value="salud_belleza">Salud y Deportes | Belleza</option>
              <option value="salud_deportes">Salud y Deportes | Deportes</option>
              <option value="salud_mental">Salud y Deportes | Salud Mental</option>
              <option value="salud_otros">Salud y Deportes | Otros</option>
            </optgroup>

            <optgroup label="Servicios de Construcción e Inmobiliarios">
              <option value="construccion_inmobiliaria">Servicios de Construcción e Inmobiliarios</option>
            </optgroup>

            <optgroup label="Banca / Finanzas / Seguros">
              <option value="banca_finanzas_seguros">Banca / Finanzas / Seguros</option>
            </optgroup>

            <optgroup label="Turismo">
              <option value="turismo">Turismo</option>
            </optgroup>

            <optgroup label="Agencias y Freelancers">
              <option value="agencias_comunicacion">Agencias y Freelancers | Comunicación</option>
              <option value="agencias_publicidad">Agencias y Freelancers | Publicidad</option>
              <option value="agencias_medios">Agencias y Freelancers | Medios</option>
              <option value="agencias_marketing">Agencias y Freelancers | Marketing</option>
              <option value="agencias_otros">Agencias y Freelancers | Otros</option>
            </optgroup>

            <optgroup label="Cultura, Entretenimiento y Ocio">
              <option value="cultura_entretenimiento">Cultura, Entretenimiento y Ocio</option>
            </optgroup>

            <optgroup label="Tecnología e Internet">
              <option value="tecnologia_internet">Tecnología e Internet</option>
            </optgroup>

            <optgroup label="Industria Y Productores">
              <option value="industria_productores">Industria Y Productores</option>
            </optgroup>

            <optgroup label="Gastronomía">
              <option value="gastronomia">Gastronomía</option>
            </optgroup>

            <optgroup label="Automotor">
              <option value="automotor">Automotor</option>
            </optgroup>

            <optgroup label="Medios de Comunicación">
              <option value="medios_comunicacion">Medios de Comunicación</option>
            </optgroup>

            <optgroup label="Transporte y Logística">
              <option value="transporte_logistica">Transporte y Logística</option>
            </optgroup>

            <optgroup label="Agricultura">
              <option value="agricultura">Agricultura</option>
            </optgroup>

            <optgroup label="Gobierno">
              <option value="gobierno">Gobierno</option>
            </optgroup>

            <optgroup label="Mascotas">
              <option value="mascotas">Mascotas</option>
            </optgroup>

            <optgroup label="Servicios de Telecomunicaciones">
              <option value="telecomunicaciones">Servicios de Telecomunicaciones</option>
            </optgroup>

            <optgroup label="Empresas de Energía Y Servicios Públicos">
              <option value="energia_servicios">Empresas de Energía Y Servicios Públicos</option>
            </optgroup>

            <optgroup label="Venta Mayorista">
              <option value="venta_mayorista">Venta Mayorista</option>
            </optgroup>

            <optgroup label="Hotelería Y Catering">
              <option value="hoteleria_catering">Hotelería Y Catering</option>
            </optgroup>

            <optgroup label="Sin Fines de Lucro y Caridad">
              <option value="sin_fines_lucro">Sin Fines de Lucro y Caridad</option>
            </optgroup>
          </select>

          <span class="popup-modal__chev" aria-hidden="true">▾</span>
        </div>
      </label>


      <label class="popup-modal__f-group">
        <span class="popup-modal__f-label">Sitio Web</span>
        <input class="popup-modal__input" type="text" name="website" placeholder="Escribe aquí" />
      </label>

      <label class="popup-modal__f-group">
        <span class="popup-modal__f-label">¿Utilizas alguna plataforma de Automatización del Marketing?</span>
        <div class="popup-modal__select-wrap">
          <select class="popup-modal__select" name="emailPlatform">
            <option value="" hidden>Selecciona</option>
            <option value="doppler">Doppler</option>
            <option value="brevo">Brevo</option>
            <option value="active_campaign">Active Campaign</option>
            <option value="mailchimp">Mailchimp</option>
            <option value="rd_station">RD Station</option>
            <option value="marketo">Marketo</option>
            <option value="icomm">Icomm</option>
            <option value="connectif">Connectif</option>
            <option value="emblue">Emblue</option>
            <option value="hubspot">Hubspot</option>
            <option value="otro">Otro</option>
          </select>
          <span class="popup-modal__chev" aria-hidden="true">▾</span>
        </div>
      </label>


      <div class="popup-modal__form-actions">
        <button class="emms__cta emms__cta--terciary emms__cta--xl"><span class="button__text">ENVIAR</span></button>
        <button class="popup-modal__btn-link" type="button" data-modal-close>OMITIR ESTE PASO</button>
      </div>
    </form>
  </div>
</div>
<?php if (!defined('EMMS_COMMONFORM_JS_INCLUDED')) {
  define('EMMS_COMMONFORM_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/commonForm.js"></script>
<?php } ?>
