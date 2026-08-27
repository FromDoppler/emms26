<?php

class SalesReportService
{
    private const REPORT_TIMEZONE = 'America/Argentina/Buenos_Aires';
    private const REPORT_HOUR = 18;
    private const MAX_DATA_ROWS = 200;
    private const MAX_TABLE_CHARACTERS = 18000;

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
        $rows = [$this->headerRow()];
        $tableCharacters = $this->rowCharacters($rows[0]);
        $displayed = 0;
        $totalAmount = '0.00';

        foreach ($sales as $sale) {
            $totalAmount = $this->addDecimal($totalAmount, (string) $sale['final_amount']);

            if ($displayed >= self::MAX_DATA_ROWS) {
                continue;
            }

            $row = $this->saleRow($sale);
            $rowCharacters = $this->rowCharacters($row);
            if ($tableCharacters + $rowCharacters > self::MAX_TABLE_CHARACTERS) {
                continue;
            }

            $rows[] = $row;
            $tableCharacters += $rowCharacters;
            $displayed++;
        }

        $period = $startLocal->format('d/m H:i')
            . ' → '
            . $endLocal->format('d/m H:i')
            . ' (Argentina)';
        $count = count($sales);
        $salesLabel = $count === 1 ? '1 venta' : $count . ' ventas';

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => '💰 EMMS · Ventas de hoy',
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
                ],
            ],
            [
                'type' => 'data_table',
                'caption' => 'Detalle de ventas EMMS',
                'page_size' => min(25, max(1, $displayed)),
                'row_header_column_index' => 1,
                'rows' => $rows,
            ],
        ];

        if ($displayed < $count) {
            $blocks[] = [
                'type' => 'context',
                'elements' => [[
                    'type' => 'mrkdwn',
                    'text' => 'Mostrando ' . $displayed . ' de ' . $count . ' ventas por límites de la tabla de Slack.',
                ]],
            ];
        }

        $blocks[] = [
            'type' => 'context',
            'elements' => [[
                'type' => 'mrkdwn',
                'text' => 'Incluye únicamente compras aprobadas con pago efectivo. Los cupones 100% quedan excluidos.',
            ]],
        ];

        return [
            'displayed_sales_count' => $displayed,
            'total_amount' => $totalAmount,
            'message' => [
                'text' => 'EMMS · Ventas de hoy · ' . $salesLabel . ' · USD ' . $totalAmount,
                'blocks' => $blocks,
            ],
        ];
    }

    private function headerRow(): array
    {
        return array_map(function (string $text): array {
            return ['type' => 'raw_text', 'text' => $text];
        }, [
            'Hora',
            'Nombre',
            'Email',
            'Teléfono',
            'País',
            'USD',
            'UTM Source',
            'UTM Medium',
            'UTM Campaign',
        ]);
    }

    private function saleRow(array $sale): array
    {
        $customer = $this->customerSnapshot($sale);
        $reportedAtUtc = new DateTimeImmutable((string) $sale['updated_at'], new DateTimeZone('UTC'));
        $reportedAtLocal = $reportedAtUtc->setTimezone(new DateTimeZone(self::REPORT_TIMEZONE));
        $firstName = trim((string) ($customer['firstname'] ?? $sale['customer_name'] ?? ''));
        $lastName = trim((string) ($customer['lastname'] ?? ''));
        $name = trim($firstName . ' ' . $lastName);

        return [
            $this->rawText($reportedAtLocal->format('H:i')),
            $this->rawText($name, 120),
            $this->rawText($sale['customer_email'] ?? '', 180),
            $this->rawText($sale['customer_phone'] ?? '', 80),
            $this->rawText($customer['country'] ?? '', 80),
            [
                'type' => 'raw_number',
                'value' => (float) $sale['final_amount'],
                'text' => (string) $sale['final_amount'],
            ],
            $this->rawText($customer['source_utm'] ?? '', 120),
            $this->rawText($customer['medium_utm'] ?? '', 120),
            $this->rawText($customer['campaign_utm'] ?? '', 160),
        ];
    }

    private function customerSnapshot(array $sale): array
    {
        $raw = json_decode((string) ($sale['raw_request'] ?? ''), true);
        if (!is_array($raw) || !isset($raw['customer']) || !is_array($raw['customer'])) {
            return [];
        }

        return $raw['customer'];
    }

    private function rawText($value, int $maxLength = 80): array
    {
        return [
            'type' => 'raw_text',
            'text' => $this->text($value, $maxLength),
        ];
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

    private function rowCharacters(array $row): int
    {
        $characters = 0;
        foreach ($row as $cell) {
            if (($cell['type'] ?? null) === 'raw_number') {
                $characters += strlen((string) ($cell['text'] ?? $cell['value'] ?? ''));
            } else {
                $characters += strlen((string) ($cell['text'] ?? ''));
            }
        }
        return $characters;
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
