<?php

namespace Cartxis\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Sales\Models\CreditMemo;
use Cartxis\Sales\Repositories\CreditMemoRepository;
use Cartxis\Sales\Services\CreditMemoService;
use Cartxis\Sales\Services\CreditMemoPdfService;
use Cartxis\Shop\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreditMemoController extends Controller
{
    protected CreditMemoRepository $repository;
    protected CreditMemoService $service;
    protected CreditMemoPdfService $pdfService;

    public function __construct(
        CreditMemoRepository $repository,
        CreditMemoService $service,
        CreditMemoPdfService $pdfService
    ) {
        $this->repository = $repository;
        $this->service = $service;
        $this->pdfService = $pdfService;
    }

    /**
     * Display credit memo listing
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'refund_status' => $request->input('refund_status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'min_amount' => $request->input('min_amount'),
            'max_amount' => $request->input('max_amount'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_direction' => $request->input('sort_direction', 'desc'),
        ];

        $perPage = $request->input('per_page', 15);
        $creditMemos = $this->repository->paginate($perPage, $filters);

        return Inertia::render('Admin/Sales/CreditMemos/Index', [
            'creditMemos' => $creditMemos,
            'filters' => $filters,
            'statistics' => $this->repository->getStats(),
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'complete', 'label' => 'Complete'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'refundStatuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'processed', 'label' => 'Processed'],
                ['value' => 'failed', 'label' => 'Failed'],
            ],
        ]);
    }

    /**
     * Display credit memo details
     */
    public function show(string|int $id): Response
    {
        $creditMemo = $this->repository->find((int) $id);

        if (!$creditMemo) {
            abort(404, 'Credit memo not found');
        }

        return Inertia::render('Admin/Sales/CreditMemos/Show', [
            'creditMemo' => $creditMemo,
        ]);
    }

    /**
     * Show create credit memo form
     */
    public function create(Request $request, string|int $orderId): Response|\Illuminate\Http\RedirectResponse
    {
        $order = Order::with(['user', 'items.product'])->findOrFail((int) $orderId);

        if (!$order->isPaid()) {
            return back()->with('error', 'Cannot create credit memo for unpaid order');
        }

        $refundableItems = $this->service->getRefundableItems($order);
        $maxRefundable = $this->service->getMaxRefundableAmount($order);

        if ($refundableItems->isEmpty()) {
            return back()->with('error', 'No items available for refund');
        }

        return Inertia::render('Admin/Sales/CreditMemos/Create', [
            'order' => $order,
            'refundableItems' => $refundableItems,
            'maxRefundableAmount' => $maxRefundable,
        ]);
    }

    /**
     * Store new credit memo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.restore_stock' => 'boolean',
            'refund_shipping' => 'nullable|numeric|min:0',
            'adjustment_positive' => 'nullable|numeric|min:0',
            'adjustment_negative' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|string|in:original_payment,store_credit,manual',
            'restore_inventory' => 'boolean',
            'process_refund' => 'boolean',
            'send_email' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);

            // Format items for service
            $items = [];
            foreach ($validated['items'] as $item) {
                $items[$item['order_item_id']] = [
                    'qty' => $item['qty'],
                    'restore_stock' => $item['restore_stock'] ?? true,
                ];
            }

            $creditMemo = $this->service->createFromOrder($order, $items, $validated);

            return redirect()
                ->route('admin.sales.credit-memos.show', $creditMemo->id)
                ->with('success', 'Credit memo created successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit credit memo form
     */
    public function edit(string|int $id): Response|\Illuminate\Http\RedirectResponse
    {
        $creditMemo = $this->repository->find((int) $id);

        if (!$creditMemo) {
            abort(404, 'Credit memo not found');
        }

        if (!$creditMemo->canBeEdited()) {
            return back()->with('error', 'Credit memo cannot be edited');
        }

        return Inertia::render('Admin/Sales/CreditMemos/Edit', [
            'creditMemo' => $creditMemo,
        ]);
    }

    /**
     * Update credit memo
     */
    public function update(Request $request, string|int $id)
    {
        $validated = $request->validate([
            'adjustment_positive' => 'nullable|numeric|min:0',
            'adjustment_negative' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|string|in:original_payment,store_credit,manual',
            'notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->update($id, $validated);

            return back()->with('success', 'Credit memo updated successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete credit memo
     */
    public function destroy(string|int $id)
    {
        try {
            $this->service->delete((int) $id);

            return redirect()
                ->route('admin.sales.credit-memos.index')
                ->with('success', 'Credit memo deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process refund for credit memo
     */
    public function processRefund(string|int $id)
    {
        try {
            $creditMemo = $this->repository->find((int) $id);
            
            if (!$creditMemo) {
                return back()->with('error', 'Credit memo not found');
            }

            $this->service->processRefund($creditMemo);

            return back()->with('success', 'Refund processed successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel credit memo
     */
    public function cancel(Request $request, string|int $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->cancel($id, $validated['reason'] ?? null);

            return back()->with('success', 'Credit memo cancelled successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send credit memo email
     */
    public function sendEmail(string|int $id)
    {
        try {
            $creditMemo = $this->repository->find((int) $id);
            
            if (!$creditMemo) {
                return back()->with('error', 'Credit memo not found');
            }

            // Email is sent through the service
            $this->service->sendCreditMemoEmail($creditMemo);

            return back()->with('success', 'Credit memo emailed successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download credit memo as PDF
     */
    public function downloadPdf(string|int $id)
    {
        try {
            $creditMemo = $this->repository->find((int) $id);
            
            if (!$creditMemo) {
                return back()->with('error', 'Credit memo not found');
            }

            return $this->pdfService->download($creditMemo);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk delete credit memos
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:credit_memos,id',
        ]);

        try {
            $deleted = $this->repository->bulkDelete($validated['ids']);

            return back()->with('success', "{$deleted} credit memo(s) deleted successfully");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
