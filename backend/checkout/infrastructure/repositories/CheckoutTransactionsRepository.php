<?php

class CheckoutTransactionsRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function findByPaymentId(string $paymentId): ?array
    {
        $rows = $this->db->query(
            "SELECT * FROM payment_transactions WHERE payment_id = ? LIMIT 1",
            [$paymentId]
        )->fetchAll();
        return $rows[0] ?? null;
    }

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            "SELECT * FROM payment_transactions WHERE id = ? LIMIT 1",
            [$id]
        )->fetchAll();
        return $rows[0] ?? null;
    }

    public function lockByPaymentId(string $paymentId): ?array
    {
        $rows = $this->db->query(
            "SELECT * FROM payment_transactions WHERE payment_id = ? LIMIT 1 FOR UPDATE",
            [$paymentId]
        )->fetchAll();
        return $rows[0] ?? null;
    }

    public function createPending(array $data): array
    {
        $rawRequest = json_encode($data['raw_request'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($rawRequest === false) {
            throw new Exception('payment_raw_request_encode_failed');
        }

        $sql = "INSERT INTO payment_transactions (
                    payment_id, correlation_id, status, provider, payment_method,
                    origin, customer_email, customer_name, customer_phone, customer_ip,
                    ticket_id, ticket_code, ticket_name, coupon_id, coupon_code,
                    amount, discount_amount, final_amount, currency,
                    event_key, event_free_id, event_vip_id, event_phase, raw_request
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $this->db->query($sql, [
                $data['payment_id'],
                $data['correlation_id'],
                CheckoutTransactionStatus::PENDING,
                $data['provider'],
                $data['payment_method'],
                $data['origin'],
                $data['customer_email'],
                $data['customer_name'],
                $data['customer_phone'],
                $data['customer_ip'],
                $data['ticket_id'],
                $data['ticket_code'],
                $data['ticket_name'],
                $data['coupon_id'],
                $data['coupon_code'],
                $data['amount'],
                $data['discount_amount'],
                $data['final_amount'],
                $data['currency'],
                $data['event_key'],
                $data['event_free_id'],
                $data['event_vip_id'],
                $data['event_phase'],
                $rawRequest,
            ]);
        } catch (Throwable $e) {
            if ($this->db->lastErrno() === 1062) {
                $existing = $this->findByPaymentId($data['payment_id']);
                if ($existing !== null) {
                    return $existing;
                }
            }
            throw $e;
        }

        $transaction = $this->findById((int) $this->db->lastInsertID());
        if ($transaction === null) {
            throw new Exception('payment_inserted_but_not_found');
        }
        return $transaction;
    }

    public function claimProcessing(string $paymentId): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?
             WHERE payment_id = ?
               AND status = ?
               AND provider_approved_at IS NULL
               AND authorization_number IS NULL
               AND transaction_link_id IS NULL
               AND authorization_response_code IS NULL
               AND purchase_response_code IS NULL
               AND response_code IS NULL",
            [CheckoutTransactionStatus::PROCESSING, $paymentId, CheckoutTransactionStatus::PENDING]
        );
        return $this->db->affectedRows() === 1;
    }

    public function rejectPendingAsAlreadyVip(string $paymentId): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, response_code = ?
             WHERE payment_id = ?
               AND status = ?
               AND provider_approved_at IS NULL
               AND authorization_number IS NULL
               AND transaction_link_id IS NULL
               AND authorization_response_code IS NULL
               AND purchase_response_code IS NULL
               AND response_code IS NULL",
            [CheckoutTransactionStatus::REJECTED, 'already_vip', $paymentId, CheckoutTransactionStatus::PENDING]
        );
        return $this->db->affectedRows() === 1;
    }

    public function markProviderRejected(string $paymentId, array $evidence, string $responseCode): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, provider = ?, authorization_number = ?,
                 transaction_link_id = ?,
                 authorization_response_code = ?, purchase_response_code = ?,
                 response_code = ?
             WHERE payment_id = ?
               AND status = ?
               AND provider_approved_at IS NULL
               AND authorization_number IS NULL
               AND transaction_link_id IS NULL
               AND authorization_response_code IS NULL
               AND purchase_response_code IS NULL
               AND response_code IS NULL",
            [
                CheckoutTransactionStatus::REJECTED,
                $evidence['provider'],
                $evidence['authorization_number'],
                $evidence['transaction_link_id'],
                $evidence['authorization_response_code'],
                $evidence['purchase_response_code'],
                $responseCode,
                $paymentId,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );
        return $this->db->affectedRows() === 1;
    }

    public function markErrorBeforeProvider(string $paymentId, string $code): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, response_code = ?
             WHERE payment_id = ?
               AND status IN (?, ?)
               AND provider_approved_at IS NULL
               AND authorization_number IS NULL
               AND transaction_link_id IS NULL
               AND authorization_response_code IS NULL
               AND purchase_response_code IS NULL
               AND response_code IS NULL",
            [
                CheckoutTransactionStatus::ERROR,
                $code,
                $paymentId,
                CheckoutTransactionStatus::PENDING,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );
        return $this->db->affectedRows() === 1;
    }

    public function persistApprovalMarker(string $paymentId, array $evidence): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET provider_approved_at = CURRENT_TIMESTAMP,
                 provider = ?, authorization_number = ?, transaction_link_id = ?,
                 authorization_response_code = ?,
                 purchase_response_code = ?, response_code = ?
             WHERE payment_id = ?
               AND status = ?
               AND provider_approved_at IS NULL
               AND authorization_number IS NULL
               AND transaction_link_id IS NULL
               AND authorization_response_code IS NULL
               AND purchase_response_code IS NULL
               AND response_code IS NULL",
            [
                $evidence['provider'],
                $evidence['authorization_number'],
                $evidence['transaction_link_id'],
                $evidence['authorization_response_code'],
                $evidence['purchase_response_code'],
                'provider_approved',
                $paymentId,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );
        return $this->db->affectedRows() === 1;
    }

    public function markApproved(string $paymentId, int $registeredId): bool
    {
        $this->db->query(
            "UPDATE payment_transactions
             SET status = ?, registered_id = ?, response_code = ?
             WHERE payment_id = ? AND status = ?",
            [
                CheckoutTransactionStatus::APPROVED,
                $registeredId,
                'approved',
                $paymentId,
                CheckoutTransactionStatus::PROCESSING,
            ]
        );
        return $this->db->affectedRows() === 1;
    }
}
