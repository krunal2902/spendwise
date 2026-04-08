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
- [x] Add `start_date`, `end_date`, `type` columns to budgets (migration)
- [x] Update `Budget` model with date scopes
- [x] Update `BudgetService` with date-range filtering & carry-forward logic
- [x] Create `BudgetCarryForwardCommand` (Artisan scheduled command)
- [x] Register scheduled command in `routes/console.php`
- [x] Update budget views (date range picker, carry-forward indicator)
- [x] Update form requests for new date fields
- [x] Test: Custom date budgets, carry-forward execution

### Phase 10: Recurring / Scheduled Expenses
- [x] Create `recurring_expenses` migration
- [x] Create `RecurringExpense` model
- [x] Create `RecurringExpenseService` (CRUD + auto-creation logic)
- [x] Create `ProcessRecurringExpensesCommand` (Artisan cron command)
- [x] Register scheduled command
- [x] Create `RecurringExpenseController`
- [x] Create form requests for recurring expenses
- [x] Create Blade views (index, create, edit)
- [x] Add routes
- [x] Test: Recurring CRUD, auto-creation accuracy, balance impact

### Phase 11: Expense Tags & Edit History
- [x] Create `tags` migration (user_id, name)
- [x] Create `expense_tag` pivot migration
- [x] Create `Tag` model
- [x] Update `Expense` model with `tags()` relationship
- [x] Create `TagService`
- [x] Update `ExpenseService` to handle tags on create/update
- [x] Update expense views (tag input, display)
- [x] Update expense form requests for tags validation
- [x] Create `expense_histories` migration
- [x] Create `ExpenseHistory` model
- [x] Update `ExpenseService` to log history on update
- [x] Add history view to expense edit page
- [x] Add routes for tags if needed
- [x] Test: Tag CRUD, tagging expenses, edit history tracking

### Phase 12: Category Locking & Part 2 Final Testing
- [x] Add `is_locked` column to `categories` table (migration)
- [x] Update `Category` model (locked scope, logic)
- [x] Create `CategoryLockService` (check & lock logic)
- [x] Hook lock check into `ExpenseService` on create
- [x] Update category views (locked indicator)
- [x] Update expense views (locked category warning)
- [x] Update Dashboard with budget overview widgets
- [x] Full integration testing of all Part 2 features
- [x] Mark Part 2 complete

---

## PART 3 – Group Expense & Bill Splitting *(Current)*

### Phase 13: Group Creation & Management
- [x] Create `groups` migration (user_id [owner], name, description, currency, image, invite_code, is_active)
- [x] Create `Group` model with relationships (owner, members, expenses) & scopes (active, forUser)
- [x] Create `group_members` migration (group_id, user_id, role [admin/member], joined_at, status [active/removed])
- [x] Create `GroupMember` model with relationships & role helpers (isAdmin, isMember)
- [x] Create `GroupService` (create, update, delete, generateInviteCode, addMember, removeMember, changeRole)
- [x] Create `StoreGroupRequest` / `UpdateGroupRequest` form requests
- [x] Create `GroupController` (index, create, store, show, edit, update, destroy)
- [x] Create Blade views for Groups (index, create, edit, show with members list)
- [x] Add routes for groups (resource + member management)
- [x] Update `User` model with `groups()`, `ownedGroups()`, `groupMemberships()` relationships
- [x] Test: Group CRUD, invite code generation, ownership isolation

### Phase 14: Group Member Management & Invitations
- [x] Create `group_invitations` migration (group_id, invited_by, email, token, status [pending/accepted/declined], expires_at)
- [x] Create `GroupInvitation` model with relationships & scopes (pending, expired)
- [x] Create `GroupInvitationService` (sendInvite, acceptInvite, declineInvite, revokeInvite, joinViaCode)
- [x] Create `StoreGroupInvitationRequest` form request
- [x] Create `GroupMemberController` (index, updateRole, remove)
- [x] Create `GroupInvitationController` (store, accept, decline, revoke)
- [x] Create Blade views for members (members list, invite modal/form, pending invitations)
- [x] Add routes for invitations & member management
- [x] Test: Invite flow, accept/decline, role changes, member removal

