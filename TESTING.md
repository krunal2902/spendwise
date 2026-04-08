# SpendWise: End-to-End Testing Guide

This guide breaks down exactly how to test every single operational dimension of SpendWise, segmented cleanly by the original 4-Part master rollout.

---

## 🟢 Part 1: Core Mechanics 

### 1. Account Operations
- **Verify**: Head into `Finance > Accounts` and generate an account (e.g. "Primary Bank"). Give it an opening initial sum.
- **Test**: Notice the balance visibly reflects your input explicitly on the core Dashboard.

### 2. Category Structuring
- **Verify**: Head to `Settings > Categories`. Toggle the "Is Locked" parameter on a dummy category.
- **Test**: Attempting to select that specific category when submitting an Expense will reject the database insertion directly.

### 3. Income & Expenses
- **Verify**: Fire a new +$500 Income into an Account, followed by a -$200 Expense.
- **Test**: Validate that the Account natively updates its internal numeric state, and that the Global Dashboard calculates the comparative `$300` Net Savings immediately.

### 4. Transfers
- **Verify**: Generate a secondary account (e.g. "Secret Stash"). Execute a Transfer shifting $100 from "Primary" into "Stash".
- **Test**: Check account indices individually. "Primary" must have explicitly drafted money inversely proportionate to "Stash" injecting it seamlessly without tripping any "Income" classifications.

---

## 🟡 Part 2: Insights & Budgets

### 1. Hard Budgets 
- **Verify**: Establish a Budget parameter allocating $100 strictly towards an "Entertainment" category limit.
- **Test**: Route two $60 expenses into the Entertainment category. Ensure the resulting Dashboard logic explicitly flags the $20 overrun via bright red threshold UI indicators.

### 2. Analytical Integrity 
- **Verify**: Move into `Analytics > Reports`. 
- **Test**: Select independent date parameters. The generated Chart.js pie widgets should actively recalculate proportional size geometry matching precisely exactly the date bounds mapped against category clusters.

---

## 🔵 Part 3: Collaborative Routing

### 1. Group Formation
- **Verify**: Head to `Groups`. Create a "Vacation Fund". 
- **Test**: Copy the system-generated 8-character hashed Token. Act as an independent user and input that hashed token directly via the *Invitation Center* linking you immediately together.

### 2. Shared Ledger Splits
- **Verify**: As User A, log a $100 "Group Expense" marking a 50/50 Exact Split against User B. 
- **Test**: Visit `Balances`. The resolution algorithm must explicitly dictate: **User B Owes User A: $50**.

### 3. Settlement Resolution
- **Verify**: Act as User B. Lodge a "Settlement" claiming a $50 cash repayment sent to User A.
- **Test**: The native `Balances` index returns to absolute `$0.00` zeroes.

---

## 🟣 Part 4: Restrictions & Deployments

### 1. Automated Health & Rule Checking 
- **Verify**: Head to `Settings > Alert Rules`. Establish a "Large Expense Flag" rule mapping anything greater than `$1,000`. 
- **Test**: Submitting a `$2,500` Expense will silently process, immediately firing a numeric pill badge onto the Navbar Bell. Upon clicking the bell, a timestamped timeline string is registered.

### 2. Modifiers: Privacy Mode
- **Verify**: Traverse internally to `System Settings` via the Sidebar. Enable the "Privacy Mode" toggle switch.
- **Test**: Opening `Dashboard` returns wildly blurred aesthetic components rendering exact balance structures useless on an overhead. Validate that active hovering smoothly restores visual integrity momentarily.

### 3. Modifiers: Emergency Lock
- **Verify**: Slide the "Emergency Mode" toggle to active. Identify an explicitly non-essential visual category.
- **Test**: The GUI explicitly warns you via a deep-red unmissable banner. Triggering a raw database map applying an expense heavily restricted by that category yields an immediate constraint failure preventing the outbound flow.

### 4. Extractions (PDF/CSV)
- **Verify**: Visit `Reports`. Hit "Download PDF Monthly Statement".
- **Test**: The resulting DOMPDF physical rendering constructs flawlessly parsed A4-dimension charts retaining exact proportional width mapping out independent Expense layouts without CSS distortions.
