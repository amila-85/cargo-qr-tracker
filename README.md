# ECO Cargo - QR Scanner & Shipment Tracking System

A full-stack web application for scanning cargo box QR codes, updating two-stage shipment logistics (Origin vs. Destination), sending automatic email notifications via SMTP, and providing role-based access control (Admin vs. Staff).

---

## Features
* **Role-Based Access Control**:
  * **Admin**: Unrestricted status updates, backward rollbacks, and a single unified status dropdown.
  * **Staff**: Forward-only status updates, auto-locking of Origin status upon export, and split two-stage workflow UI.
* **Dual-Stage Tracking**:
  * **Origin Logistics**: Warehouse -> Transit to Port -> Port Arrival -> Shipped.
  * **Destination Logistics**: Port Arrival -> Customs Cleared -> Out for Delivery -> Delivered.
* **QR & Manual Search**: Live browser QR scanner with fallback manual tracking number lookup.
* **Email Notifications**: Automatic customer updates via PHPMailer / SMTP.
* **Offline Support**: Local caching with IndexedDB and auto-sync upon reconnection.

---

## Setup & Testing Instructions

### 1. Database Setup
1. Import `database.sql` into phpMyAdmin (`http://localhost/phpmyadmin`).
2. Verify that `BoxShipments` and `Users` tables are created.

### 2. Configuration
1. Copy `config.example.php` to `config.php`:
   ```bash
   cp config.example.php config.php
   ```
2. Update database credentials and SMTP password inside `config.php` / `api_update_status.php`.

### 3. Test Credentials
| Username | Default Password | Role | Access Level |
| :--- | :--- | :--- | :--- |
| `admin` | `admin123` | Admin | Full Access / Backward Updates / Unified UI |
| `staff1` | `staff123` | Staff | Forward-only Updates / Split Stage Rules |