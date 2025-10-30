<?php

namespace Vortex\Sales\Services;

use Vortex\Sales\Models\Invoice;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class InvoicePdfService
{
    /**
     * Generate PDF for invoice
     *
     * @param Invoice $invoice
     * @return string PDF file path
     * @throws MpdfException
     */
    public function generate(Invoice $invoice): string
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $html = $this->generateHtml($invoice);
        
        $mpdf->WriteHTML($html);
        
        $filename = storage_path('app/invoices/' . $invoice->invoice_number . '.pdf');
        
        // Ensure directory exists
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }
        
        $mpdf->Output($filename, 'F');
        
        return $filename;
    }

    /**
     * Generate and download PDF
     *
     * @param Invoice $invoice
     * @param bool $inline
     * @return void
     * @throws MpdfException
     */
    public function download(Invoice $invoice, bool $inline = false): void
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $html = $this->generateHtml($invoice);
        
        $mpdf->WriteHTML($html);
        
        $filename = $invoice->invoice_number . '.pdf';
        $destination = $inline ? 'I' : 'D'; // I = inline, D = download
        
        $mpdf->Output($filename, $destination);
    }

    /**
     * Generate HTML content for invoice
     *
     * @param Invoice $invoice
     * @return string
     */
    protected function generateHtml(Invoice $invoice): string
    {
        $order = $invoice->order;
        $items = $order->items ?? [];
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Invoice ' . htmlspecialchars($invoice->invoice_number) . '</title>
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
                    border-bottom: 3px solid #3b82f6;
                }
                .company-name {
                    font-size: 28px;
                    font-weight: bold;
                    color: #1e40af;
                    margin-bottom: 5px;
                }
                .invoice-title {
                    font-size: 20px;
                    color: #64748b;
                    margin-top: 10px;
                }
                .info-section {
                    margin-bottom: 30px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                }
                .info-box {
                    width: 48%;
                }
                .info-label {
                    font-weight: bold;
                    color: #64748b;
                    font-size: 11px;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                .info-value {
                    font-size: 13px;
                    color: #1e293b;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                table thead {
                    background-color: #f1f5f9;
                }
                table th {
                    padding: 12px 8px;
                    text-align: left;
                    font-weight: bold;
                    color: #475569;
                    font-size: 11px;
                    text-transform: uppercase;
                    border-bottom: 2px solid #cbd5e1;
                }
                table td {
                    padding: 10px 8px;
                    border-bottom: 1px solid #e2e8f0;
                }
                table tbody tr:last-child td {
                    border-bottom: none;
                }
                .text-right {
                    text-align: right;
                }
                .totals {
                    margin-top: 30px;
                    float: right;
                    width: 300px;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #e2e8f0;
                }
                .total-row:last-child {
                    border-bottom: none;
                    border-top: 2px solid #3b82f6;
                    font-weight: bold;
                    font-size: 16px;
                    margin-top: 10px;
                    padding-top: 12px;
                }
                .total-label {
                    color: #64748b;
                }
                .total-value {
                    font-weight: bold;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .status-pending {
                    background-color: #fef3c7;
                    color: #92400e;
                }
                .status-sent {
                    background-color: #dbeafe;
                    color: #1e40af;
                }
                .status-paid {
                    background-color: #d1fae5;
                    color: #065f46;
                }
                .status-cancelled {
                    background-color: #fee2e2;
                    color: #991b1b;
                }
                .notes-section {
                    margin-top: 30px;
                    padding: 15px;
                    background-color: #f8fafc;
                    border-left: 3px solid #3b82f6;
                }
                .notes-title {
                    font-weight: bold;
                    margin-bottom: 5px;
                    color: #475569;
                }
                .footer {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #e2e8f0;
                    text-align: center;
                    color: #94a3b8;
                    font-size: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-name">Vortex</div>
                <div class="invoice-title">INVOICE</div>
            </div>

            <div class="info-section">
                <table style="border: none; margin: 0;">
                    <tr>
                        <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                            <div class="info-label">Invoice Number</div>
                            <div class="info-value">' . htmlspecialchars($invoice->invoice_number) . '</div>
                            
                            <div class="info-label" style="margin-top: 15px;">Order Number</div>
                            <div class="info-value">' . htmlspecialchars($order->order_number) . '</div>
                            
                            <div class="info-label" style="margin-top: 15px;">Status</div>
                            <div class="status-badge status-' . $invoice->status . '">' . strtoupper($invoice->status) . '</div>
                        </td>
                        <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                            <div class="info-label">Issue Date</div>
                            <div class="info-value">' . $invoice->issue_date->format('F d, Y') . '</div>
                            
                            <div class="info-label" style="margin-top: 15px;">Due Date</div>
                            <div class="info-value">' . $invoice->due_date->format('F d, Y') . '</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="info-section">
                <div class="info-label">Bill To</div>
                <div class="info-value">
                    <strong>' . htmlspecialchars($order->customer_name ?? 'Guest') . '</strong><br>
                    ' . htmlspecialchars($order->customer_email) . '<br>
                    ' . ($order->phone ? htmlspecialchars($order->phone) . '<br>' : '') . '
                    ' . ($order->billing_address ? nl2br(htmlspecialchars($order->billing_address)) : '') . '
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($items as $item) {
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item->product_name) . '</td>
                        <td class="text-right">' . number_format($item->quantity) . '</td>
                        <td class="text-right">₹' . number_format($item->price, 2) . '</td>
                        <td class="text-right">₹' . number_format($item->quantity * $item->price, 2) . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="totals">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">₹' . number_format($invoice->subtotal, 2) . '</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Tax:</span>
                    <span class="total-value">₹' . number_format($invoice->tax, 2) . '</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Shipping:</span>
                    <span class="total-value">₹' . number_format($invoice->shipping, 2) . '</span>
                </div>
                <div class="total-row">
                    <span>Total Amount:</span>
                    <span>₹' . number_format($invoice->total, 2) . '</span>
                </div>
            </div>

            <div style="clear: both;"></div>';

        if ($invoice->notes) {
            $html .= '
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div>' . nl2br(htmlspecialchars($invoice->notes)) . '</div>
            </div>';
        }

        $html .= '
            <div class="footer">
                Thank you for your business!<br>
                This is a computer-generated invoice and does not require a signature.
            </div>
        </body>
        </html>';

        return $html;
    }
}
