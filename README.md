# SpendWise

SpendWise is an advanced, comprehensive Personal Finance & Expense Management System built natively on Laravel 12. It provides users with deep tools to manage varied accounts, handle complex multi-user bill splitting, enforce strict budget restrictions, and rapidly generate detailed PDF statements.

## 🚀 Key Features

*   **Core Mechanics**: Multi-account management, customizable categories with iconography, explicit Income/Expense/Transfer mapping.
*   **Analytics Engine**: Chart.js trend visualizations, Budget vs. Actual breakdown bars, and a proprietary Financial Health Score algorithm mapping user financial performance out of 100 points.
*   **Collaborative Groups**: Seamlessly create tokenized groups, invite participants, and cleanly slice real-world group checks natively. The settlement engine dictates exactly *Who owes Who*.
*   **Rule-Based Alerts System**: Reactive, passive background triggers alerting users to Budget Limits, Low Balances, or highly suspicious Large Expenses via a realtime Notification Hub.
*   **Modifier States**: 
    *   **Emergency Mode**: Globally blocks all new non-essential discretionary spending execution at the database level.
    *   **Privacy Mode**: Visual hover-revealed blurring across dashboard data components to fight shoulder-surfing in public scenarios.
*   **Universal Exporting**: Dynamic physical layout generation using DOMPDF extracting "Monthly Statements" and native CSV tabular iterations for deep data processing.

## 🛠 Tech Stack

*   **Framework**: Laravel 12
*   **Frontend**: Tailwind CSS, Alpine.js, Blade Components
*   **Icons & Assets**: FontAwesome, Chart.js
*   **PDF Generation**: `barryvdh/laravel-dompdf`

## ⚙️ Installation

1.  Clone this repository.
    ```bash
    git clone https://github.com/your-username/spendwise.git
    cd spendwise
    ```
2.  Install Composer Dependencies.
    ```bash
    composer install
    ```
3.  Install NPM Packages & Compile Assets.
    ```bash
    npm install
    npm run build
    ```
4.  Configure Environment Parameters.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Make sure to configure your `DB_DATABASE` credentials in the `.env` file.*
5.  Run Migrations and Seeders (Includes Admin).
    ```bash
    php artisan migrate:fresh --seed
    ```
6.  Start the Local Development Server.
    ```bash
    php artisan serve
    ```

## 📝 Usage & Testing
Extensive documentation regarding end-to-end testing phases from initial Accounts setup to managing Group Ledger distributions is cataloged explicitly within the `TESTING.md` file located in the root repository.

## 📄 License
This application is publicly developed as open-source software under the [MIT license](https://opensource.org/licenses/MIT).
