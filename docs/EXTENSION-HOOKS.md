# Extension hooks (commerce / core)

Thin hooks for commercial extensions (Multi-Vendor, Delivery, etc.). Extensions must live under `extension/<code>/` and are **not** shipped in the public Cartxis git tree.

## Settings

`SettingService::set($key, $value, $type = 'string', $group = null, $extensionCode = null)`

When `$extensionCode` is set, uninstall via `cartxis:extensions:uninstall` deletes those settings automatically (`deleteByExtension`).

## Actions (`do_action`)

| Hook | Args | Fired from |
|------|------|------------|
| `checkout.order.creating` | `$data`, `$cartItems`, `$totals` | `CheckoutService::createOrder` |
| `checkout.order.created` | `$order`, `$data`, `$cartItems` | after order + items |
| `checkout.order.placed` | `$order`, `$data`, `$cartItems` | same as created (alias for fulfillers) |
| `order.status.updated` | `$order`, `$oldStatus`, `$newStatus`, `$comment` | `OrderService::updateStatus` |
| `order.payment.updated` | `$order`, `$old`, `$new`, `$comment` | `OrderService::updatePaymentStatus` |
| `shipment.status.updated` | `$shipment`, `$oldStatus`, `$newStatus` | `ShipmentService::updateStatus` |
| `cart.item.adding` | `$itemPayload`, `$cart` | Cart web/API add |
| `product.saving` | `$validated`, `$product\|null`, `$request` | Admin product store/update |
| `product.saved` | `$product`, `$request`, `$wasRecentlyCreated` | after save |

## Filters (`apply_filters`)

| Hook | Value | Extra args |
|------|-------|------------|
| `checkout.address.validate` | `['valid'=>true,'message'=>null]` | `$shippingAddress`, `$cartItems` |
| `cart.items` | cart array | — |
| `cart.item.adding` | `$itemPayload` (may add `vendor_id`) | `$cart` |
| `cart.item.added` | new cart line | `$product`, `$itemPayload` |
| `product.query` | Eloquent builder | `$request` |
| `product.saving` | `$validated` array | `$product\|null`, `$request` |

Existing: `cart.shipping`, `cart.taxes`, `theme.shared_data`, `storefront.body.end`, `extension.*`.
