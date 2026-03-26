<header class="emms__header">
    <div class="emms__container--lg emms__fade-in">
        <div class="emms__header__logo emms__header__logo--digital-trends">
            <a href="/" id="logo-link">
                <img src="/src/img/logos/logo-emms.png" alt="Emms Digital Trends 2025">
            </a>
        </div>
        <a class="emms__header__nav--mb" id="btn-burger"></a>
        <nav class="emms__header__nav emms__header__nav--hidden" id="nav-mb">
            <ul class="emms__header__nav__menu" id="navMenu">
                <li>
                    <a href="/" class="nav-link"
                        data-registered-url="/registrado"
                        data-unregistered-url="/">
                        home
                    </a>
                </li>
                <li>
                    <a href="/ecommerce" class="nav-link"
                        data-registered-url="/ecommerce-registrado"
                        data-unregistered-url="/ecommerce">
                        E-commerce
                    </a>
                </li>
                <li>
                    <a href="/sponsors" class="nav-link"
                        data-registered-url="/sponsors-registrado"
                        data-unregistered-url="/sponsors">
                        biblioteca de recursos
                    </a>
                </li>
                <li class="emms__header__nav__menu__dropdown">
                    <a href="/ediciones-anteriores" class="nav-link"
                        data-registered-url="/ediciones-anteriores-registrado"
                        data-unregistered-url="/ediciones-anteriores">
                        Qué es el EMMS
                    </a>
                    <ul class="emms__header__nav__submenu">
                        <li>
                            <a href="/ediciones-anteriores#sobre-emms" class="nav-link"
                                data-registered-url="/ediciones-anteriores-registrado#sobre-emms"
                                data-unregistered-url="/ediciones-anteriores#sobre-emms">
                                Sobre el EMMS
                            </a>
                        </li>
                        <li>
                            <a href="/ediciones-anteriores#ediciones-anteriores" class="nav-link"
                                data-registered-url="/ediciones-anteriores-registrado#ediciones-anteriores"
                                data-unregistered-url="/ediciones-anteriores#ediciones-anteriores">
                                Revive ediciones anteriores
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="/sponsors-promo" class="nav-link"
                        data-registered-url="/sponsors-promo"
                        data-unregistered-url="/sponsors">
                        sponsors
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userRegistered = localStorage.getItem('userRegistered') === 'true';
        const logoLink = document.getElementById('logo-link');
        const navLinks = document.querySelectorAll('.nav-link');

        logoLink.href = userRegistered ? '/registrado' : '/';

        navLinks.forEach(link => {
            link.href = userRegistered ?
                link.getAttribute('data-registered-url') :
                link.getAttribute('data-unregistered-url');
        });

        const currentPath = window.location.pathname;
        let activeLink = Array.from(navLinks).reduce((bestMatch, link) => {
            const linkPath = new URL(link.href).pathname;
            return (currentPath.startsWith(linkPath) && linkPath.length > (bestMatch ? new URL(bestMatch.href).pathname.length : 0)) ?
                link :
                bestMatch;
        }, null);

        if (activeLink) {
            activeLink.classList.add('active');
        } else {
            document.querySelector('#navMenu a[href="/"]').classList.add('active');
        }
    });
</script>
