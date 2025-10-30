<?php

namespace Vortex\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vortex\Sales\Models\Invoice;
use Vortex\Sales\Repositories\InvoiceRepository;
use Vortex\Sales\Services\InvoiceService;
use Vortex\Sales\Services\InvoicePdfService;
use Vortex\Sales\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    protected InvoiceRepository $invoiceRepository;
    protected InvoiceService $invoiceService;
    protected InvoicePdfService $pdfService;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceService $invoiceService,
        InvoicePdfService $pdfService
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display invoice listing
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'min_amount' => $request->input('min_amount'),
            'max_amount' => $request->input('max_amount'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_direction' => $request->input('sort_direction', 'desc'),
        ];

        $perPage = $request->input('per_page', 15);
        $invoices = $this->invoiceRepository->paginate($perPage, $filters);

        return Inertia::render('Admin/Sales/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Display invoice details
     */
    public function show(int $id): Response
    {
        $invoice = $this->invoiceRepository->find($id);

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $invoiceData = $this->invoiceService->generateInvoiceData($invoice);

        return Inertia::render('Admin/Sales/Invoices/Show', [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Create invoice from order
     */
    public function createFromOrder(Request $request, int $orderId)
    {
        $request->validate([
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $order = \Vortex\Shop\Models\Order::findOrFail($orderId);
            
            $invoice = $this->invoiceService->createFromOrder($order, [
                'due_date' => $request->input('due_date'),
                'notes' => $request->input('notes'),
            ]);

            return redirect()
                ->route('admin.sales.invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update invoice
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->invoiceService->update($id, $request->only(['due_date', 'notes']));

            return back()->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(int $id)
    {
        try {
            $this->invoiceService->markAsSent($id);

            return back()->with('success', 'Invoice marked as sent');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(int $id)
    {
        try {
            $this->invoiceService->markAsPaid($id);

            return back()->with('success', 'Invoice marked as paid');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel invoice
     */
    public function cancel(int $id)
    {
        try {
            $this->invoiceService->cancel($id);

            return back()->with('success', 'Invoice cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete invoice
     */
    public function destroy(int $id)
    {
        try {
            $this->invoiceService->delete($id);

            return redirect()
                ->route('admin.sales.invoices.index')
                ->with('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(int $id)
    {
        try {
            $invoice = $this->invoiceRepository->find($id);
            
            if (!$invoice) {
                return back()->with('error', 'Invoice not found');
            }
            
            $this->pdfService->download($invoice, false);
            
            return response()->noContent();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Email invoice to customer
     */
    public function sendEmail(int $id)
    {
        try {
            $invoice = $this->invoiceRepository->find($id);
            
            if (!$invoice) {
                return back()->with('error', 'Invoice not found');
            }
            
            // Generate PDF
            $pdfPath = $this->pdfService->generate($invoice);
            
            // Send email with PDF attachment
            Mail::to($invoice->order->customer_email)
                ->send(new InvoiceMail($invoice, $pdfPath));
            
            // Mark as sent if it was pending
            if ($invoice->status === 'pending') {
                $this->invoiceService->markAsSent($id);
            }
            
            // Clean up PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
            
            return back()->with('success', 'Invoice emailed successfully to ' . $invoice->order->customer_email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
