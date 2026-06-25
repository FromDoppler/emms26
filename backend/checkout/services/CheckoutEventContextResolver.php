<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');

class CheckoutEventContextResolver
{
    private function resolveEventKey(string $freeId, string $folder): string
    {
        $map = [
            'ECOMMERCE' => ECOMMERCE,
            'DIGITALTRENDS' => DIGITALTRENDS,
        ];

        $key = array_search($freeId, $map, true);

        if ($key === false) {
            throw new Exception('Unknown eventKey for freeId "' . $freeId . '". Add it to CheckoutEventContextResolver::resolveEventKey().');
        }

        return $key;
    }

    public function resolve(): array
    {
        $currentEvent = getCurrentEvent();

        if (!is_array($currentEvent)) {
            throw new Exception('Current event is not configured for checkout.');
        }

        $phaseResponse = processPhaseToShow($currentEvent['freeId']);
        $phase = $phaseResponse['phaseToShow'] ?? 'pre';

        return [
            'eventKey' => $this->resolveEventKey($currentEvent['freeId'], $currentEvent['folder']),
            'eventType' => $currentEvent['type'],
            'eventFreeId' => $currentEvent['freeId'],
            'eventVipId' => $currentEvent['vipId'],
            'eventDisplayName' => $currentEvent['name'],
            'eventPhase' => $phase,
            'registeredFreeColumn' => $currentEvent['registeredFreeColumn'],
            'registeredVipColumn' => $currentEvent['registeredVipColumn'],
            'folder' => $currentEvent['folder'],
        ];
    }
}
