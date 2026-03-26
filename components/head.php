<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<meta name="keywords" content="">
<meta name="language" content="Spanish">
<meta name="revisit-after" content="15 days">
<meta name="author" content="Doppler">

<meta property="og:type" content="website" />

<meta name="twitter:card" content="summary_large_image">
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />

<link rel="apple-touch-icon" sizes="180x180" href="common/html/img/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/src/img/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/src/img/favicon-16x16.png">

<!-- Flickity -->
<link rel="stylesheet" href="/src/<?= VERSION ?>/flickity/flickity.min.css">

<link rel="stylesheet" href="/src/<?= VERSION ?>/css/styles.css">

<!-- IntelInput -->
<link rel="stylesheet" href="/src/<?= VERSION ?>/css/components/intelInput.css">

<!-- Font Proxima Nova -->
<link rel="stylesheet" href="https://use.typekit.net/fbq8dbp.css" />

<!-- Font Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&display=swap" rel="stylesheet">

<!-- El aviso de consentimiento de cookies de OneTrust comienza para goemms.com. -->
<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="02d37671-cb77-4e6a-804f-9955eb1f7c97<?= (PRODUCTION) ? '' : '-test' ?>"></script>
<script type="text/javascript">
  function OptanonWrapper() {}
</script>
<!-- El aviso de consentimiento de cookies de OneTrust finaliza para goemms.com. -->
<!-- Google Tag Manager -->
<?php
if (PRODUCTION) {
?>
<script>
  (function(w, d, s, l, ids) {
    w[l] = w[l] || [];
    w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

    Object.values(ids).forEach((id) => {
    const dl = l !== 'dataLayer' ? '&l=' + l : '';
    const j = d.createElement(s);
    j.async = true;
    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + id + dl;
    const f = d.getElementsByTagName(s)[0];
    f.parentNode.insertBefore(j, f);
    });
  })(window, document, 'script', 'dataLayer', <?php echo json_encode(GTM_IDS); ?>);
</script>
<?php } ?>
<!-- End Google Tag Manager -->
<?php renderAppScripts();?>
