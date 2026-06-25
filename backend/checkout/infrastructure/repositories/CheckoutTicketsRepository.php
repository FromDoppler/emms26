<?php

class CheckoutTicketsRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function findActiveTicketsForEvent(array $event): array
    {
        return $this->db->query(
            "SELECT *
             FROM payment_tickets
             WHERE is_active = 1
               AND event_key = ?
             ORDER BY price ASC, id ASC",
            [$event['eventKey']]
        )->fetchAll();
    }

    public function findActiveTicketByCodeForEvent(array $event, string $ticketCode): ?array
    {
        $result = $this->db->query(
            "SELECT *
             FROM payment_tickets
             WHERE is_active = 1
               AND event_key = ?
               AND ticket_code = ?
             LIMIT 1",
            [$event['eventKey'], $ticketCode]
        )->fetchAll();

        return $result[0] ?? null;
    }
}
