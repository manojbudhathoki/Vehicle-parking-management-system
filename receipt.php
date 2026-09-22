<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$receiptId = (int) ($_GET['id'] ?? 0);
if ($receiptId <= 0) {
    http_response_code(404);
    exit('Receipt not found.');
}

$sql = "SELECT
            r.id AS receipt_id, r.receipt_number, r.issued_at,
            pr.id AS parking_record_id, pr.parking_number, pr.entry_time, pr.exit_time,
            pr.parking_duration_minutes, pr.calculated_fee, pr.status AS parking_status,
            u.id AS customer_id, u.full_name, u.email, u.phone,
            v.vehicle_number, v.vehicle_type, v.brand, v.model, v.color,
            ps.slot_number, ps.zone,
            p.amount, p.payment_method, p.payment_status, p.transaction_reference, p.paid_at,
            s.full_name AS staff_name
        FROM receipts r
        JOIN parking_records pr ON pr.id = r.parking_record_id
        JOIN users u ON u.id = r.customer_id
        JOIN vehicles v ON v.id = pr.vehicle_id
        JOIN parking_slots ps ON ps.id = pr.slot_id
        JOIN payments p ON p.id = r.payment_id
        LEFT JOIN users s ON s.id = p.recorded_by
        WHERE r.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$receiptId]);
$receipt = $stmt->fetch();

if (!$receipt) {
    http_response_code(404);
    exit('Receipt not found.');
}

$role = $_SESSION['user']['role'] ?? '';
if ($role === 'customer' && (int) $receipt['customer_id'] !== (int) $_SESSION['user']['id']) {
    http_response_code(403);
    exit('Access denied.');
}
if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$duration = (int) ($receipt['parking_duration_minutes'] ?? 0);
$hours = intdiv($duration, 60);
$minutes = $duration % 60;
$durationText = $hours > 0 ? $hours . ' hr' . ($hours !== 1 ? 's' : '') : '';
if ($minutes > 0 || $hours === 0) {
    $durationText .= ($durationText ? ' ' : '') . $minutes . ' min' . ($minutes !== 1 ? 's' : '');
}

$backUrl = $role === 'admin' ? 'admin/receipts.php' : ($role === 'staff' ? 'staff/receipts.php' : 'customer/receipts.php');
$autoPrint = isset($_GET['autoprint']) && $_GET['autoprint'] === '1';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt <?= e($receipt['receipt_number']) ?> | SmartPark</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            max-width: 760px;
            margin: 24px auto 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .back {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db !important;
        }

        .print {
            background: #1d4ed8;
            color: #fff;
        }

        .receipt {
            width: 760px;
            max-width: calc(100% - 30px);
            margin: 0 auto 35px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .10);
            padding: 38px;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            gap: 25px;
            padding-bottom: 22px;
            border-bottom: 2px solid #111827;
        }

        .brand {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        .brand-sub {
            color: #6b7280;
            margin-top: 5px;
            font-size: 13px;
        }

        .receipt-title {
            text-align: right;
        }

        .receipt-title h1 {
            margin: 0 0 7px;
            font-size: 22px;
        }

        .receipt-number {
            font-weight: 700;
            color: #1d4ed8;
            font-size: 14px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            padding: 22px 0;
        }

        .meta-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 14px;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .value {
            font-size: 14px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            padding: 12px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6b7280;
        }

        .amount-row td {
            border-bottom: 0;
            padding-top: 20px;
            font-size: 17px;
            font-weight: 800;
        }

        .amount-row td:last-child {
            color: #1d4ed8;
            text-align: right;
            font-size: 24px;
        }

        .payment {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 650px) {
            .receipt {
                padding: 22px;
            }

            .receipt-header,
            .meta {
                grid-template-columns: 1fr;
                display: block;
            }

            .receipt-title {
                text-align: left;
                margin-top: 18px;
            }

            .meta-box {
                margin-bottom: 10px;
            }

            .toolbar {
                margin-left: 15px;
                margin-right: 15px;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .receipt {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 15px;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a class="back" href="<?= BASE_URL . e($backUrl) ?>">← Back to Receipts</a>
        <button class="print" type="button" onclick="window.print()">🖨 Print Receipt</button>
    </div>

    <main class="receipt" id="printableReceipt">
        <div class="receipt-header">
            <div>
                <div class="brand">SmartPark</div>
                <div class="brand-sub">Vehicle Parking Management System</div>
            </div>
            <div class="receipt-title">
                <h1>PAYMENT RECEIPT</h1>
                <div class="receipt-number"><?= e($receipt['receipt_number']) ?></div>
                <div style="font-size:12px;color:#6b7280;margin-top:5px;">Issued: <?= e($receipt['issued_at']) ?></div>
            </div>
        </div>

        <div class="meta">
            <div class="meta-box">
                <div class="label">Customer</div>
                <div class="value"><?= e($receipt['full_name']) ?></div>
                <?php if (!empty($receipt['phone'])): ?>
                    <div style="font-size:12px;color:#6b7280;margin-top:4px;">Phone: <?= e($receipt['phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['email'])): ?>
                    <div style="font-size:12px;color:#6b7280;margin-top:3px;">Email: <?= e($receipt['email']) ?></div>
                <?php endif; ?>
            </div>
            <div class="meta-box">
                <div class="label">Parking</div>
                <div class="value"><?= e($receipt['parking_number']) ?></div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">Status:
                    <?= e(ucfirst($receipt['parking_status'])) ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Parking Detail</th>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vehicle</td>
                    <td><?= e($receipt['vehicle_number']) ?> —
                        <?= e(ucfirst($receipt['vehicle_type'])) ?><?php if ($receipt['brand'] || $receipt['model']): ?>
                            (<?= e(trim(($receipt['brand'] ?? '') . ' ' . ($receipt['model'] ?? ''))) ?>)<?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Color</td>
                    <td><?= e($receipt['color'] ?: '—') ?></td>
                </tr>
                <tr>
                    <td>Parking Slot</td>
                    <td><?= e($receipt['slot_number']) ?><?= !empty($receipt['zone']) ? ' — Zone ' . e($receipt['zone']) : '' ?>
                    </td>
                </tr>
                <tr>
                    <td>Entry Time</td>
                    <td><?= e($receipt['entry_time']) ?></td>
                </tr>
                <tr>
                    <td>Exit Time</td>
                    <td><?= e($receipt['exit_time'] ?: '—') ?></td>
                </tr>
                <tr>
                    <td>Parking Duration</td>
                    <td><?= e($durationText) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="payment">
            <div style="display:flex;justify-content:space-between;gap:15px;align-items:center;">
                <div><strong>Payment</strong>
                    <div style="font-size:12px;color:#6b7280;margin-top:4px;">Method:
                        <?= e(ucfirst($receipt['payment_method'])) ?><?php if ($receipt['transaction_reference']): ?> ·
                            Ref: <?= e($receipt['transaction_reference']) ?><?php endif; ?></div>
                </div>
                <span class="status"><?= e($receipt['payment_status']) ?></span>
            </div>
        </div>

        <table>
            <tbody>
                <tr class="amount-row">
                    <td>Total Parking Fee</td>
                    <td><?= money((float) $receipt['amount']) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Thank you for using SmartPark.<br>
            Please keep this receipt for your parking record.<br>
            Recorded by: <?= e($receipt['staff_name'] ?: 'System') ?>
        </div>
    </main>
    <?php if ($autoPrint): ?>
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });</script>
    <?php endif; ?>
</body>

</html>