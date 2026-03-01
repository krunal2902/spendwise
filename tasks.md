# SpendWise – Budget & Expense Management System
## Master Task Tracker

---

## PART 1 – Core System & Money Flow *(Current)*

### Phase 1: Project Setup & Authentication
- [x] Install Laravel Breeze (Blade stack)
- [x] Run `npm install && npm run build`
- [x] Add `role`, `phone`, `currency` columns to users table (migration)
- [x] Update `User` model with new fields, relationships, `isAdmin()` helper
- [x] Create `RoleMiddleware` for admin routes
- [x] Register middleware in bootstrap
- [x] Create `AdminUserSeeder` (default admin account)
- [x] Test: Registration, Login, Role-based access

### Phase 2: Accounts & Categories
- [x] Create `accounts` migration
- [x] Create `Account` model with relationships & scopes
- [x] Create `categories` migration
- [x] Create `Category` model with relationships & scopes
- [x] Create `AccountService` (create, update, delete, recalculateBalance)
- [x] Create `CategoryService` (create, update, toggle, getForUser)
- [x] Create Form Requests (StoreAccount, UpdateAccount, StoreCategory, UpdateCategory)
- [x] Create `AccountController` (full CRUD)
- [x] Create `CategoryController` (CRUD + toggle)
- [x] Create `CategorySeeder` (system default categories)
- [x] Create Blade views for Accounts (index, create, edit, show)
- [x] Create Blade views for Categories (index, create, edit)
- [x] Test: Account CRUD, Category CRUD, ownership isolation

### Phase 3: Income & Expense Management
- [x] Create `incomes` migration
- [x] Create `Income` model with relationships
- [x] Create `expenses` migration
- [x] Create `Expense` model with relationships
- [x] Create `ActivityLogService`
- [x] Create `activity_logs` migration
- [x] Create `ActivityLog` model
- [x] Create `IncomeService` (create/update/delete with balance mgmt)
- [x] Create `ExpenseService` (create/update/delete with balance guard)
- [x] Create Form Requests (StoreIncome, UpdateIncome, StoreExpense, UpdateExpense)
- [x] Create `IncomeController` and `ExpenseController`
- [x] Create Blade views for Income (index, create, edit)
- [x] Create Blade views for Expenses (index, create, edit)
- [x] Test: Balance auto-update on CRUD, insufficient funds guard

### Phase 4: Transfers & Activity Logs
- [x] Create `transfers` migration
- [x] Create `Transfer` model with relationships
- [x] Create `TransferService` (create/delete with dual-account balance)
- [x] Create `activity_logs` migration (already done)
- [x] Create `ActivityLog` model (already done)
- [x] Create `ActivityLogService` (already done)
- [x] Create `StoreTransferRequest`
- [x] Create `TransferController`
- [x] Create `ActivityLogController` (index, read-only)
- [x] Create Blade views for Transfers (index, create)
- [x] Create Blade view for Activity Logs (index)
- [x] Test: Transfer balance, activity audit trail

### Phase 5: Dashboard & Admin Panel
- [x] Create `DashboardService` (summary, recent transactions, chart data)
- [x] Create `DashboardController`
- [x] Create Admin `AdminUserController` (user listing, user detail)
- [x] Create Dashboard Blade view (summary cards, category chart, recent transactions, quick actions)
- [x] Create Admin views (users index, user detail)
- [x] Wire routes for Dashboard and Admin panel
- [x] Test: Dashboard data accuracy, admin access control

### Phase 6: Part 1 Final Testing & Polish
- [x] Route list verification (54 routes confirmed)
- [x] Migration status check (10 migrations all ran)
- [x] Browser smoke test (user to verify manually)
- [x] Verify all CRUD flows
- [x] Final review and walkthrough

---

## PART 2 – Budgeting & Expense Enhancements *(Current)*

### Phase 7: Monthly Budget Management
- [x] Create `budgets` migration (user_id, name, amount, month, year, notes)
- [x] Create `Budget` model with relationships & scopes
- [x] Create `BudgetService` (CRUD, month/year scoping, spent calculation)
- [x] Create `StoreBudgetRequest` / `UpdateBudgetRequest`
- [x] Create `BudgetController` (full CRUD)
- [x] Create Blade views for Budgets (index, create, edit, show)
- [x] Add routes for budgets
- [x] Test: Budget CRUD, month scope, ownership isolation

