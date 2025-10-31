# Credit Memos Filter Testing Guide

## Test Data Overview
We now have **7 credit memos** with diverse attributes:

| Credit Memo Number | Status | Method | Amount | Date |
|-------------------|--------|--------|--------|------|
| CRM-20251031-943269 | cancelled | online | ₹0.00 | Oct 31 (today) |
| CRM-20251031-002473 | complete | online | ₹29.99 | Oct 31 (today) |
| CRM-20251031-190537 | complete | online | ₹5.40 | Oct 31 (today) |
| CRM-20251031-TEST01 | pending | offline | ₹10.00 | Oct 26 (5 days ago) |
| CRM-20251031-TEST02 | complete | store_credit | ₹15.50 | Oct 28 (3 days ago) |
| CRM-20251031-TEST03 | pending | manual | ₹50.00 | Oct 30 (yesterday) |
| CRM-20251031-TEST04 | complete | original_payment | ₹25.00 | Oct 29 (2 days ago) |

---

## Filter Tests to Perform

### 1. **Search Filter**
Navigate to: `/admin/sales/credit-memos`

**Test 1.1:** Search by Credit Memo Number
- Type: `CRM-20251031-TEST01`
- **Expected:** Shows only TEST01 (₹10.00, pending)

**Test 1.2:** Search by Order Number
- Type: `ORD-251028-422057`
- **Expected:** Shows all 7 credit memos (all belong to same order)

**Test 1.3:** Search by Customer Email
- Type: `saju.g`
- **Expected:** Shows all 7 credit memos

**Test 1.4:** Partial Search
- Type: `TEST`
- **Expected:** Shows 4 TEST credit memos (TEST01-TEST04)

---

### 2. **Status Filter**
**Test 2.1:** Filter by Pending
- Select dropdown: "Pending"
- **Expected:** Shows 2 credit memos (TEST01, TEST03)

**Test 2.2:** Filter by Complete
- Select dropdown: "Complete"
- **Expected:** Shows 4 credit memos (002473, 190537, TEST02, TEST04)

**Test 2.3:** Filter by Cancelled
- Select dropdown: "Cancelled"
- **Expected:** Shows 1 credit memo (943269)

**Test 2.4:** All Statuses
- Select dropdown: "All Statuses"
- **Expected:** Shows all 7 credit memos

---

### 3. **Advanced Filters** (Click "Filters" button)

#### 3.1: Refund Method Filter
**Test 3.1.1:** Online Method
- Refund Method: "Online"
- **Expected:** 3 credit memos (943269, 002473, 190537)

**Test 3.1.2:** Offline Method
- Refund Method: "Offline"
- **Expected:** 1 credit memo (TEST01)

**Test 3.1.3:** Store Credit Method
- Refund Method: "Store Credit"
- **Expected:** 1 credit memo (TEST02)

**Test 3.1.4:** Manual Method
- Refund Method: "Manual"
- **Expected:** 1 credit memo (TEST03)

**Test 3.1.5:** Original Payment Method
- Refund Method: "Original Payment"
- **Expected:** 1 credit memo (TEST04)

---

#### 3.2: Date Range Filter
**Test 3.2.1:** Last 3 days
- Date From: `2025-10-28`
- Date To: `2025-10-31`
- **Expected:** 6 credit memos (all except TEST01 from Oct 26)

**Test 3.2.2:** Specific Date
- Date From: `2025-10-31`
- Date To: `2025-10-31`
- **Expected:** 3 credit memos (943269, 002473, 190537)

**Test 3.2.3:** Old Records
- Date From: `2025-10-26`
- Date To: `2025-10-27`
- **Expected:** 1 credit memo (TEST01)

---

#### 3.3: Amount Range Filter
**Test 3.3.1:** Small Amounts (Under ₹10)
- Min Amount: `0`
- Max Amount: `10`
- **Expected:** 2 credit memos (943269 ₹0.00, 190537 ₹5.40)

**Test 3.3.2:** Medium Amounts (₹10-₹30)
- Min Amount: `10`
- Max Amount: `30`
- **Expected:** 4 credit memos (TEST01 ₹10.00, TEST02 ₹15.50, TEST04 ₹25.00, 002473 ₹29.99)

**Test 3.3.3:** High Amounts (Above ₹50)
- Min Amount: `50`
- Max Amount: `1000`
- **Expected:** 1 credit memo (TEST03 ₹50.00)

**Test 3.3.4:** Exact Amount Range
- Min Amount: `15`
- Max Amount: `26`
- **Expected:** 2 credit memos (TEST02 ₹15.50, TEST04 ₹25.00)

---

### 4. **Combined Filters**

**Test 4.1:** Status + Method
- Status: "Pending"
- Refund Method: "Manual"
- **Expected:** 1 credit memo (TEST03)

**Test 4.2:** Status + Amount Range
- Status: "Complete"
- Min Amount: `20`
- Max Amount: `30`
- **Expected:** 2 credit memos (TEST04 ₹25.00, 002473 ₹29.99)

**Test 4.3:** Date + Amount + Method
- Date From: `2025-10-28`
- Max Amount: `20`
- Refund Method: "Store Credit"
- **Expected:** 1 credit memo (TEST02 ₹15.50)

**Test 4.4:** Search + Status
- Search: `TEST`
- Status: "Complete"
- **Expected:** 2 credit memos (TEST02, TEST04)

---

### 5. **Reset Filter**

**Test 5.1:** Apply Multiple Filters then Reset
1. Set Status: "Pending"
2. Set Min Amount: `20`
3. Click "Reset" button
- **Expected:** All filters cleared, shows all 7 credit memos

---

## Statistics Verification

After each filter, check that the statistics cards update correctly:

- **Total**: Count of filtered results
- **Pending**: Count of pending in filtered results
- **Refunded**: Count of complete in filtered results
- **Total Amount**: Sum of complete credit memos' amounts in filtered results

---

## URL Parameters

Verify that filters update the URL correctly:
- Search: `?search=TEST`
- Status: `?status=pending`
- Combined: `?status=complete&min_amount=10&max_amount=30`

The URL should allow bookmarking and sharing filtered views.

---

## Quick Test Checklist

- [ ] Search by credit memo number works
- [ ] Search by order number works
- [ ] Search by customer email works
- [ ] Status filter (All Statuses) works
- [ ] Status filter (Pending) works
- [ ] Status filter (Complete) works
- [ ] Status filter (Cancelled) works
- [ ] Refund method filter works for all options
- [ ] Date range filter works
- [ ] Amount range filter works
- [ ] Combined filters work together
- [ ] Reset button clears all filters
- [ ] Statistics update with filters
- [ ] URL parameters are correct
- [ ] Pagination works with filters (if more than 15 records)

---

**Note:** MCP browser automation doesn't work reliably with Vue.js/Inertia.js apps. Please test these filters manually in your browser at `https://vortex.test/admin/sales/credit-memos`
