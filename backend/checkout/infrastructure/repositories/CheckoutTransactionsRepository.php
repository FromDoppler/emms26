<?php

class CheckoutTransactionsRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function findByIdempotencyKey(string $idempotencyKey): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM payment_transactions WHERE idempotency_key = ? LIMIT 1",
            [$idempotencyKey]
        )->fetchAll();

        return $result[0] ?? null;
    }

    public function findByPublicId(string $publicId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM payment_transactions WHERE public_id = ? LIMIT 1",
            [$publicId]
        )->fetchAll();

        return $result[0] ?? null;
    }

    public function createPendingTransaction(array $data): array
    {
        $data['public_id'] = $data['public_id'] ?? $this->generatePublicId();

        try {
            return $this->insertPendingTransaction($data);
        } catch (Exception $e) {
            if ($this->db->lastErrno() !== 1062) {
                throw $e;
            }

            $existingTransaction = $this->findByIdempotencyKey($data['idempotency_key']);
            if ($existingTransaction !== null) {
                $semanticMismatch =
                    $existingTransaction['customer_email'] !== $data['customer_email']
                    || (int) $existingTransaction['ticket_id'] !== (int) $data['ticket_id']
                    || (float) $existingTransaction['final_amount'] !== (float) $data['final_amount']
                    || $existingTransaction['event_key'] !== $data['event_key'];

                Logger::event('payment_transaction_idempotency_key_reused', [
                    'idempotency_key' => $data['idempotency_key'],
                    'existing_transaction_id' => (int) $existingTransaction['id'],
                    'existing_public_id' => $existingTransaction['public_id'],
                    'semantic_mismatch' => $semanticMismatch,
                ], 'PAYMENTS', $semanticMismatch ? Logger::WARNING : Logger::DUPLICATE);

                if ($semanticMismatch) {
                    throw new Exception('payment_idempotency_key_semantic_mismatch');
                }

                return $existingTransaction;
            }

            $data['public_id'] = $this->generatePublicId();

            return $this->insertPendingTransaction($data);
        }
    }

    public function findById(int $id): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM payment_transactions WHERE id = ? LIMIT 1",
            [$id]
        )->fetchAll();

        return $result[0] ?? null;
    }

    public function claimProcessing(int $transactionId): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?
             WHERE id = ?
               AND status = ?",
            [CheckoutTransactionStatus::PROCESSING, $transactionId, CheckoutTransactionStatus::PENDING]
        );

        return $this->db->affectedRows() === 1;
    }

    public function markApproved(int $transactionId, array $data): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, provider = ?, registered_id = ?, provider_transaction_id = ?, authorization_number = ?,
                 transaction_link_id = ?, authorization_response_code = ?, purchase_response_code = ?,
                 response_code = ?, response_message = ?, raw_response = ?
             WHERE id = ?
               AND status = ?",
            [
                CheckoutTransactionStatus::APPROVED,
                $data['provider'],
                $data['registered_id'],
                $data['provider_transaction_id'] ?? null,
                $data['authorization_number'] ?? null,
                $data['transaction_link_id'] ?? null,
                $data['authorization_response_code'] ?? null,
                $data['purchase_response_code'] ?? null,
                $data['response_code'],
                $data['response_message'],
                json_encode(CheckoutPayloadSanitizer::sanitize($data['raw_response'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $transactionId,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );

        return $this->db->affectedRows() === 1;
    }

    public function markRejected(int $transactionId, array $data): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, provider = ?, provider_transaction_id = ?, authorization_number = ?,
                 transaction_link_id = ?, authorization_response_code = ?, purchase_response_code = ?,
                 response_code = ?, response_message = ?, raw_response = ?
             WHERE id = ?
               AND status = ?",
            [
                CheckoutTransactionStatus::REJECTED,
                $data['provider'],
                $data['provider_transaction_id'] ?? null,
                $data['authorization_number'] ?? null,
                $data['transaction_link_id'] ?? null,
                $data['authorization_response_code'] ?? null,
                $data['purchase_response_code'] ?? null,
                $data['response_code'],
                $data['response_message'],
                json_encode(CheckoutPayloadSanitizer::sanitize($data['raw_response'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $transactionId,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );

        return $this->db->affectedRows() === 1;
    }

    public function markError(int $transactionId, array $data): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, provider = ?, provider_transaction_id = ?, authorization_number = ?,
                 transaction_link_id = ?, authorization_response_code = ?, purchase_response_code = ?,
                 response_code = ?, response_message = ?, raw_response = ?
             WHERE id = ?
               AND status IN (?, ?)",
            [
                CheckoutTransactionStatus::ERROR,
                $data['provider'],
                $data['provider_transaction_id'] ?? null,
                $data['authorization_number'] ?? null,
                $data['transaction_link_id'] ?? null,
                $data['authorization_response_code'] ?? null,
                $data['purchase_response_code'] ?? null,
                $data['response_code'],
                $data['response_message'],
                json_encode(CheckoutPayloadSanitizer::sanitize($data['raw_response'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $transactionId,
                CheckoutTransactionStatus::PENDING,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );

        return $this->db->affectedRows() === 1;
    }

    private function insertPendingTransaction(array $data): array
    {
        $sql = "INSERT INTO payment_transactions (
                    public_id, correlation_id, idempotency_key, status, provider,
                    payment_method, origin, customer_email, customer_name, customer_phone, ticket_id, ticket_code, ticket_name, coupon_id,
                    coupon_code, coupon_link_code, discount_type, discount_value, amount, discount_amount,
                    final_amount, currency, event_key, event_free_id, event_vip_id, raw_request
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $data['public_id'],
            $data['correlation_id'],
            $data['idempotency_key'],
            CheckoutTransactionStatus::PENDING,
            $data['provider'],
            $data['payment_method'],
            $data['origin'],
            $data['customer_email'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['ticket_id'],
            $data['ticket_code'],
            $data['ticket_name'],
            $data['coupon_id'],
            $data['coupon_code'],
            $data['coupon_link_code'],
            $data['discount_type'],
            $data['discount_value'],
            $data['amount'],
            $data['discount_amount'],
            $data['final_amount'],
            $data['currency'],
            $data['event_key'],
            $data['event_free_id'],
            $data['event_vip_id'],
            json_encode(CheckoutPayloadSanitizer::sanitize($data['raw_request'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $insertedId = (int) $this->db->lastInsertID();
        $transaction = $this->findById($insertedId);

        if ($transaction === null) {
            throw new Exception('payment_transaction_inserted_but_not_found');
        }

        return $transaction;
    }

    private function generatePublicId(): string
    {
        return 'pay_' . bin2hex(random_bytes(16));
    }
}
