<?php

class RegisteredProfileWriter
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function save(array $user): void
    {
        $existing = $this->db->query(
            'SELECT id FROM registered WHERE email = ? LIMIT 1',
            [$user['email']]
        )->fetchArray();

        if (!$existing) {
            $this->db->saveRegistered($user);
            return;
        }

        $profile = $user;
        unset($profile['ecommerce'], $profile['digital_trends'], $profile['digital-trends']);
        $this->db->saveRegistered($profile);

        $this->db->query(
            'UPDATE registered
             SET ecommerce = GREATEST(ecommerce, ?),
                 `digital-trends` = GREATEST(`digital-trends`, ?)
             WHERE id = ?',
            [
                (int) ($user['ecommerce'] ?? 0),
                (int) ($user['digital_trends'] ?? 0),
                (int) $existing['id'],
            ]
        );
    }
}