### Phase 15: Group Expense Tracking
- [x] Create `group_expenses` migration (group_id, paid_by [user_id], category_id, amount, description, expense_date, split_type [equal/exact/percentage], notes, receipt_image)
- [x] Create `GroupExpense` model with relationships (group, paidBy, splits, category)
- [x] Create `group_expense_splits` migration (group_expense_id, user_id, amount, percentage, is_settled, settled_at)
- [x] Create `GroupExpenseSplit` model with relationships & scopes (settled, unsettled, forUser)
- [x] Create `GroupExpenseService` (create, update, delete with automatic split calculation)
- [x] Implement split calculators: `calculateEqualSplit()`, `calculateExactSplit()`, `calculatePercentageSplit()`
- [x] Create `StoreGroupExpenseRequest` / `UpdateGroupExpenseRequest` form requests
- [x] Create `GroupExpenseController` (index, create, store, edit, update, destroy)
- [x] Create Blade views for Group Expenses (index with filters, create with dynamic split UI, edit)
- [x] Add routes for group expenses
- [x] Test: Expense CRUD per group, all 3 split types, split total validation

### Phase 16: Balance Calculation & Settlement
- [x] Create `GroupBalanceService` (calculateBalances, getNetBalances, getMemberOwes, getMemberIsOwed)
- [x] Implement pairwise balance computation (who owes whom and how much)
- [x] Create `group_settlements` migration (group_id, paid_by [user_id], paid_to [user_id], amount, notes, settled_at)
- [x] Create `GroupSettlement` model with relationships (group, paidBy, paidTo)
- [x] Create `GroupSettlementService` (create, delete, getSettlementHistory)
- [x] Implement `SimplifyDebtsAlgorithm` – minimize transactions using net-balance greedy approach
- [x] Create `StoreGroupSettlementRequest` form request
- [x] Create `GroupSettlementController` (index, store, destroy)
- [x] Create Blade views for Settlements (balance overview, settlement form, settlement history)
- [x] Update Group show view with balance summary widget & settlement suggestions
- [x] Add routes for settlements
- [x] Test: Balance accuracy, settlement flow, debt simplification algorithm

### Phase 17: Group Dashboard & Part 3 Final Testing
- [x] Create Group dashboard section in Group show view (total spent, per-member breakdown, recent expenses)
- [x] Add group activity feed (expenses added, members joined, settlements made)
- [x] Update main Dashboard with "My Groups" summary widget (groups, outstanding balances)
- [x] Update navigation/sidebar with Groups link
- [x] Add `ActivityLogService` integration for all group actions
- [x] Full integration testing of all Part 3 features with Parts 1-2
- [x] Test: Cross-feature data integrity (personal vs group expenses), edge cases (single member, zero splits)
- [x] Mark Part 3 complete

---

## PART 4 – Reporting, Alerts & Final Integration *(Blocked – waiting for Part 3 completion)*

### Phase 18: Account-Wise & Category-Wise Reports
- [x] Create `ReportService` (accountReport, categoryReport, dateRangeFiltering, aggregation helpers)
- [x] Implement `getAccountReport()` – per-account income/expense/transfer summary with date filters
- [x] Implement `getCategoryReport()` – per-category expense breakdown with trends & comparison
- [x] Create `ReportController` (index, accountReport, categoryReport)
- [x] Create `ReportFilterRequest` form request (date_from, date_to, account_ids, category_ids, group_by)
- [x] Create Blade views for Reports (reports index/hub, account report with charts, category report with charts)
- [x] Integrate Chart.js charts (bar, pie, line) for visual data representation
- [x] Add routes for reports
- [x] Test: Report accuracy with sample data, date filtering, empty state handling

### Phase 19: Budget vs Actual Analysis
- [x] Add `getBudgetAnalysis()` to `ReportService` (budget vs actual spending, variance calculation, trend)
- [x] Implement month-over-month budget comparison (current vs previous months)
- [x] Implement category-level budget drill-down (budgeted vs actual per category)
- [x] Create `BudgetReportController` (index, show)
- [x] Create Blade views for Budget Analysis (overview with progress bars, category drill-down, trend chart)
- [x] Add visual indicators for over-budget / under-budget / on-track states
- [x] Add routes for budget reports
- [x] Test: Budget vs actual accuracy, carry-forward impact on analysis, edge cases

### Phase 20: Financial Health Score
- [x] Create `FinancialHealthService` (calculateScore, getBreakdown, getRecommendations)
- [x] Implement scoring formula based on weighted rules:
  - [x] Savings ratio (income - expenses / income) → weight: 30%
  - [x] Budget adherence (% of budgets on-track) → weight: 25%
  - [x] Expense diversity (category spread, no single > 50%) → weight: 15%
  - [x] Account health (positive balances, multiple accounts) → weight: 15%
  - [x] Consistency (regular income, no extreme spikes) → weight: 15%
