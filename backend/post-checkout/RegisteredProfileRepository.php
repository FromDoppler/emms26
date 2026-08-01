<?php

class RegisteredProfileRepository
{
    private $db;
    private $allowedColumns = [
        'ecommerce',
        'ecommerce-vip',
        'digital-trends',
        'digital-trends-vip',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function findByEmailForEvent(string $email, string $freeColumn, string $vipColumn): ?array
    {
        return $this->findByEmail($email, $freeColumn, $vipColumn, false);
    }

    public function findByEmailForEventForUpdate(string $email, string $freeColumn, string $vipColumn): ?array
    {
        return $this->findByEmail($email, $freeColumn, $vipColumn, true);
    }

    private function findByEmail(string $email, string $freeColumn, string $vipColumn, bool $forUpdate): ?array
    {
        $this->assertAllowedColumn($freeColumn);
        $this->assertAllowedColumn($vipColumn);

        $safeFreeColumn = $this->quoteColumn($freeColumn);
        $safeVipColumn = $this->quoteColumn($vipColumn);

        $sql = "SELECT id, email, firstname, phone, company, jobPosition, country,
                       $safeFreeColumn AS is_free, $safeVipColumn AS is_vip
                FROM registered
                WHERE email = ?
                ORDER BY id ASC
                LIMIT 1" . ($forUpdate ? " FOR UPDATE" : "");

        $result = $this->db->query($sql, [strtolower(trim($email))])->fetchAll();
        return $result[0] ?? null;
    }

    private function assertAllowedColumn(string $column): void
    {
        if (!in_array($column, $this->allowedColumns, true)) {
            throw new Exception('Registered profile column not in allowlist: "' . $column . '". Add it to RegisteredProfileRepository::allowedColumns.');
        }
    }

    private function quoteColumn(string $column): string
    {
        return strpos($column, '-') !== false ? "`$column`" : $column;
    }
}
