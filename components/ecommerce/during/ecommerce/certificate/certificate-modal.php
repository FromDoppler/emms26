<div id="certificateModal" class="emms__certificate-modal">
    <div class="emms__certificate-modal__window">
        <img src="src/img/certificate-image.png" alt="Imagen descarga certificado">
        <h3>¡Estás a un paso de descargar tu Certificado de Asistencia!</h3>
        <p>Ingresa tu nombre y apellido para descargarlo ahora 🙂</p>
        <form id="certificateForm">
            <input type="text" placeholder="Ingresa aquí tu Nombre y Apellido" name="fullname">
            <span class="certificateError">¡Ouch! Debes ingresar al menos 2 caracteres.</span>
            <a class="emms__cta" type="button" id="certificateCta"><span class="button__text">DESCÁRGALO AQUI</span></a>
            <button class="emms__certificate-modal__window__close" data-dismiss="emms__certificate-modal"></button>
        </form>
    </div>
</div>
<script type="module" src="components/ecommerce/during/ecommerce/certificate/certificateModal.js?version=<?= VERSION ?>"></script>
