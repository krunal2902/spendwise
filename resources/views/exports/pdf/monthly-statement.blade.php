<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Statement</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { display: table; width: 100%; border-bottom: 2px solid #5c6bc0; padding-bottom: 10px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: bottom; }
        .header-right { display: table-cell; text-align: right; vertical-align: bottom; }
        .title { font-size: 24px; font-weight: bold; color: #3949ab; margin: 0; }
        .summary-box { width: 100%; display: table; margin-bottom: 30px; }
        .summary-item { display: table-cell; width: 33.33%; text-align: center; padding: 15px; border: 1px solid #e0e0e0; background-color: #f8f9fa; }
        .summary-label { font-size: 11px; text-transform: uppercase; color: #757575; margin-bottom: 5px; }
        .summary-value { font-size: 18px; font-weight: bold; }
        .text-green { color: #2e7d32; }
        .text-red { color: #c62828; }
        .text-blue { color: #1565c0; }
        h3 { border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #424242; margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; color: #555; }
        .text-right { text-align: right; }
        .footer { text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; margin-top: 40px; position: fixed; bottom: 0; width: 100%; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <h1 class="title">SpendWise Statement</h1>
            <p style="margin: 5px 0 0;">Period: {{ $monthName }}</p>
        </div>
        <div class="header-right">
            <p style="margin: 0;"><strong>{{ $user->name }}</strong></p>
            <p style="margin: 0; color: #666;">{{ $user->email }}</p>
            <p style="margin: 0; color: #666;">Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Income</div>
            <div class="summary-value text-green">₹{{ number_format($totalIncome, 2) }}</div>
        </div>
        <div class="summary-item" style="border-left: none; border-right: none;">
            <div class="summary-label">Total Expenses</div>
            <div class="summary-value text-red">₹{{ number_format($totalExpense, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Net Saving</div>
            <div class="summary-value {{ $netFlow >= 0 ? 'text-blue' : 'text-red' }}">
                {{ $netFlow >= 0 ? '+' : '' }}₹{{ number_format($netFlow, 2) }}
            </div>
        </div>
    </div>

    @if($expensesByCategory->isNotEmpty())
        <h3>Expense Breakdown by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">% of Total Space</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $category => $amount)
                    <tr>
                        <td>{{ $category ?: 'Uncategorized' }}</td>
                        <td class="text-right">₹{{ number_format($amount, 2) }}</td>
                        <td class="text-right">
                            {{ $totalExpense > 0 ? number_format(($amount / $totalExpense) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="page-break"></div>

    <h3>Detailed Expenses</h3>
    @if($expenses->isEmpty())
        <p>No expenses recorded this month.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td>{{ $expense->account->name ?? 'N/A' }}</td>
                        <td>{{ $expense->category->name ?? 'Uncategorized' }}</td>
                        <td>{{ $expense->description }}</td>
                        <td class="text-right">₹{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Detailed Income</h3>
    @if($incomes->isEmpty())
        <p>No income recorded this month.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomes as $income)
                    <tr>
                        <td>{{ $income->income_date->format('Y-m-d') }}</td>
                        <td>{{ $income->account->name ?? 'N/A' }}</td>
                        <td>{{ $income->category->name ?? 'Uncategorized' }}</td>
                        <td>{{ $income->description }}</td>
                        <td class="text-right text-green">+₹{{ number_format($income->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated by SpendWise. All amounts are in INR (₹). This defines a summary snapshot of your balances for the stipulated time period.
    </div>

</body>
</html>
