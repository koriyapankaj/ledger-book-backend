# LedgerBook Backend 📒

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)

**LedgerBook** is a personal finance API designed to track "every single rupee" with precision. It moves beyond simple expense tracking by using a ledger-based system to handle debts, loans, split payments, and inter-account transfers.

## 🌟 Project Overview

This system allows users to manage their financial life by recording:
* **Expenses & Income** (Standard tracking)
* **Assets & Liabilities** (Bank accounts, Cash, Credit Cards)
* **Debts & Loans** (Tracking money owed to/by friends and contacts)
* **Split Transactions** (Handling scenarios where a single bill includes both personal expense and money lent to a friend)

## ⚡ Key Features

* **Smart Ledger:** Double-entry inspired logic to keep "Net Worth" and "Liquidity" accurate.
* **Split Transaction Engine:** Record a ₹1000 payment as ₹500 Expense + ₹500 Loan in a single go.
* **Contact Management:** Maintain a running balance for every person you transact with (Friends, Shopkeepers).
* **Multi-Account:** Manage Savings, Cash, Wallets, and Credit Cards in one view.
* **Scalable Architecture:** Built on Laravel 11 with a focus on API performance and data integrity.

## 🛠 Tech Stack

* **Backend:** Laravel 11 (API)
* **Database:** MySQL
* **Auth:** Laravel Sanctum
* **Frontend:** Vue 3 (Separate Repo)

## 📂 Database Structure

The core logic relies on 6 pivotal tables:
1.  `users` - Authentication.
2.  `accounts` - Physical storage of money (Banks, Wallets).
3.  `contacts` - People you lend to or borrow from.
4.  `categories` - Grouping for expenses.
5.  `transactions` - The parent event (Header).
6.  `transaction_splits` - The granular details (Breakdown of where money went).

## 🚀 Getting Started

Follow these steps to set up the backend locally:

```bash
# 1. Clone the repository
git clone [https://github.com/your-username/ledger-book-backend.git](https://github.com/your-username/ledger-book-backend.git)
cd ledger-book-backend

# 2. Install Dependencies
composer install

# 3. Environment Setup
cp .env.example .env
# Open .env and configure your DB_DATABASE, DB_USERNAME, and DB_PASSWORD

# 4. Generate App Key
php artisan key:generate

# 5. Run Migrations
php artisan migrate

# 6. Start Server
php artisan serve