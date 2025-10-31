<?php

namespace Vortex\Sales\Services;

use Vortex\Sales\Models\CreditMemo;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class CreditMemoPdfService
{
    /**
     * Generate PDF for credit memo
     */
    public function generate(CreditMemo $creditMemo): string
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $html = $this->generateHtml($creditMemo);
        
        $mpdf->WriteHTML($html);
        
        $filename = storage_path('app/credit-memos/' . $creditMemo->credit_memo_number . '.pdf');
        
        // Ensure directory exists
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }
        
        $mpdf->Output($filename, 'F');
        
        return $filename;
    }

    /**
     * Generate and download PDF
     */
    public function download(CreditMemo $creditMemo, bool $inline = false)
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $html = $this->generateHtml($creditMemo);
        
        $mpdf->WriteHTML($html);
        
        $filename = $creditMemo->credit_memo_number . '.pdf';
        $destination = $inline ? 'I' : 'D';
        
        return $mpdf->Output($filename, $destination);
    }

    /**
     * Generate HTML content for credit memo
     */
    protected function generateHtml(CreditMemo $creditMemo): string
    {
        $order = $creditMemo->order;
        $items = $creditMemo->items;
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Credit Memo ' . htmlspecialchars($creditMemo->credit_memo_number) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    color: #333;
                    line-height: 1.5;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #dc2626;
                }
                .header h1 {
                    color: #dc2626;
                    font-size: 28px;
                    margin: 0 0 10px 0;
                }
                .company-info {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .company-info h2 {
                    margin: 0 0 5px 0;
                    font-size: 18px;
                }
                .info-section {
                    margin-bottom: 20px;
                }
                .info-row {
                    overflow: hidden;
                    margin-bottom: 20px;
                }
                .info-col {
                    width: 48%;
                    float: left;
                }
                .info-col:last-child {
                    float: right;
                }
                .info-col h3 {
                    font-size: 14px;
                    margin: 0 0 10px 0;
                    color: #dc2626;
                }
                .info-col p {
                    margin: 3px 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                table thead {
                    background-color: #dc2626;
                    color: white;
                }
                table th {
                    padding: 10px;
                    text-align: left;
                    font-weight: bold;
                }
                table td {
                    padding: 10px;
                    border-bottom: 1px solid #e5e7eb;
                }
                table tbody tr:hover {
                    background-color: #f9fafb;
                }
                .text-right {
                    text-align: right;
                }
                .totals {
                    float: right;
                    width: 40%;
                    margin-top: 20px;
                }
                .totals table {
                    margin: 0;
                }
                .totals td {
                    padding: 8px;
                    border: none;
                }
                .totals .grand-total {
                    font-weight: bold;
                    font-size: 16px;
                    background-color: #dc2626;
                    color: white;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 11px;
                    color: #6b7280;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 20px;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                    background-color: #fef3c7;
                    color: #92400e;
                }
                .notes {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9fafb;
                    border-left: 4px solid #dc2626;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>CREDIT MEMO</h1>
                <p style="font-size: 14px; margin: 5px 0;">Credit Memo #' . htmlspecialchars($creditMemo->credit_memo_number) . '</p>
                <span class="status-badge">' . strtoupper($creditMemo->status) . '</span>
            </div>

            <div class="company-info">
                <h2>' . htmlspecialchars(config('app.name')) . '</h2>
                <p>' . config('app.url') . '</p>
            </div>

            <div class="info-row">
                <div class="info-col">
                    <h3>Customer Information</h3>
                    <p><strong>' . htmlspecialchars($order->customer_name ?? 'N/A') . '</strong></p>
                    <p>' . htmlspecialchars($order->customer_email ?? '') . '</p>
                    <p>' . htmlspecialchars($order->billing_address ?? '') . '</p>
                </div>
                <div class="info-col">
                    <h3>Refund Details</h3>
                    <p><strong>Date Issued:</strong> ' . $creditMemo->created_at->format('M d, Y') . '</p>
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order->order_number) . '</p>
                    ' . ($creditMemo->refunded_at ? '<p><strong>Refunded:</strong> ' . $creditMemo->refunded_at->format('M d, Y') . '</p>' : '') . '
                    <p><strong>Refund Method:</strong> ' . ucfirst(str_replace('_', ' ', $creditMemo->refund_method ?? 'N/A')) . '</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">Qty Refunded</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($items as $item) {
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item->product_name) . '</td>
                        <td>' . htmlspecialchars($item->sku ?? '-') . '</td>
                        <td class="text-right">' . number_format($item->qty_refunded) . '</td>
                        <td class="text-right">₹' . number_format($item->price, 2) . '</td>
                        <td class="text-right">₹' . number_format($item->tax_amount, 2) . '</td>
                        <td class="text-right">₹' . number_format($item->row_total, 2) . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="totals">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-right">₹' . number_format($creditMemo->subtotal, 2) . '</td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-right">₹' . number_format($creditMemo->tax_amount, 2) . '</td>
                    </tr>';

        if ($creditMemo->shipping_amount > 0) {
            $html .= '
                    <tr>
                        <td>Shipping Refund:</td>
                        <td class="text-right">₹' . number_format($creditMemo->shipping_amount, 2) . '</td>
                    </tr>';
        }

        if ($creditMemo->discount_amount > 0) {
            $html .= '
                    <tr>
                        <td>Discount:</td>
                        <td class="text-right">-₹' . number_format($creditMemo->discount_amount, 2) . '</td>
                    </tr>';
        }

        if ($creditMemo->adjustment_positive > 0) {
            $html .= '
                    <tr>
                        <td>Adjustment (Fee):</td>
                        <td class="text-right">-₹' . number_format($creditMemo->adjustment_positive, 2) . '</td>
                    </tr>';
        }

        if ($creditMemo->adjustment_negative > 0) {
            $html .= '
                    <tr>
                        <td>Adjustment (Refund):</td>
                        <td class="text-right">₹' . number_format($creditMemo->adjustment_negative, 2) . '</td>
                    </tr>';
        }

        $html .= '
                    <tr class="grand-total">
                        <td>Total Refund:</td>
                        <td class="text-right">₹' . number_format($creditMemo->grand_total, 2) . '</td>
                    </tr>
                </table>
            </div>

            <div style="clear: both;"></div>';

        if ($creditMemo->notes) {
            $html .= '
            <div class="notes">
                <strong>Notes:</strong><br>
                ' . nl2br(htmlspecialchars($creditMemo->notes)) . '
            </div>';
        }

        $html .= '
            <div class="footer">
                <p><strong>' . htmlspecialchars(config('app.name')) . '</strong></p>
                <p>This is a computer-generated credit memo and does not require a signature.</p>
                <p>&copy; ' . date('Y') . ' ' . htmlspecialchars(config('app.name')) . '. All rights reserved.</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
