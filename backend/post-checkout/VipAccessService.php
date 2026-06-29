<?php

class VipAccessService
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

    public function upgrade(array $event, array $customer, string $phase): int
    {
        $freeColumn = $event['registeredFreeColumn'];
        $vipColumn = $event['registeredVipColumn'];
        $this->assertAllowedColumn($freeColumn);
        $this->assertAllowedColumn($vipColumn);

        $normalizedEmail = strtolower(trim($customer['email']));

        // SELECT FOR UPDATE locks the matched row (or the gap when no row exists yet)
        // for the duration of the caller's open transaction, serializing concurrent
        // upgrades for the same email without needing advisory locks.
        $existing = $this->db->query(
            "SELECT * FROM registered WHERE email = ? LIMIT 1 FOR UPDATE",
            [$normalizedEmail]
        )->fetchAll();

        if (!empty($existing)) {
            return $this->applyUpgrade($existing[0], $freeColumn, $vipColumn, $customer, $phase);
        }

        $insertSql = "INSERT INTO registered (
                        `register`, phase, email, firstname, lastname, country, phone, company, jobPosition, website,
                        emailPlatform, ecommerce, `ecommerce-vip`, `digital-trends`, `digital-trends-vip`,
                        source_utm, medium_utm, campaign_utm, content_utm, term_utm, emms_ref
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $columns = [
            'ecommerce' => 0,
            'ecommerce-vip' => 0,
            'digital-trends' => 0,
            'digital-trends-vip' => 0,
        ];
        $columns[$freeColumn] = 1;
        $columns[$vipColumn] = 1;

        try {
            $this->db->query($insertSql, [
                date('Y-m-d H:i:s'),
                $phase,
                $normalizedEmail,
                $customer['firstname'],
                $customer['lastname'],
                $customer['country'],
                $customer['phone'],
                $customer['company'],
                $customer['jobPosition'],
                $customer['website'],
                $customer['emailPlatform'],
                $columns['ecommerce'],
                $columns['ecommerce-vip'],
                $columns['digital-trends'],
                $columns['digital-trends-vip'],
                $customer['source_utm'],
                $customer['medium_utm'],
                $customer['campaign_utm'],
                $customer['content_utm'],
                $customer['term_utm'],
                $customer['emms_ref'],
            ]);

            return (int) $this->db->lastInsertID();
        } catch (Exception $e) {
            if ($this->db->lastErrno() !== 1062) {
                throw $e;
            }

            // A concurrent transaction committed a row for the same email between our
            // SELECT FOR UPDATE and this INSERT. Reload with FOR UPDATE and update instead.
            $existing = $this->db->query(
                "SELECT * FROM registered WHERE email = ? LIMIT 1 FOR UPDATE",
                [$normalizedEmail]
            )->fetchAll();

            if (empty($existing)) {
                throw new Exception('registered_profile_not_found_after_duplicate_insert');
            }

            return $this->applyUpgrade($existing[0], $freeColumn, $vipColumn, $customer, $phase);
        }
    }

    private function applyUpgrade(array $profile, string $freeColumn, string $vipColumn, array $customer, string $phase): int
    {
        $registeredId = (int) $profile['id'];
        $sql = "UPDATE registered
                SET `$freeColumn` = 1,
                    `$vipColumn` = 1,
                    firstname = COALESCE(NULLIF(?, ''), firstname),
                    phone = COALESCE(NULLIF(?, ''), phone),
                    company = COALESCE(NULLIF(?, ''), company),
                    jobPosition = COALESCE(NULLIF(?, ''), jobPosition),
                    country = COALESCE(NULLIF(?, ''), country),
                    phase = ?
                WHERE id = ?";
        $this->db->query($sql, [
            $customer['firstname'],
            $customer['phone'],
            $customer['company'],
            $customer['jobPosition'],
            $customer['country'],
            $phase,
            $registeredId,
        ]);
        return $registeredId;
    }

    private function assertAllowedColumn(string $column): void
    {
        if (!in_array($column, $this->allowedColumns, true)) {
            throw new Exception('Invalid registered column: ' . $column);
        }
    }
}
