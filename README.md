# 🩸 JeevanSetu: The Bridge of Life (Blood & Organ Donation System)

JeevanSetu is a functionally complete, full-stack web application designed to centralize and optimize the management of blood and organ donation processes across institutions and individual donors. It replaces fragmented systems with a single, intelligent, real-time platform.

## ✨ Project Highlights (Expert Features)

* **Real-time Operational Dashboards:** Dedicated, high-contrast dashboards for Donors, Hospitals, and Blood Banks.
* **Dynamic Inventory Tracking:** Blood Banks manage component-level stock with automated **Low-Stock Alerts** and **Camp Scheduling** (Intermediate Feature).
* **Donor Availability System:** Individual Donors can instantly toggle their **Emergency Availability Status** (AJAX functional).
* **Advanced Intelligence Core:** Implements the structural logic for a **Weighted Organ Matching Algorithm** based on urgency, HLA, and wait time.
* **Public Visibility:** Public landing page features a **Dynamic Graph Visualization** of critical blood shortages across the entire system.
* **Admin Oversight:** Centralized panel for managing user accounts, tracking system health, and executing critical features.

## 🛠️ Technology Stack

| Component | Technology | Notes |
| :--- | :--- | :--- |
| **Frontend** | HTML5, CSS3, **Bootstrap 4** | Expert-level, responsive, and engaging UI design. |
| **Interactivity** | **JavaScript (ES6)**, **Chart.js** | Used for real-time data visualization, form validation, and AJAX updates. |
| **Backend** | **PHP** (Procedural/Modular) | Handles core logic, security, and database routing. |
| **Database** | **MySQL / MariaDB** | Used for data persistence, including complex tables for Inventory, Requests, and Organ Recipients. |

## 🚀 Installation Guide

### Prerequisites

1.  A local server environment (**XAMPP, WAMP, or MAMP**).
2.  PHP 8.0+
3.  MySQL / MariaDB

### Steps

1.  **Clone the Repository:**
    ```bash
    git clone [YOUR_REPO_URL] JeevanSetu
    ```

2.  **Database Setup:**
    * Open phpMyAdmin or your database tool.
    * Create a new database named **`jeevansetu`**.
    * Import the provided **`jeevansetu.sql`** file (This file contains all table schemas and sample data).

3.  **Configure Connection:**
    * Navigate to `JeevanSetu/includes/config.php`.
    * Update the database constants to match your local server credentials (e.g., `DB_USERNAME`, `DB_PASSWORD`).

4.  **Run Application:**
    * Start your Apache and MySQL services.
    * Access the project in your browser: `http://localhost/JeevanSetu/views/public/loader.html` (The animated loader is the entry point).

## 🔑 Default Login Credentials (For Testing)

| Role | Email | Password | Dashboard Link |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@jeevansetu.gov` | `password123` | `/views/user_dashboards/admin_dashboard.php` |
| **Donor** | `priya.sharma@donor.com` | `password123` | `/views/user_dashboards/donor_dashboard.php` |
| **Hospital** | `city_general@hospital.com` | `password123` | `/views/user_dashboards/hospital_dashboard.php` |
| **Blood Bank** | `central_bank@bloodbank.com` | `password123` | `/views/user_dashboards/bank_dashboard.php` |

***
*Note: Passwords are stored as plain text for testing purposes. **This must be converted to hashing** (bcrypt) before deployment.*
