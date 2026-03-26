<div id="certificateModal" class="emms__certificate-modal">
    <div class="emms__certificate-modal__window">
        <img src="src/img/certificate-image.png" alt="Imagen descarga certificado">
        <h3>¡Tu Certificado de Asistencia ya está disponible!</h3>
        <p>Ingresa tu nombre y apellido para obtenerlo ahora 🙂</p>
        <form id="certificateForm">
            <input type="text" placeholder="Ingresa aquí tu Nombre y Apellido" name="fullname">
            <span class="certificateError">¡Ouch! Debes ingresar al menos 2 caracteres.</span>
            <a class="emms__cta" type="button" id="certificateCta"><span class="button__text">DESCARGA TU CERTIFICADO</span></a>
            <button class="emms__certificate-modal__window__close" data-dismiss="emms__certificate-modal"></button>
        </form>
    </div>
</div>
<script type="module" src="components/digital-trends/during/digital-trends/certificate/certificateModal.js?version=<?= VERSION ?>"></script>
