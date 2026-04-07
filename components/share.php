<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();

function getCurrentShareUrl($normalizedUrl)
{
    $path = empty($normalizedUrl) ? '/' : $normalizedUrl;
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'goemms.com';

    return $scheme . '://' . $host . $path;
}

function getLinkPreByCurrentUrl($url)
{
    $shareUrl = rawurlencode(getCurrentShareUrl($url));
    $twitterText = rawurlencode('¡Ya llega el EMMS 2026! Vive una edición especial por los 20 años de Doppler: vuelven algunos de los Speakers más destacados en la historia del EMMS, junto a Conferencias, Workshops y beneficios especiales. Es online y gratuito.');
    $linkedinTitle = rawurlencode('¡Ya llega el EMMS 2026!');
    $linkedinSummary = rawurlencode('¡Ya llega el EMMS 2026! Se viene una edición especial del evento de Marketing Digital más esperado: celebramos los 20 años de Doppler con el regreso de algunos de los Speakers más destacados en la historia del EMMS. Conferencias, Workshops, Casos de Éxito, sorteos, beneficios especiales y mucho más. Online y gratuito.');
    $facebookQuote = rawurlencode('¡Ya llega el EMMS 2026! 🤩 Vive una edición especial por los 20 años de Doppler, con el regreso de algunos de los Speakers más destacados en la historia del EMMS. Descubre tendencias, ideas y estrategias de negocio junto a referentes de la industria. 💡 Es online y gratuito.');

    $shareLinks = [
        'twitter' => "https://twitter.com/intent/tweet?text={$twitterText}%20{$shareUrl}",
        'linkedln' => "https://www.linkedin.com/shareArticle?mini=true&url={$shareUrl}&title={$linkedinTitle}&summary={$linkedinSummary}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}&quote={$facebookQuote}",
    ];

    $urls = [
        '/' => $shareLinks,
        '/digital-trends-registrado' => $shareLinks,
        '/*' => $shareLinks,
    ];

    return $urls[$url] ?? $urls['/*'];
}


function getLinkDuringByCurrentUrl($url)
{
    $urls = [
        '/' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20a%20la%20nueva%20edici%C3%B3n%20del%20evento%20m%C3%A1s%20esperado%20por%20marketers%20y%20emprendedores.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A%2F%2Fgoemms.com%2Fdigital-trends&summary=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20%C3%9Anete%20a%20la%20transmisi%C3%B3n%20del%20evento%20y%20disfruta%20las%20Conferencias%20de%20figuras%20internacionales%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20y%20%C3%BAnete%20al%20evento%20que%20marcar%C3%A1%20las%20tendencias%20del%20presente%20y%20futuro%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'whatsapp' => 'https://wa.me/?text=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20y%20%C3%BAnete%20al%20evento%20que%20marcar%C3%A1%20las%20tendencias%20del%20presente%20y%20futuro%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends'
        ],

        '/digital-trends' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20a%20la%20nueva%20edici%C3%B3n%20del%20evento%20m%C3%A1s%20esperado%20por%20marketers%20y%20emprendedores.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A%2F%2Fgoemms.com%2Fdigital-trends&summary=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20%C3%9Anete%20a%20la%20transmisi%C3%B3n%20del%20evento%20y%20disfruta%20las%20Conferencias%20de%20figuras%20internacionales%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20y%20%C3%BAnete%20al%20evento%20que%20marcar%C3%A1%20las%20tendencias%20del%20presente%20y%20futuro%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.'
        ],

        '/digital-trends-registrado' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20a%20la%20nueva%20edici%C3%B3n%20del%20evento%20m%C3%A1s%20esperado%20por%20marketers%20y%20emprendedores.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A%2F%2Fgoemms.com%2Fdigital-trends&summary=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20%C3%9Anete%20a%20la%20transmisi%C3%B3n%20del%20evento%20y%20disfruta%20las%20Conferencias%20de%20figuras%20internacionales%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20y%20%C3%BAnete%20al%20evento%20que%20marcar%C3%A1%20las%20tendencias%20del%20presente%20y%20futuro%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.'
        ],

        '/*' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20a%20la%20nueva%20edici%C3%B3n%20del%20evento%20m%C3%A1s%20esperado%20por%20marketers%20y%20emprendedores.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A%2F%2Fgoemms.com%2Fdigital-trends&summary=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20%C3%9Anete%20a%20la%20transmisi%C3%B3n%20del%20evento%20y%20disfruta%20las%20Conferencias%20de%20figuras%20internacionales%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=EMMS%20Digital%20Trends%202025%3A%20%C2%A1Estamos%20en%20vivo!%20Reg%C3%ADstrate%20gratis%20y%20%C3%BAnete%20al%20evento%20que%20marcar%C3%A1%20las%20tendencias%20del%20presente%20y%20futuro%20del%20Marketing%20Digital.%20Es%20online%20y%20gratuito.%20Reserva%20tu%20lugar%20en%20goemms.com%2Fdigital-trends'
        ],
    ];

    return $urls[$url] ?? $urls['/*'];
}


