<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');

class CheckoutEventContextResolver
{
    private function eventCatalog(): array
    {
        $configuredEvents = $GLOBALS['events'] ?? [];
        $catalog = [];
        foreach (['ECOMMERCE', 'DIGITALTRENDS'] as $key) {
            $event = $configuredEvents[$key] ?? null;
            if (!is_array($event)) {
                throw new Exception('Missing checkout event configuration for "' . $key . '".');
            }
            $catalog[$key] = [
                'eventType' => $event['type'],
                'eventFreeId' => $event['freeId'],
                'registeredFreeColumn' => $event['registeredFreeColumn'],
                'registeredVipColumn' => $event['registeredVipColumn'],
                'eventDisplayName' => $event['name'],
                'folder' => $event['folder'],
            ];
        }
        return $catalog;
    }

    private function resolveEventKey(string $freeId): string
    {
        foreach ($this->eventCatalog() as $key => $event) {
            if ($event['eventFreeId'] === $freeId) {
                return $key;
            }
        }
        throw new Exception('Unknown eventKey for freeId "' . $freeId . '".');
    }

    public function resolve(): array
    {
        $currentEvent = getCurrentEvent();

        if (!is_array($currentEvent)) {
            throw new Exception('Current event is not configured for checkout.');
        }

        $phaseResponse = processPhaseToShow($currentEvent['freeId']);
        $phase = $this->validatePhase($phaseResponse['phaseToShow'] ?? null);

        return [
            'eventKey' => $this->resolveEventKey($currentEvent['freeId']),
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

    public function resolveByPayment(array $payment): array
    {
        $key = (string) $payment['event_key'];
        $catalog = $this->eventCatalog();
        if (!isset($catalog[$key])) {
            throw new Exception('Unknown eventKey "' . $key . '".');
        }

        return array_merge($catalog[$key], [
            'eventKey' => $key,
            'eventFreeId' => $payment['event_free_id'],
            'eventVipId' => $payment['event_vip_id'],
            'eventPhase' => $this->validatePhase($payment['event_phase'] ?? null),
        ]);
    }

    private function validatePhase($phase): string
    {
        if (!is_string($phase) || !in_array($phase, ['pre', 'during', 'post'], true)) {
            throw new Exception('invalid_checkout_event_phase_configuration');
        }

        return $phase;
    }
}
