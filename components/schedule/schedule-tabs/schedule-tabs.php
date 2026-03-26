<?php

echo '<div class="schedule__tabs">';

if ($digitalTrendsStates['isPre']) {
    $state = 'pre';
    include __DIR__ . '/schedule-buttons.php';
}

if ($digitalTrendsStates['isLive'] || $digitalTrendsStates['isDuring']) {
    $state = 'live';
    $currentDay = $dayDuring;
    include __DIR__ . '/schedule-buttons.php';
}

if ($digitalTrendsStates['isPost']) {
    $state = 'post';
    include __DIR__ . '/schedule-buttons.php';
}

echo '</div>';

?>


<script>
    // Funciones para intercambiar las tabs y sus contenidos
    document.addEventListener('DOMContentLoaded', () => {
        const tab = document.querySelector('.emms__calendar__tabs');

        const tabButtons = tab.querySelectorAll('[role="tab"]');
        const tabPanels = Array.from(tab.querySelectorAll('[role="tabpanel"]'));
        tabPanels.forEach((panel, index) => {
            panel.hidden = index !== 0;
        });

        function tabClickHandler(e) {
            //Hide All Tabpanel
            tabPanels.forEach(panel => {
                panel.hidden = true;
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
    });
</script>