function getLinkPostByCurrentUrl($url)
{
    $urls = [
        '/' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20al%20evento%20y%20accede%20a%20las%20conferencias%20de%20las%20principales%20figuras%20internacionales%20de%20la%20industria.%20Inscr%C3%ADbete%20ahora%20en%20https%3A%2F%2Fgoemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fgoemms.com%2Fdigital-trends&title=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3!&summary=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20ahora%20y%20accede%20a%20las%20conferencias%20gratuitas%20para%20aprender%20las%20%C3%BAltimas%20tendencias%20en%20Marketing%20Digital.%20Es%20gratis%20y%20online.',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20y%20accede%20a%20todas%20las%20conferencias%20para%20capacitarte%20con%20referentes%20internacionales%20en%20Marketing%20Digital.'
        ],

        '/digital-trends-registrado' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20al%20evento%20y%20accede%20a%20las%20conferencias%20de%20las%20principales%20figuras%20internacionales%20de%20la%20industria.%20Inscr%C3%ADbete%20ahora%20en%20https%3A%2F%2Fgoemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fgoemms.com%2Fdigital-trends&title=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3!&summary=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20ahora%20y%20accede%20a%20las%20conferencias%20gratuitas%20para%20aprender%20las%20%C3%BAltimas%20tendencias%20en%20Marketing%20Digital.%20Es%20gratis%20y%20online.',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20y%20accede%20a%20todas%20las%20conferencias%20para%20capacitarte%20con%20referentes%20internacionales%20en%20Marketing%20Digital.'
        ],

        '/*' => [
            'twitter' => 'https://twitter.com/intent/tweet?text=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20al%20evento%20y%20accede%20a%20las%20conferencias%20de%20las%20principales%20figuras%20internacionales%20de%20la%20industria.%20Inscr%C3%ADbete%20ahora%20en%20https%3A%2F%2Fgoemms.com%2Fdigital-trends',
            'linkedln' => 'https://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fgoemms.com%2Fdigital-trends&title=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3!&summary=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20ahora%20y%20accede%20a%20las%20conferencias%20gratuitas%20para%20aprender%20las%20%C3%BAltimas%20tendencias%20en%20Marketing%20Digital.%20Es%20gratis%20y%20online.',
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fgoemms.com%2Fdigital-trends&quote=El%20EMMS%20Digital%20Trends%202025%20ya%20finaliz%C3%B3%2C%20%C2%A1pero%20a%C3%BAn%20puedes%20verlo!%20Reg%C3%ADstrate%20gratis%20y%20accede%20a%20todas%20las%20conferencias%20para%20capacitarte%20con%20referentes%20internacionales%20en%20Marketing%20Digital.'
        ],
    ];

    return $urls[$url] ??  $urls['/*'];
}



if ($digitalTrendsStates['isPre']) {
    $link = getLinkPreByCurrentUrl($normalizedUrl);
} else if ($digitalTrendsStates['isDuring']) {
    $link = getLinkDuringByCurrentUrl($normalizedUrl);
} else if ($digitalTrendsStates['isPost']) {
    $link = getLinkPostByCurrentUrl($normalizedUrl);
}
?>

<div class="emms__share">
    <a id="btn-share" class="emms__share__open-list"><img src="/src/img/icons/icon-share.svg" alt="Share"></a>
    <ul id="list-share" class="emms__share__list">
        <li>
            <a href="javascript: void(0);" onclick="window.open ('<?= $link['facebook'] ?>', 'Facebook', 'toolbar=0, status=0, width=550, height=350');">
                <img src="/src/img/Facebook-w.svg" alt="Facebook">
            </a>
        </li>
        <li>
            <a href="javascript: void(0);" onclick="window.open ('<?= $link['twitter'] ?>', 'Twitter', 'toolbar=0, status=0, width=550, height=350');">
                <img src="/src/img/Twitter-w.svg" alt="Twitter">
            </a>
        </li>
        <li>
            <a href="javascript: void(0);" onclick="window.open ('<?= $link['linkedln'] ?>', 'Linkedin', 'toolbar=0, status=0, width=550, height=550');">
                <img src="/src/img/LinkedIn-w.svg" alt="LinkedIn">
            </a>
        </li>
    </ul>
</div>
