<div id="modalRegister" class="emms__register-modal">
    <div class="emms__register-modal__window">
        <!-- Form -->
        <?php
        $formTitle = 'Regístrate aquí 🙂';
        $formSubTitle = 'Desbloquea la Biblioteca de Recursos, accede a conferencias de años anteriores y súmate gratis a la edición 2025.';
        $eventType = ECOMMERCE;
        ?>
        <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/register-form-component.php'); ?>
        <!-- End form -->
        <button class="emms__register-modal__window__close" data-dismiss="emms__register-modal"></button>
    </div>
</div>
