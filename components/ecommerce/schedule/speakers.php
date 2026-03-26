<?php

/**
 * Renderiza un botón de tab del calendario.
 *
 * Esta función genera el HTML para un botón del calendario, incluyendo
 * si está seleccionado o finalizado. La visualización depende de los
 * parámetros pasados, como si está seleccionado o si ha finalizado.
 *
 * @param int $day El día del evento (1, 2, 3.).
 * @param array $info Un arreglo que contiene la fecha completa y la abreviada solo para mobile.
 * @param bool $isSelected Indica si el botón está seleccionado, lo cual activa la tab de la agenda correspondiente a ese día.
 * @param bool $isFinalized Indica si el día está finalizado, lo cual le agrega el label de finalizado al día correspondiente.
 * @param string $state El estado actual del evento. Puede ser 'pre', 'live' o 'post'.
 *
 * @return string El HTML generado para un botón del calendario.
 */
function renderButton($day, $info, $isSelected, $isFinalized, $state)
{
    $isPost = $state === 'post';
    $selected = $isSelected ? 'true' : 'false';
    $finalized = ($isFinalized && !$isPost) ? ' - finalizado' : '';
    $id = "day{$day}";

    return <<<HTML
        <button class="emms__calendar__tab" role="tab" aria-selected="{$selected}" id="{$id}">
            <span class="dk">{$info['date']}{$finalized}</span>
            <span class="mb">{$info['short']}{$finalized}</span>
        </button>
        HTML;
}


/**
 * Renderiza todos los botones de tabs del calendario según el estado.
 *
 * Dependiendo del estado del evento ('pre', 'live', 'post'), esta función
 * genera los botones correspondientes, marcando los días seleccionados y
 * finalizados según sea necesario.
 *
 * @param array $days Un arreglo de días con su fecha completa y fecha abreviada para mobile.
 * @param string $state El estado actual del evento. Puede ser 'pre', 'live' o 'post'.
 * @param int|null $currentDay El día actual (solo relevante para el estado 'live').
 *
 * @return string El HTML generado para todos los botones del calendario.
 */
function renderCalendarButtons($days, $state, $currentDay = null)
{
    $output = '';

    foreach ($days as $day => $info) {
        $isSelected = false;
        $isFinalized = false;

        switch ($state) {
            case 'pre':
                $isSelected = $day === 1;
                break;
            case 'live':
                $isSelected = $day === $currentDay;
                $isFinalized = $day < $currentDay;
                break;
            case 'post':
                // Si el evento termino dejamos el primer dia seleccionado por default
                $isSelected = $day === 1;
                $isFinalized = true;
                break;
        }

        $output .= renderButton($day, $info, $isSelected, $isFinalized, $state);
    }

    return $output;
}

/**
 * Renderiza las tabs del calendario.
 *
 * Esta función genera el HTML para las pestañas de los días del calendario,
 * basado en el estado actual del evento (pre, live, post).
 *
 * @param array $days Un arreglo de días con sus fechas completas  y fecha abreviada para mobile.
 * @param array $digitalTrendsStates El estado del evento (isPre, isLive, isPost) - variable global.
 * @param int|null $dayDuring El día actual (solo relevante cuando está 'live').
 *
 * @return string El HTML generado para las pestañas del calendario.
 */
function renderCalendarTabs($days, $digitalTrendsStates, $dayDuring = null)
{
    $output = '<div class="emms__calendar__tab__list">';

    if ($digitalTrendsStates['isPre']) {
        $output .= renderCalendarButtons($days, 'pre');
    }

    if ($digitalTrendsStates['isLive'] || $digitalTrendsStates['isDuring']) {
        $output .= renderCalendarButtons($days, 'live', $dayDuring);
    }

    if ($digitalTrendsStates['isPost']) {
        $output .= renderCalendarButtons($days, 'post');
    }

    $output .= '</div>';

    return $output;
}
?>

<div class="emms__calendar__tabs">

    <?php
    $days = [
        1 => ['date' => '28 de Abril', 'short' => '28/04'],
        2 => ['date' => '29 de Abril', 'short' => '29/04'],
    ];

    // Renderizamos las pestañas del calendario
    echo renderCalendarTabs($days, $digitalTrendsStates, $dayDuring);
    ?>

    <?php
    include('showSpeakersByDay.php');
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tab = document.querySelector('.emms__calendar__tabs');
        const tabButtons = tab.querySelectorAll('[role="tab"]');
        const tabPanels = Array.from(tab.querySelectorAll('[role="tabpanel"]'));

        tabPanels.forEach((panel, index) => {
            panel.hidden = index !== 0;
        });

        function tabClickHandler(e) {
            //Hide All Tabpane
            tabPanels.forEach(panel => {
                panel.hidden = 'true';
            });

            //Deselect Tab Button
            tabButtons.forEach(button => {
                button.setAttribute('aria-selected', 'false');
            });

            //Mark New Tab
            e.currentTarget.setAttribute('aria-selected', 'true');

            //Show New Tab
            const {
                id
            } = e.currentTarget;

            const currentTab = tabPanels.find(
                panel => panel.getAttribute('aria-labelledby') === id
            );

            currentTab.hidden = false;
        }

        const initializeTabs = () => {
            const activeButton = Array.from(tabButtons).find(button => button.getAttribute('aria-selected') === 'true');

            if (activeButton) {
                tabClickHandler({
                    currentTarget: activeButton
                });
            }
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', tabClickHandler);
        })


        initializeTabs();


        // Show/Hide Biography Speaker
        const btnsBio = document.querySelectorAll(".emms__calendar__list__item__card__btn-bio");
        const btnBioHide = document.querySelectorAll(".emms__calendar__list__item__card__btn-bio-hide");
        const classBioShow = "emms__calendar__list__item__card__bio--show";

        btnsBio.forEach(btn => {
            btn.addEventListener("click", () => {
                hideAllBios();
                btn.parentNode.querySelector(".bio-speaker").classList.toggle(classBioShow);
            });
        })

        btnBioHide.forEach(btnHide => {
            btnHide.addEventListener("click", () => {
                btnHide.parentNode.classList.toggle(classBioShow);
            });
        })

        function hideAllBios() {
            document.querySelectorAll(".bio-speaker").forEach(bio => {
                bio.classList.remove(classBioShow);
            })
        }
    });
</script>
