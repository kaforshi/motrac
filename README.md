# Motrac - Money Tracker Application

A comprehensive money tracking application built with Laravel, MySQL, and Tailwind CSS.

## Features

### 1. Transaction Management
- Record Income, Expenses, and Transfers
- Multi-level categories (parent and sub-categories)
- Split Transaction feature
- In-app calculator
- Receipt photo attachments
- Transaction templates for recurring entries

### 2. Account Management
- Multiple account types (Cash, Bank, E-Wallet, Liability)
- Reconcile Mode for balance adjustments
- Hide investment accounts from daily balance

### 3. Smart Budgeting
- Monthly spending limits per category
- Progress bars with color indicators
- Budget rollover feature
- Automatic notifications

### 4. Debt & Receivables Management
- Track loans separately
- Installment payments
- Due date tracking
- Automatic balance calculation

### 5. Analytics & Reports
- Trend charts
- Calendar view
- Export to Excel/CSV
- Privacy Mode
- Dark Mode
- Advanced search filters

## Installation

1. Clone the repository
2. Run `composer install`
3. Run `npm install`
4. Create `.env` file (copy from `env.template` if needed)
5. Configure your database in `.env`
6. Run `php artisan key:generate` (if not already done)
7. Run `php artisan migrate`
8. Run `php artisan storage:link`
9. Run `php artisan serve`
10. Run `npm run dev` (in another terminal)

## Requirements

- PHP >= 8.1
- MySQL >= 5.7
- Node.js >= 18
- Composer