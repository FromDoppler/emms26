<?php

class SalesReportService
{
    private const REPORT_TIMEZONE = 'America/Argentina/Buenos_Aires';
    private const REPORT_HOUR = 18;
    private const MAX_SLACK_BLOCKS = 50;

    private $repository;
    private $slack;

    public function __construct(SalesReportRepository $repository, SlackWebhookClient $slack)
    {
        $this->repository = $repository;
        $this->slack = $slack;
    }

    public function publish(?DateTimeImmutable $now = null): array
    {
        $timezone = new DateTimeZone(self::REPORT_TIMEZONE);
        $nowLocal = $now === null
            ? new DateTimeImmutable('now', $timezone)
            : $now->setTimezone($timezone);

        [$startLocal, $endLocal] = $this->resolvePeriod($nowLocal);
        $utc = new DateTimeZone('UTC');
        $sales = $this->repository->findPaidSales(
            $startLocal->setTimezone($utc),
            $endLocal->setTimezone($utc)
        );

        $result = [
            'period_start' => $startLocal->format(DateTimeInterface::ATOM),
            'period_end' => $endLocal->format(DateTimeInterface::ATOM),
            'sales_count' => count($sales),
            'status' => 'no_sales',
        ];

        if ($sales === []) {
            return $result;
        }

        $payload = $this->buildSlackPayload($sales, $startLocal, $endLocal);
        $this->slack->send($payload['message']);

        $result['status'] = 'sent';
        $result['displayed_sales_count'] = $payload['displayed_sales_count'];
        $result['total_amount'] = $payload['total_amount'];

        return $result;
    }

    private function resolvePeriod(DateTimeImmutable $nowLocal): array
    {
        $boundaryToday = $nowLocal->setTime(self::REPORT_HOUR, 0, 0);
        $end = $nowLocal >= $boundaryToday
            ? $boundaryToday
            : $boundaryToday->modify('-1 day');

        return [$end->modify('-1 day'), $end];
    }

    private function buildSlackPayload(array $sales, DateTimeImmutable $startLocal, DateTimeImmutable $endLocal): array
    {
        $totalAmount = '0.00';
        foreach ($sales as $sale) {
            $totalAmount = $this->addDecimal($totalAmount, (string) $sale['final_amount']);
        }

        $count = count($sales);
        $displayLimit = $this->resolveDisplayLimit($count);
        $displayedSales = array_slice($sales, 0, $displayLimit);
        $displayed = count($displayedSales);

        $period = $startLocal->format('d/m H:i')
            . ' → '
            . $endLocal->format('d/m H:i')
            . ' (Argentina)';
        $salesLabel = $count === 1 ? '1 venta' : $count . ' ventas';
        $title = '💰 EMMS · Ventas de hoy';

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => $title,
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => '*Período:* ' . $period
                        . "\n*Ventas:* " . $count
                        . ' · *Facturación:* USD ' . $totalAmount,
                    'verbatim' => true,
                ],
            ],
        ];

        foreach ($displayedSales as $sale) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $this->saleText($sale),
                    'verbatim' => true,
                ],
            ];
        }

        if ($displayed < $count) {
            $blocks[] = [
                'type' => 'context',
                'elements' => [[
                    'type' => 'mrkdwn',
                    'text' => 'Mostrando ' . $displayed . ' de ' . $count . ' ventas por límites de Slack.',
                    'verbatim' => true,
                ]],
            ];
        }

        $blocks[] = [
            'type' => 'context',
            'elements' => [[
                'type' => 'mrkdwn',
                'text' => 'Incluye únicamente compras aprobadas con pago efectivo. Los cupones 100% quedan excluidos.',
                'verbatim' => true,
            ]],
        ];

        return [
            'displayed_sales_count' => $displayed,
            'total_amount' => $totalAmount,
            'message' => [
                'text' => $title . ' · ' . $salesLabel . ' · USD ' . $totalAmount,
                'blocks' => $blocks,
            ],
        ];
    }

    private function resolveDisplayLimit(int $count): int
    {
        $fixedBlocks = 3; // header, summary and final context
        $withoutTruncation = self::MAX_SLACK_BLOCKS - $fixedBlocks;

        if ($count <= $withoutTruncation) {
            return $count;
        }

        return $withoutTruncation - 1; // reserve one block for the truncation context
    }

    private function saleText(array $sale): string
    {
        $customer = $this->customerSnapshot($sale);
        $reportedAtUtc = new DateTimeImmutable((string) $sale['updated_at'], new DateTimeZone('UTC'));
        $reportedAtLocal = $reportedAtUtc->setTimezone(new DateTimeZone(self::REPORT_TIMEZONE));

        $amount = $this->escapeSlackText($this->text((string) $sale['final_amount'], 20));
        $email = $this->escapeSlackText($this->text($sale['customer_email'] ?? '', 160));
        $phone = $this->escapeSlackText($this->text($sale['customer_phone'] ?? '', 70));
        $source = $this->escapeSlackText($this->text($customer['source_utm'] ?? '', 60));
        $medium = $this->escapeSlackText($this->text($customer['medium_utm'] ?? '', 60));
        $campaign = $this->escapeSlackText($this->text($customer['campaign_utm'] ?? '', 90));

        return implode("\n", [
            '💰 *USD ' . $amount . '* · ' . $reportedAtLocal->format('H:i'),
            '✉️ ' . $email . ' · 📞 ' . $phone,
            '📊 ' . $source . ' · ' . $medium . ' · ' . $campaign,
        ]);
    }

    private function customerSnapshot(array $sale): array
    {
        $raw = json_decode((string) ($sale['raw_request'] ?? ''), true);
        if (!is_array($raw) || !isset($raw['customer']) || !is_array($raw['customer'])) {
            return [];
        }

        return $raw['customer'];
    }

    private function escapeSlackText(string $value): string
    {
        return str_replace(
            ['&', '<', '>'],
            ['&amp;', '&lt;', '&gt;'],
            $value
        );
    }

    private function text($value, int $maxLength): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '—';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLength);
        }

        return substr($text, 0, $maxLength);
    }

    private function addDecimal(string $left, string $right): string
    {
        $leftCents = $this->decimalToCents($left);
        $rightCents = $this->decimalToCents($right);
        $total = $leftCents + $rightCents;

        return sprintf('%d.%02d', intdiv($total, 100), $total % 100);
    }

    private function decimalToCents(string $value): int
    {
        if (preg_match('/^(\d+)\.(\d{2})$/D', trim($value), $matches) !== 1) {
            throw new InvalidArgumentException('invalid_sales_report_decimal');
        }

        return ((int) $matches[1] * 100) + (int) $matches[2];
    }
}