### Phase 8: Category-Wise Budgets
- [x] Create `category_budgets` migration (budget_id, category_id, amount)
- [x] Create `CategoryBudget` model
- [x] Update `BudgetService` with category budget logic
- [x] Update `Budget` model with `categoryBudgets()` relationship
- [x] Create `StoreCategoryBudgetRequest`
- [x] Update `BudgetController` for category budget management
- [x] Update budget views (add category breakdown UI)
- [x] Test: Per-category limits, spent vs budgeted

### Phase 9: Time-Bound Budgets & Carry-Forward
- [ ] Add `start_date`, `end_date`, `type` columns to budgets (migration)
- [ ] Update `Budget` model with date scopes
- [ ] Update `BudgetService` with date-range filtering & carry-forward logic
- [ ] Create `BudgetCarryForwardCommand` (Artisan scheduled command)
- [ ] Register scheduled command in `routes/console.php`
- [ ] Update budget views (date range picker, carry-forward indicator)
- [ ] Update form requests for new date fields
- [ ] Test: Custom date budgets, carry-forward execution

### Phase 10: Recurring / Scheduled Expenses
- [ ] Create `recurring_expenses` migration
- [ ] Create `RecurringExpense` model
- [ ] Create `RecurringExpenseService` (CRUD + auto-creation logic)
- [ ] Create `ProcessRecurringExpensesCommand` (Artisan cron command)
- [ ] Register scheduled command
- [ ] Create `RecurringExpenseController`
- [ ] Create form requests for recurring expenses
- [ ] Create Blade views (index, create, edit)
- [ ] Add routes
- [ ] Test: Recurring CRUD, auto-creation accuracy, balance impact

### Phase 11: Expense Tags & Edit History
- [ ] Create `tags` migration (user_id, name)
- [ ] Create `expense_tag` pivot migration
- [ ] Create `Tag` model
- [ ] Update `Expense` model with `tags()` relationship
- [ ] Create `TagService`
- [ ] Update `ExpenseService` to handle tags on create/update
- [ ] Update expense views (tag input, display)
- [ ] Update expense form requests for tags validation
- [ ] Create `expense_histories` migration
- [ ] Create `ExpenseHistory` model
- [ ] Update `ExpenseService` to log history on update
- [ ] Add history view to expense edit page
- [ ] Add routes for tags if needed
- [ ] Test: Tag CRUD, tagging expenses, edit history tracking

### Phase 12: Category Locking & Part 2 Final Testing
- [ ] Add `is_locked` column to `categories` table (migration)
- [ ] Update `Category` model (locked scope, logic)
- [ ] Create `CategoryLockService` (check & lock logic)
- [ ] Hook lock check into `ExpenseService` on create
- [ ] Update category views (locked indicator)
- [ ] Update expense views (locked category warning)
- [ ] Update Dashboard with budget overview widgets
- [ ] Full integration testing of all Part 2 features
- [ ] Mark Part 2 complete

---

## PART 3 – Group Expense & Bill Splitting *(Blocked – waiting for Part 2 completion)*

- [ ] Group Creation & Management
- [ ] Group Member Roles (Admin / Member)
- [ ] Group Expense Tracking
- [ ] Bill Splitting – Equal / Exact / Percentage
- [ ] Balance Calculation per Member
- [ ] Smart Settlement Suggestions (minimize transactions algorithm)
- [ ] Testing & Integration with Parts 1-2

---

## PART 4 – Reporting, Alerts & Final Integration *(Blocked – waiting for Part 3 completion)*

- [ ] Account-Wise Reports
- [ ] Category-Wise Reports
- [ ] Budget vs Actual Analysis
- [ ] Financial Health Score (rule-based formula)
- [ ] Rule-Based Alerts & Notifications
- [ ] Emergency Mode (restrict non-essential categories)
- [ ] Privacy / Focus Mode
- [ ] Data Export (PDF & CSV)
- [ ] Final Integration Testing
- [ ] Full System Demo Preparation
