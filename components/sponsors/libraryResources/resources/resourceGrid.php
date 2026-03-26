<?php
include 'resourceCard.php';

$resourcesJson = '[
    {
        "image": "/src/img/resources/resource-conversaciones.png",
        "tag": "RECURSO EXCLUSIVO",
        "title": "E-book: De clics a conversiones",
        "description": "Aprende a crear Estrategias eficaces para impulsar tus ventas, optimizar tu tienda online y mejorar el proceso de compra de tus clientes.",
        "downloadText" : "DESCARGAR EL E-BOOK →",
        "downloadUrl": "https://www.fromdoppler.com/wp-content/uploads/2024/06/recurso-emplifi.pdf"
    },
    {
        "image": "/src/img/resources/resource-ia.png",
        "tag": "RECURSO EXCLUSIVO",
        "title": "Buenas prácticas de IA que tu E-commerce necesita",
        "description": "Descarga este recurso y aprende consejos eficaces para sumar a tu estrategia y un bonus de herramientas recomendadas para optimizar tiempos en tu E-commerce.",
        "downloadText" : "DESCARGAR INFOGRAFÍA →",
        "downloadUrl": "https://www.fromdoppler.com/wp-content/uploads/2024/04/EMMS-Buenas-practicas-de-IA-que-tu-E-commerce-necesita.pdf"
    },
    {
        "image": "/src/img/resources/resource-email-mkt.png",
        "tag": "RECURSO EXCLUSIVO",
        "title": "Email Marketing para fechas de alto volumen de ventas",
        "description": "Aprende en este e-book las claves principales para que tu negocio convierta Leads en ventas concretas durante fechas especiales como Cyber Monday, Buen Fin o Hot Sale.",
        "downloadText" : "DESCARGAR EL E-BOOK →",
        "downloadUrl": "https://www.fromdoppler.com/wp-content/uploads/2024/03/ebook-altovolumen-ventas.pdf"
    },
    {
        "image": "/src/img/resources/resource-guia.png",
        "tag": "RECURSO EXCLUSIVO",
        "title": "Guía de Email y Automation Marketing",
        "description": "Descárgate esta guía diseñada para ayudarte a alcanzar a tus Contactos y potenciar tu negocio de forma práctica junto a Doppler.",
        "downloadText" : "DESCARGAR HERRAMIENTA →",
        "downloadUrl": "https://www.fromdoppler.com/wp-content/uploads/2023/07/20230629-guia-automation-marketing.pdf"
    }
]';

$resources = json_decode($resourcesJson, true);
?>


<section class="resources-grid">
    <div class="emms__container--lg">
        <header class="resources-grid__header">
            <h2 class="resources-grid__title">
                Aprovecha estos recursos exclusivos que obtienes por ser parte del evento
            </h2>
        </header>
        <ul class="resources-grid__content">
            <?php foreach ($resources as $resource) {
                renderResourceCard($resource, $isRegistered);
            } ?>
        </ul>
    </div>
</section>
