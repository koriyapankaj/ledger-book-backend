# Ledger Book Backend - Complete API Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Design](#database-design)
4. [Core Features](#core-features)
5. [API Endpoints](#api-endpoints)
6. [Transaction Logic](#transaction-logic)
7. [Installation Guide](#installation-guide)

---

## 1. Project Overview

**Ledger Book Backend** is a comprehensive personal finance management API built with Laravel 11. It enables users to track every rupee of their financial life - from simple income/expense tracking to complex scenarios like split payments, lending/borrowing, credit card management, and inter-account transfers.

### Tech Stack
- **Framework:** Laravel 11
- **Authentication:** Laravel Sanctum (Token-based)
- **Database:** MySQL
- **Architecture:** Service Layer Pattern
- **API Style:** RESTful JSON API

### Key Capabilities
- Multi-account management (Cash, Bank, Wallets, Credit Cards)
- Income and expense tracking with categories
- Debt/lending management with contacts
- Inter-account transfers
- Automatic balance calculations
- Budget tracking (optional)
- Transaction statistics and reports

---

## 2. System Architecture

### Architecture Pattern
```
┌─────────────┐
│   Routes    │ → API endpoints definition
└──────┬──────┘
       │
┌──────▼──────┐
│ Controllers │ → Handle HTTP requests/responses
└──────┬──────┘
       │
┌──────▼──────┐
│  Services   │ → Business logic & transaction handling
└──────┬──────┘
       │
┌──────▼──────┐
│   Models    │ → Database interaction & relationships
└──────┬──────┘
       │
┌──────▼──────┐
│  Database   │ → Data persistence
└─────────────┘
```

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── AccountController.php
│   │       ├── CategoryController.php
│   │       ├── ContactController.php
│   │       └── TransactionController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Account/
│   │   ├── Category/
│   │   ├── Contact/
│   │   └── Transaction/
│   └── Resources/
│       ├── UserResource.php
│       ├── AccountResource.php
│       ├── CategoryResource.php
│       ├── ContactResource.php
│       └── TransactionResource.php
├── Models/
│   ├── User.php
│   ├── Account.php
│   ├── Category.php
│   ├── Contact.php
│   ├── Transaction.php
│   └── Budget.php
├── Services/
│   └── TransactionService.php
└── Traits/
    └── BelongsToUser.php
```

---

## 3. Database Design

### ER Diagram
```
┌──────────────┐
│    users     │
└───────┬──────┘
        │ 1
        │
        │ *
    ┌───▼────────────────────────────────┐
    │                                    │
┌───▼──────┐  ┌──────────┐  ┌──────────▼┐  ┌─────────────┐
│ accounts │  │categories│  │  contacts  │  │   budgets   │
└────┬─────┘  └─────┬────┘  └──────┬─────┘  └──────┬──────┘
     │              │              │                │
     │ *            │ *            │ *              │ *
     └──────────────┴──────────────┴────────────────┘
                    │
                ┌───▼────────────┐
                │  transactions  │
                └────────────────┘
```

### Tables & Relationships

#### 3.1 Users Table
**Purpose:** Store user authentication and profile information

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | User's full name |
| email | varchar(255) | Unique email (login credential) |
| password | varchar(255) | Hashed password |
| currency | varchar(3) | Default currency (e.g., INR, USD) |
| timezone | varchar | User's timezone |
| is_active | boolean | Account status |
| last_login_at | timestamp | Last login timestamp |
| created_at | timestamp | Account creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Relationships:**
- Has Many: Accounts, Categories, Contacts, Transactions, Budgets

**Helper Methods:**
- `getTotalAssets()` - Sum of all asset account balances
- `getTotalLiabilities()` - Sum of all liability account balances
- `getNetWorth()` - Assets - Liabilities

---

#### 3.2 Accounts Table
**Purpose:** Store user's financial accounts (wallets, banks, credit cards)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| name | varchar(255) | Account name (e.g., "HDFC Savings") |
| type | enum | 'asset' or 'liability' |
| subtype | enum | cash, bank_account, digital_wallet, credit_card, loan |
| balance | decimal(15,2) | Current account balance |
| credit_limit | decimal(15,2) | Credit limit (for credit cards) |
| account_number | varchar(50) | Masked account number |
| bank_name | varchar(255) | Bank/institution name |
| color | varchar(7) | Hex color code for UI |
| icon | varchar(50) | Icon identifier |
| is_active | boolean | Account status |
| include_in_total | boolean | Include in net worth calculation |
| notes | text | Additional notes |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `user_id, is_active`
- `user_id, type`

**Relationships:**
- Belongs To: User
- Has Many: Transactions (as account)
- Has Many: Transactions (as to_account for transfers)

**Scopes:**
- `active()` - Only active accounts
- `asset()` - Asset type accounts
- `liability()` - Liability type accounts
- `ofSubtype($subtype)` - Filter by subtype

**Helper Methods:**
- `updateBalance($amount)` - Add/subtract from balance
- `getAvailableCredit()` - Available credit for credit cards
- `isOverLimit()` - Check if credit limit exceeded

**Business Rules:**
- Asset accounts: positive balance = money you have
- Liability accounts: positive balance = money you owe
- Credit cards: balance starts at 0, increases with spending

---

#### 3.3 Categories Table
**Purpose:** Organize income and expenses into categories

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| parent_id | bigint | FK to categories (for subcategories) |
| name | varchar(255) | Category name |
| type | enum | 'income' or 'expense' |
| color | varchar(7) | Hex color code |
| icon | varchar(50) | Icon identifier |
| is_active | boolean | Category status |
| order | integer | Display order |
| description | text | Category description |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `user_id, type, is_active`
- `user_id, parent_id`

**Relationships:**
- Belongs To: User
- Belongs To: Parent Category
- Has Many: Child Categories
- Has Many: Transactions
- Has Many: Budgets

**Scopes:**
- `active()` - Only active categories
- `income()` - Income categories
- `expense()` - Expense categories
- `parentOnly()` - Top-level categories only
- `ordered()` - Order by 'order' field

**Helper Methods:**
- `isParent()` - Check if it's a parent category
- `hasChildren()` - Check if it has subcategories

**Hierarchy:**
- Parent categories (parent_id = null)
- Child categories (parent_id references parent)
- Example: "Food & Dining" → "Breakfast", "Lunch", "Dinner"

---

#### 3.4 Contacts Table
**Purpose:** Track people you lend money to or borrow from

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| name | varchar(255) | Contact name |
| email | varchar(255) | Contact email |
| phone | varchar(20) | Contact phone |
| balance | decimal(15,2) | Current balance (+ they owe you, - you owe them) |
| notes | text | Additional notes |
| is_active | boolean | Contact status |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `user_id, is_active`
- `user_id, balance`

**Relationships:**
- Belongs To: User
- Has Many: Transactions

**Scopes:**
- `active()` - Only active contacts
- `owesYou()` - Contacts with positive balance
- `youOwe()` - Contacts with negative balance
- `settled()` - Contacts with zero balance

**Helper Methods:**
- `updateBalance($amount)` - Update balance
- `owesYou()` - Returns true if they owe you
- `youOwe()` - Returns true if you owe them
- `isSettled()` - Returns true if balance is zero
- `getBalanceStatus()` - Returns 'owes_you', 'you_owe', or 'settled'

**Balance Logic:**
- Positive balance: Contact owes you money
- Negative balance: You owe contact money
- Zero balance: All settled

---

#### 3.5 Transactions Table (Core)
**Purpose:** Record all financial transactions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| type | enum | Transaction type (see below) |
| amount | decimal(15,2) | Transaction amount |
| account_id | bigint | FK to accounts (source) |
| to_account_id | bigint | FK to accounts (destination for transfers) |
| category_id | bigint | FK to categories |
| contact_id | bigint | FK to contacts (for debt transactions) |
| transaction_date | date | Date of transaction |
| title | varchar(255) | Short description |
| description | text | Detailed description |
| reference_number | varchar(100) | Reference/receipt number |
| metadata | json | Additional flexible data |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Transaction Types:**
- `income` - Money coming in (salary, freelance)
- `expense` - Money going out (food, shopping)
- `transfer` - Moving money between your accounts
- `lent` - Money you lent to someone
- `borrowed` - Money you borrowed from someone
- `repayment_in` - Someone paying you back
- `repayment_out` - You paying someone back

**Indexes:**
- `user_id, transaction_date`
- `user_id, type`
- `user_id, account_id`
- `user_id, category_id`
- `user_id, contact_id`

**Relationships:**
- Belongs To: User
- Belongs To: Account (source)
- Belongs To: Account (destination, as toAccount)
- Belongs To: Category
- Belongs To: Contact

**Scopes:**
- `ofType($type)` - Filter by type
- `income()` - Income transactions
- `expense()` - Expense transactions
- `transfer()` - Transfer transactions
- `dateRange($start, $end)` - Filter by date range
- `thisMonth()` - Current month transactions
- `thisYear()` - Current year transactions
- `recent($days)` - Recent N days

**Helper Methods:**
- `isIncome()` - Check if income type
- `isExpense()` - Check if expense type
- `isTransfer()` - Check if transfer type
- `isDebtRelated()` - Check if debt-related

---

#### 3.6 Budgets Table (Optional)
**Purpose:** Set spending limits for categories

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| category_id | bigint | FK to categories |
| amount | decimal(15,2) | Budget amount |
| period | enum | daily, weekly, monthly, yearly |
| start_date | date | Budget start date |
| end_date | date | Budget end date (null for recurring) |
| is_active | boolean | Budget status |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |
| deleted_at | timestamp | Soft delete timestamp |

**Relationships:**
- Belongs To: User
- Belongs To: Category

**Scopes:**
- `active()` - Active budgets
- `current()` - Currently applicable budgets

**Helper Methods:**
- `getSpentAmount()` - Total spent in budget period
- `getRemainingAmount()` - Budget remaining
- `getPercentageUsed()` - Percentage of budget used
- `isOverBudget()` - Check if budget exceeded

---

## 4. Core Features

### 4.1 Authentication System
- **Token-based authentication** using Laravel Sanctum
- **No session cookies** - suitable for API consumption
- **Multi-device support** - Generate separate tokens per device
- **Token management** - Logout from current device or all devices

### 4.2 Account Management
- Create multiple accounts of different types
- Support for:
  - Cash wallets
  - Bank accounts
  - Digital wallets (PayTM, PhonePe, etc.)
  - Credit cards (with credit limits)
  - Loans
- Real-time balance tracking
- Activate/deactivate accounts
- Include/exclude accounts from net worth calculation

### 4.3 Category System
- Hierarchical categories (parent → children)
- Separate income and expense categories
- 13 default parent categories with 60+ subcategories
- Custom category creation
- Color-coded and icon-based for UI
- Reorder categories

### 4.4 Contact Management
- Track people you lend money to or borrow from
- Automatic balance calculation
- View who owes you vs. who you owe
- Filter by balance status
- Cannot delete contacts with unsettled balances

### 4.5 Transaction Engine (Core)

#### Transaction Types & Their Effects:

**1. Income Transaction**
```
User receives salary → Bank Account
Effect: Bank balance increases
Required: account_id, category_id, amount
```

**2. Expense Transaction**
```
User pays for food → Cash decreases
Effect: Account balance decreases
Required: account_id, category_id, amount
```

**3. Transfer Transaction**
```
ATM withdrawal: Bank → Cash
Credit card payment: Bank → Credit Card
Effect: Source decreases, Destination increases
Required: account_id, to_account_id, amount
```

**4. Lent Transaction**
```
You lend ₹500 to Rahul
Effect: 
- Your account decreases by ₹500
- Rahul's balance increases by ₹500 (he owes you)
Required: account_id, contact_id, amount
```

**5. Borrowed Transaction**
```
You borrow ₹1000 from Priya
Effect:
- Your account increases by ₹1000
- Priya's balance decreases by -₹1000 (you owe her)
Required: account_id, contact_id, amount
```

**6. Repayment In Transaction**
```
Rahul pays you back ₹500
Effect:
- Your account increases by ₹500
- Rahul's balance decreases by ₹500
Required: account_id, contact_id, amount
```

**7. Repayment Out Transaction**
```
You pay Priya back ₹1000
Effect:
- Your account decreases by ₹1000
- Priya's balance increases by ₹1000
Required: account_id, contact_id, amount
```

### 4.6 TransactionService - Business Logic

The `TransactionService` handles all complex transaction logic:

**Key Methods:**
- `createTransaction()` - Creates transaction and updates balances
- `updateTransaction()` - Reverses old effects, applies new ones
- `deleteTransaction()` - Reverses transaction effects
- `getStatistics()` - Income, expense, savings by period
- `getSpendingByCategory()` - Category-wise expense breakdown

**Transaction Guarantees:**
- **Atomic operations** - All or nothing (DB transactions)
- **Balance consistency** - Balances always accurate
- **Audit trail** - Cannot modify past, only create new
- **Rollback support** - Failed transactions don't affect balances

### 4.7 BelongsToUser Trait

**Purpose:** Automatic user scoping and security

**Features:**
- Auto-sets `user_id` on model creation
- Automatically filters all queries by authenticated user
- Works with Sanctum token authentication
- Prevents users from seeing other users' data

**Benefits:**
- No manual user_id filtering needed
- Security by default
- Clean controller code
- Easy to bypass for admin operations

---

## 5. API Endpoints

### Base URL
```
http://localhost:8000/api
```

### 5.1 Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "currency": "INR",
  "timezone": "Asia/Kolkata"
}

Response 201:
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "currency": "INR",
    "timezone": "Asia/Kolkata",
    "is_active": true,
    "financial_summary": {
      "total_assets": 0,
      "total_liabilities": 0,
      "net_worth": 0
    }
  },
  "token": "1|abc...xyz"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

Response 200:
{
  "message": "Login successful",
  "user": { ... },
  "token": "2|def...uvw"
}
```

#### Get Current User
```http
GET /api/me
Authorization: Bearer {token}

Response 200:
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "financial_summary": {
      "total_assets": 58500.00,
      "total_liabilities": 5000.00,
      "net_worth": 53500.00
    }
  }
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}

Response 200:
{
  "message": "Logged out successfully"
}
```

#### Logout All Devices
```http
POST /api/logout-all
Authorization: Bearer {token}

Response 200:
{
  "message": "Logged out from all devices successfully"
}
```

---

### 5.2 Account Endpoints

#### Get All Accounts
```http
GET /api/accounts
Authorization: Bearer {token}
Query Parameters:
  - type: asset|liability
  - subtype: cash|bank_account|digital_wallet|credit_card|loan
  - active_only: true|false

Response 200:
{
  "accounts": [
    {
      "id": 1,
      "name": "Cash",
      "type": "asset",
      "subtype": "cash",
      "balance": 5000.00,
      "color": "#10B981",
      "icon": "wallet",
      "is_active": true,
      "include_in_total": true
    }
  ]
}
```

#### Create Account
```http
POST /api/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "HDFC Savings",
  "type": "asset",
  "subtype": "bank_account",
  "balance": 50000,
  "bank_name": "HDFC Bank",
  "account_number": "****1234",
  "color": "#3B82F6",
  "icon": "bank",
  "include_in_total": true
}

Response 201:
{
  "message": "Account created successfully",
  "account": { ... }
}
```

#### Get Single Account
```http
GET /api/accounts/{id}
Authorization: Bearer {token}

Response 200:
{
  "account": {
    "id": 1,
    "name": "Cash",
    "balance": 5000.00,
    ...
  }
}
```

#### Update Account
```http
PUT /api/accounts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Account Name",
  "is_active": false
}

Response 200:
{
  "message": "Account updated successfully",
  "account": { ... }
}
```

#### Delete Account
```http
DELETE /api/accounts/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Account deleted successfully"
}

Response 422 (if has transactions):
{
  "message": "Cannot delete account with existing transactions. Please deactivate instead."
}
```

#### Get Accounts Summary
```http
GET /api/accounts-summary
Authorization: Bearer {token}

Response 200:
{
  "summary": {
    "total_assets": 58500.00,
    "total_liabilities": 5000.00,
    "net_worth": 53500.00,
    "accounts_count": 5,
    "active_accounts_count": 5
  }
}
```

---

### 5.3 Category Endpoints

#### Get All Categories
```http
GET /api/categories
Authorization: Bearer {token}
Query Parameters:
  - type: income|expense
  - parent_only: true|false
  - active_only: true|false

Response 200:
{
  "categories": [
    {
      "id": 1,
      "name": "Food & Dining",
      "type": "expense",
      "color": "#EF4444",
      "icon": "utensils",
      "has_children": true,
      "children": [
        {
          "id": 2,
          "name": "Breakfast",
          "parent_id": 1,
          ...
        }
      ]
    }
  ]
}
```

#### Create Category
```http
POST /api/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Custom Category",
  "type": "expense",
  "parent_id": null,
  "color": "#8B5CF6",
  "icon": "star",
  "order": 1
}

Response 201:
{
  "message": "Category created successfully",
  "category": { ... }
}
```

#### Update Category
```http
PUT /api/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "is_active": false
}

Response 200:
{
  "message": "Category updated successfully",
  "category": { ... }
}
```

#### Delete Category
```http
DELETE /api/categories/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Category deleted successfully"
}

Response 422 (if has transactions):
{
  "message": "Cannot delete category with existing transactions. Please deactivate instead."
}

Response 422 (if has children):
{
  "message": "Cannot delete category with subcategories. Please delete subcategories first."
}
```

---

### 5.4 Contact Endpoints

#### Get All Contacts
```http
GET /api/contacts
Authorization: Bearer {token}
Query Parameters:
  - status: owes_you|you_owe|settled
  - active_only: true|false
  - search: name search term

Response 200:
{
  "contacts": [
    {
      "id": 1,
      "name": "Rahul Sharma",
      "email": "rahul@example.com",
      "phone": "+91 98765 43211",
      "balance": 500.00,
      "balance_status": "owes_you",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Priya Patel",
      "balance": -1000.00,
      "balance_status": "you_owe"
    }
  ]
}
```

#### Create Contact
```http
POST /api/contacts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Amit Kumar",
  "email": "amit@example.com",
  "phone": "+91 98765 43210",
  "notes": "Office colleague"
}

Response 201:
{
  "message": "Contact created successfully",
  "contact": {
    "id": 3,
    "name": "Amit Kumar",
    "balance": 0.00,
    "balance_status": "settled",
    ...
  }
}
```

#### Update Contact
```http
PUT /api/contacts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "phone": "+91 99999 99999"
}

Response 200:
{
  "message": "Contact updated successfully",
  "contact": { ... }
}
```

#### Delete Contact
```http
DELETE /api/contacts/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Contact deleted successfully"
}

Response 422 (if unsettled):
{
  "message": "Cannot delete contact with unsettled balance. Current balance: 500.00"
}
```

#### Get Contacts Summary
```http
GET /api/contacts-summary
Authorization: Bearer {token}

Response 200:
{
  "summary": {
    "total_owed_to_you": 1500.00,
    "total_you_owe": 2000.00,
    "net_position": -500.00,
    "contacts_count": 5,
    "settled_count": 2
  }
}
```

---

### 5.5 Transaction Endpoints

#### Get All Transactions
```http
GET /api/transactions
Authorization: Bearer {token}
Query Parameters:
  - type: income|expense|transfer|lent|borrowed|repayment_in|repayment_out
  - account_id: filter by account
  - category_id: filter by category
  - contact_id: filter by contact
  - start_date: YYYY-MM-DD
  - end_date: YYYY-MM-DD
  - period: today|week|month|year
  - search: search in title/description
  - per_page: pagination limit (default 15)

Response 200:
{
  "transactions": [
    {
      "id": 1,
      "type": "expense",
      "amount": 250.00,
      "account": {
        "id": 1,
        "name": "Cash"
      },
      "category": {
        "id": 2,
        "name": "Breakfast"
      },
      "transaction_date": "2025-01-15",
      "title": "Morning breakfast",
      "description": "Breakfast at cafe"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67
  }
}
```

#### Create Transaction
```http
POST /api/transactions
Authorization: Bearer {token}
Content-Type: application/json

// Example 1: Income
{
  "type": "income",
  "amount": 50000,
  "account_id": 2,
  "category_id": 1,
  "transaction_date": "2025-01-01",
  "title": "January Salary",
  "description": "Monthly salary credit"
}

// Example 2: Expense
{
  "type": "expense",
  "amount": 250,
  "account_id": 1,
  "category_id": 8,
  "transaction_date": "2025-01-15",
  "title": "Breakfast",
  "description": "Morning coffee and sandwich"
}

// Example 3: Transfer
{
  "type": "transfer",
  "amount": 5000,
  "account_id": 2,
  "to_account_id": 1,
  "transaction_date": "2025-01-15",
  "title": "ATM Withdrawal",
  "description": "Cash withdrawal from bank"
}

// Example 4: Lent Money
{
  "type": "lent",
  "amount": 500,
  "account_id": 1,
  "contact_id": 2,
  "transaction_date": "2025-01-15",
  "title": "Lent to Rahul",
  "description": "For breakfast payments"
}

// Example 5: Borrowed Money
{
  "type": "borrowed",
  "amount": 1000,
  "account_id": 1,
  "contact_id": 3,
  "transaction_date": "2025-01-10",
  "title": "Borrowed from Priya",
  "description": "Emergency cash needed"
}

Response 201:
{
  "message": "Transaction created successfully",
  "transaction": { ... }
}
```

#### Update Transaction
```http
PUT /api/transactions/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 300,
  "title": "Updated title"
}

Response 200:
{
  "message": "Transaction updated successfully",
  "transaction": { ... }
}
```

#### Delete Transaction
```http
DELETE /api/transactions/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Transaction deleted successfully"
}
```

#### Get Transaction Statistics
```http
GET /api/transactions-statistics
Authorization: Bearer {token}
Query Parameters:
  - period: today|week|month|year (default: month)

Response 200:
{
  "statistics": {
    "total_income": 50000.00,
    "total_expense": 15000.00,
    "net_savings": 35000.00,
    "total_transfers": 5000.00,
    "period": "month"
  }
}
```