- [x] Create `FinancialHealthController` (show)
- [x] Create Blade view for Health Score (score gauge, breakdown cards, improvement tips)
- [x] Add routes for financial health
- [x] Test: Score calculation with varied profiles, edge cases (new users, zero data)

### Phase 21: Rule-Based Alerts & Notifications
- [x] Create `alert_rules` migration (user_id, type [budget_threshold/low_balance/large_expense/recurring_due], conditions JSON, is_active, last_triggered_at)
- [x] Create `AlertRule` model with relationships & scopes (active, forType)
- [x] Create `notifications` migration (user_id, type, title, message, data JSON, is_read, read_at)
- [x] Create `Notification` model with relationships & scopes (unread, recent)
- [x] Create `AlertService` (evaluateRules, triggerAlert, checkBudgetThresholds, checkLowBalance, checkLargeExpense)
- [x] Create `NotificationService` (create, markAsRead, markAllRead, getUnread, getRecent)
- [x] Create `StoreAlertRuleRequest` form request
- [x] Create `AlertRuleController` (index, store, update, destroy, toggle)
- [x] Create `NotificationController` (index, markRead, markAllRead)
- [x] Create Blade views for Alert Rules (index/manage, create/edit modal)
- [x] Create Blade views for Notifications (dropdown in navbar, full index page)
- [x] Hook alert evaluation into `ExpenseService`, `IncomeService`, `TransferService` on create
- [x] Add routes for alerts and notifications
- [x] Test: Alert triggering accuracy, notification creation, read/unread state

### Phase 22: Emergency Mode & Privacy/Focus Mode
- [x] Add `emergency_mode` and `focus_mode` boolean columns to `users` table (migration)
- [x] Create `EmergencyModeService` (activate, deactivate, getNonEssentialCategories, isRestricted)
- [x] Define essential vs non-essential category classification (system-level flag on categories)
- [x] Add `is_essential` column to `categories` table (migration)
- [x] Hook emergency mode check into `ExpenseService.create()` – block non-essential expenses
- [x] Create `FocusModeService` (activate, deactivate, getHiddenData)
- [x] Implement focus mode – hide balances and amounts on dashboard (show asterisks/hidden)
- [x] Create `UserModeController` (toggleEmergency, toggleFocus)
- [x] Update Dashboard view with emergency mode banner & focus mode masking
- [x] Update expense creation views with emergency mode warnings
- [x] Add quick-toggle in navigation/header for both modes
- [x] Add routes for mode toggles
- [x] Test: Emergency mode blocking, focus mode masking, persistence across sessions

### Phase 23: Data Export (PDF & CSV)
- [x] Install `barryvdh/laravel-dompdf` package for PDF generation
- [x] Create `ExportService` (exportToCsv, exportToPdf, buildExportData)
- [x] Implement CSV export for: Expenses, Incomes, Transfers, Budget Reports, Group Expenses
- [x] Implement PDF export with formatted templates for: Monthly Statement, Budget Report, Group Settlement Report
- [x] Create PDF Blade templates (export/pdf/monthly-statement, export/pdf/budget-report, export/pdf/group-report)
- [x] Create `ExportController` (exportExpenses, exportIncomes, exportBudgetReport, exportGroupReport)
- [x] Add export buttons to existing views (expenses index, incomes index, reports, group show)
- [x] Add routes for exports
- [x] Test: CSV format validity, PDF rendering, large dataset performance

### Phase 24: Final Integration Testing & System Polish
- [x] Full route list verification (all new routes registered)
- [x] Migration status check (all new migrations ran successfully)
- [x] Cross-module integration testing:
  - [x] Personal expenses ↔ Group expenses isolation
  - [x] Budget tracking ↔ Group expense impact
  - [x] Alert triggers across all transaction types
  - [x] Emergency mode ↔ Group expense blocking
  - [x] Export ↔ Filtered data accuracy
- [x] UI/UX consistency review across all new views
- [x] Update Dashboard with all Part 4 widgets (health score, notifications bell, mode toggles)
- [x] Update navigation/sidebar with all new menu items (Reports, Alerts, Health Score)
- [x] Performance review (N+1 queries, eager loading, caching opportunities)
- [x] Browser smoke test (all flows end-to-end)
- [x] Final walkthrough document
- [x] Mark Part 4 complete – System ready for demo
