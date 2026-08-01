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
}
