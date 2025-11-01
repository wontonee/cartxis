/**
 * Sales Module TypeScript Types
 * 
 * Type definitions for Orders, Invoices, Shipments, and Credit Memos
 */

// Order Types
export interface Order {
  id: number;
  order_number: string;
  user_id?: number;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  customer_email: string;
  customer_phone?: string;
  status: 'pending' | 'processing' | 'completed' | 'cancelled' | 'refunded' | 'failed';
  payment_status: 'pending' | 'paid' | 'failed';
  payment_method?: string;
  shipping_method?: string;
  tracking_number?: string;
  subtotal: number;
  tax: number;
  shipping_cost: number;
  discount: number;
  total: number;
  total_refunded?: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  items: OrderItem[];
  addresses?: Address[];
  shipments?: Shipment[];
  invoices?: Invoice[];
  credit_memos?: CreditMemo[];
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id?: number;
  product_sku?: string;
  product_name: string;
  product_image?: string;
  quantity: number;
  qty_shipped?: number;
  qty_returned?: number;
  price: number;
  total: number;
  tax_amount: number;
  discount_amount: number;
  options?: Record<string, any>;
  product?: {
    id: number;
    name: string;
    sku: string;
    quantity: number;
  };
}

export interface Address {
  id: number;
  type: 'shipping' | 'billing';
  full_name: string;
  phone: string;
  address_line1: string;
  address_line2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
}

// Invoice Types
export interface Invoice {
  id: number;
  order_id: number;
  invoice_number: string;
  status: 'pending' | 'sent' | 'paid' | 'cancelled';
  issue_date: string;
  due_date?: string;
  subtotal: number;
  tax: number;
  total: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  order?: Order;
}

// Shipment Types
export interface Shipment {
  id: number;
  order_id: number;
  shipment_number: string;
  status: 'pending' | 'shipped' | 'in_transit' | 'out_for_delivery' | 'delivered' | 'failed' | 'cancelled';
  carrier?: string;
  tracking_number?: string;
  tracking_url?: string;
  shipped_at?: string;
  delivered_at?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
  order?: Order;
  shipment_items?: ShipmentItem[];
}

export interface ShipmentItem {
  id: number;
  shipment_id: number;
  order_item_id: number;
  quantity: number;
  order_item?: OrderItem;
}

// Credit Memo Types
export interface CreditMemo {
  id: number;
  order_id: number;
  invoice_id?: number;
  credit_memo_number: string;
  status: 'pending' | 'complete' | 'cancelled';
  subtotal: number;
  discount_amount: number;
  tax_amount: number;
  shipping_amount: number;
  adjustment_positive: number;
  adjustment_negative: number;
  grand_total: number;
  refund_status: 'pending' | 'processed' | 'failed';
  refund_method: 'online' | 'offline' | 'original_payment' | 'store_credit' | 'manual';
  refunded_at?: string;
  notes?: string;
  admin_notes?: string;
  restore_inventory: boolean;
  inventory_restored_at?: string;
  created_by?: number;
  created_at: string;
  updated_at: string;
  order?: Order;
  invoice?: Invoice;
  items?: CreditMemoItem[];
}

export interface CreditMemoItem {
  id: number;
  credit_memo_id: number;
  order_item_id: number;
  product_name: string;
  sku?: string;
  qty: number;
  price: number;
  row_total: number;
  tax_amount: number;
  discount_amount: number;
  created_at: string;
  updated_at: string;
  order_item?: OrderItem;
}

export interface RefundableItem {
  order_item_id: number;
  product_name: string;
  sku?: string;
  qty_ordered: number;
  qty_refunded: number;
  qty_refundable: number;
  price: number;
  max_refund_amount: number;
}

// Filters
export interface OrderFilters {
  search?: string;
  status?: string;
  payment_status?: string;
  date_from?: string;
  date_to?: string;
  min_amount?: number;
  max_amount?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

export interface InvoiceFilters {
  search?: string;
  status?: string;
  date_from?: string;
  date_to?: string;
  min_amount?: number;
  max_amount?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

export interface ShipmentFilters {
  search?: string;
  status?: string;
  carrier?: string;
  date_from?: string;
  date_to?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

export interface CreditMemoFilters {
  search?: string;
  status?: string;
  refund_method?: 'online' | 'offline';
  date_from?: string;
  date_to?: string;
  min_amount?: number;
  max_amount?: number;
  order_id?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

// Paginated Response
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

// Statistics
export interface OrderStatistics {
  total: number;
  pending: number;
  processing: number;
  completed: number;
  cancelled: number;
  revenue: number;
  average_order_value: number;
}

export interface InvoiceStatistics {
  total: number;
  pending: number;
  sent: number;
  paid: number;
  cancelled: number;
  total_amount: number;
  pending_amount: number;
  paid_amount: number;
}

export interface ShipmentStatistics {
  total: number;
  pending: number;
  shipped: number;
  in_transit: number;
  out_for_delivery: number;
  delivered: number;
  failed: number;
  cancelled: number;
}

export interface CreditMemoStatistics {
  total: number;
  pending: number;
  refunded: number;
  cancelled: number;
  total_amount: number;
  pending_amount: number;
  refunded_amount: number;
}

// Transaction Types
export interface Transaction {
  id: number;
  order_id: number;
  invoice_id?: number;
  credit_memo_id?: number;
  transaction_number: string;
  type: 'payment' | 'refund' | 'authorization' | 'capture';
  payment_method: string;
  gateway: string;
  gateway_transaction_id?: string;
  amount: number;
  status: 'pending' | 'completed' | 'failed' | 'cancelled';
  response_data?: Record<string, any>;
  notes?: string;
  processed_at?: string;
  created_at: string;
  updated_at: string;
  order?: Order;
  invoice?: Invoice;
  credit_memo?: CreditMemo;
}

export interface TransactionFilters {
  search?: string;
  type?: string;
  status?: string;
  gateway?: string;
  payment_method?: string;
  date_from?: string;
  date_to?: string;
  amount_min?: number;
  amount_max?: number;
  order_id?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

export interface TransactionStatistics {
  total: number;
  completed: number;
  pending: number;
  failed: number;
  total_amount: number;
  payment_count: number;
  refund_count: number;
  payment_amount: number;
  refund_amount: number;
}

// Status Options
export interface StatusOption {
  value: string;
  label: string;
}
