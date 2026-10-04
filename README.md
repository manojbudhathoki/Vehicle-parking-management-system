# SmartPark — Vehicle Parking Management System

A single-facility **Vehicle Parking Management System** developed as a BCA 4th-semester academic project using **Core PHP, MySQL, HTML5, CSS3 and JavaScript**. The system supports separate workflows for customers, parking staff and administrators.

> **Academic note:** Bootstrap is used as a frontend UI framework for responsive layout and reusable interface components. The application's business logic, database design, authentication, authorization, booking, parking, payment-record and receipt workflows are implemented in Core PHP/MySQL and project-specific JavaScript/CSS.

---

## 1. Project Overview

SmartPark is designed to computerize the major operations of a parking facility, including:

- Customer registration and login
- Vehicle registration and management
- Parking booking
- Parking slot management
- Vehicle entry and exit
- Parking fee calculation
- Payment records
- Receipt generation
- Customer notifications
- Staff operations and reports
- Administrative management
- Audit logging

The current scope is a **single parking facility**. Payments are recorded as **Cash or Online** records; no external payment gateway is integrated.

---

## 2. Technology Stack

| Technology / Resource | Purpose |
|---|---|
| **PHP 8.x (Core PHP)** | Server-side application logic and request handling |
| **MySQL 8.x** | Relational database |
| **PDO** | Database connectivity and prepared statements |
| **Apache / XAMPP** | Local web-server/development environment |
| **HTML5** | Page structure and forms |
| **CSS3** | Project-specific styling and responsive customization |
| **JavaScript** | Client-side interaction and asynchronous requests |
| **Bootstrap 5.3.3** | Responsive grid, forms, buttons, cards, tables and utility classes |
| **Bootstrap Icons 1.11.3** | Interface/sidebar icons |
| **MySQL InnoDB** | Transactions and referential integrity |

### Frontend framework usage

Bootstrap is used only as a **frontend support framework**. It does not provide the application's parking-management logic. The project also contains substantial custom CSS in:

```text
assets/css/style.css
assets/css/panel.css
```

Project-specific JavaScript is located in:

```text
assets/js/main.js
```

The application uses Bootstrap 5.3.3 and Bootstrap Icons 1.11.3 through jsDelivr CDN in the current version. Therefore, the current version requires internet access for those CDN resources unless they are downloaded and linked locally.

---

## 3. User Roles

### Customer

- Register/login
- Manage personal profile
- Add/edit/delete vehicles
- Create parking bookings
- View booking status
- View active parking
- View parking history
- View payments
- View receipts
- Read notifications

### Parking Staff

- Staff dashboard
- Process booked and walk-in vehicle entry
- Manage vehicle exit
- Calculate parking fees on the server
- Record payments
- Generate receipts
- View active parking
- View booking/history information
- View operational reports

### Administrator

- Dashboard and system KPIs
- Customer management
- Staff management
- Vehicle management
- Parking-slot management
- Parking-rate management
- Booking management
- Payment management
- Receipt management
- Reports
- System settings
- Audit logs

---

## 4. Main Application Workflow

### Customer booking workflow

```text
Customer Login
      ↓
Select / Add Vehicle
      ↓
Create Booking
      ↓
Server validates vehicle ownership
      ↓
Server checks vehicle-type / slot compatibility
      ↓
Server checks booking overlap
      ↓
Booking is stored in MySQL
      ↓
Customer views booking status
```

### Parking entry workflow

```text
Booking / Walk-in Request
          ↓
Staff verifies vehicle
          ↓
Available slot is selected
          ↓
Parking record is created
          ↓
Vehicle enters parking
```

### Vehicle exit workflow

```text
Active Parking Record
          ↓
Staff processes vehicle exit
          ↓
Server calculates duration
          ↓
Server calculates parking fee
          ↓
Payment record created
          ↓
Receipt generated
          ↓
Parking/slot status updated
```

The fee calculation is performed on the **server side** rather than trusting a value submitted by the browser.

---

## 5. Database

The database SQL file is located at:

```text
database/parking_management.sql
```

The current schema contains the following main tables:

```text
users
vehicles
parking_slots
parking_rates
bookings
parking_records
payments
receipts
notifications
audit_logs
contact_messages
system_settings
```

The database uses **InnoDB** and foreign-key relationships to maintain data integrity.

### Main relationships

```text
Users
 ├── Vehicles
 ├── Bookings
 ├── Notifications
 └── Audit-related activity

Vehicles
 ├── Bookings
 └── Parking Records

Parking Slots
 ├── Bookings / Parking Records
 └── Slot availability

Bookings
 └── Parking Records

Parking Records
 └── Payments
      └── Receipts
```

The exact relationships and constraints should be read from `database/parking_management.sql` when preparing the ER diagram for the academic report.

---

## 6. Security Features

The project includes several server-side security measures:

- `password_hash()` and `password_verify()` for passwords
- Session regeneration at login
- HttpOnly/SameSite session-cookie configuration
- PDO prepared statements
- CSRF-token protection for POST requests
- Server-side role authorization
- Customer ownership checks
- Server-side parking-fee calculation
- Database transactions for important entry/exit/payment operations
- Audit logging for important staff operations
- Output escaping with `htmlspecialchars()`
- `.htaccess` configuration for the Apache environment

Security controls should still be reviewed and tested before any real-world deployment.

---

## 7. Installation — XAMPP

### Requirements

- Windows/Linux/macOS with a compatible XAMPP installation
- Apache
- PHP 8.x
- MySQL/MariaDB compatible with the supplied SQL schema
- A modern web browser

### Steps

1. Install XAMPP.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the project folder into:

```text
C:\xampp\htdocs\vehicle-parking-management
```

4. Open phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

5. Create/import the supplied database using:

```text
database/parking_management.sql
```

6. Check the database settings in:

```text
config/database.php
```

7. Open:

```text
http://localhost/vehicle-parking-management/
```

There is no required `/public/` directory; the project root resolves to `index.php`.

---

## 8. Demo Accounts

The supplied database contains demo accounts for development/testing:

```text
Admin
Email: admin@gmail.com
Password: admin12345

Staff
Email: staff@gmail.com
Password: staff12345

Customer
Email: customer@gmail.com
Password: user12345

Customer1
Email: customer1@gmail.com
Password: customer12345
```

For any deployment outside the academic/local environment, change or remove demo credentials and use secure account-management practices.

---

## 9. Project Structure

```text
vehicle-parking-management/
│
├── admin/                 # Admin modules
├── customer/              # Customer modules
├── staff/                 # Staff modules
├── assets/
│   ├── css/               # Custom CSS
│   └── js/                # Project JavaScript
├── config/                # Database/configuration
├── database/              # SQL schema/data
├── includes/              # Shared layouts, authentication and helpers
├── uploads/               # Upload directory placeholder
├── index.php              # Public landing page
├── login.php              # Login
├── register.php           # Customer registration
├── logout.php             # Logout
├── receipt.php            # Receipt display/printing
├── contact.php            # Contact page
├── rates.php              # Public parking rates
├── slots.php              # Public slot information
├── about.php              # About page
├── how-it-works.php       # System explanation
├── .htaccess              # Apache configuration
└── README.md
```

---

## 10. Application Architecture

The project follows a simple server-rendered layered approach rather than an MVC framework:

```text
Browser
   ↓
PHP Page / JavaScript
   ↓
Authentication + Validation
   ↓
Business Logic
   ↓
PDO
   ↓
MySQL
   ↓
HTML / JSON Response
   ↓
Browser
```

Shared authentication and helper logic is kept under `includes/`, while configuration/database connection code is kept under `config/`.

---

## 11. Offline Use

The application itself is local, but the current version loads these frontend dependencies from jsDelivr:

```text
Bootstrap 5.3.3 CSS
Bootstrap 5.3.3 JS Bundle
Bootstrap Icons 1.11.3
```

Therefore, the UI requires internet access for those CDN files in the current version.

For a fully offline demonstration, download the corresponding Bootstrap CSS/JS and Bootstrap Icons files into the project, then replace the CDN `<link>` and `<script>` references in:

```text
includes/header.php
includes/panel_layout.php
includes/footer.php
includes/panel_footer.php
```

After that, the application can be demonstrated locally without depending on the CDN.

---

## 12. Academic Scope and Limitations

This version is intended for a **BCA 4th-semester academic project** and focuses on demonstrating web-application development fundamentals:

- Requirement-based system design
- Relational database design
- CRUD operations
- Authentication and authorization
- Session management
- Server-side validation
- Basic web security practices
- Responsive frontend development
- PHP/MySQL integration
- Practical parking-management workflows

The project does **not** include:

- RFID hardware
- IoT sensors
- ANPR/camera-based vehicle recognition
- External payment gateway
- Mobile application
- Multi-branch parking management
- Real-time hardware integration

These are outside the current project scope.

---

## 13. Suggested Academic Documentation

For project submission and external evaluation, prepare documentation that matches the implemented system, including:

1. Introduction and problem statement
2. Objectives and scope
3. Requirement analysis
4. Functional and non-functional requirements
5. Feasibility study
6. SRS
7. System architecture
8. Use-case diagram
9. DFD
10. ER diagram
11. Database/schema design
12. UI screenshots
13. Implementation details
14. Testing and test cases
15. Security considerations
16. Limitations
17. Future enhancements
18. Conclusion
19. References

The team should be able to explain the implementation rather than treating Bootstrap or any other library as a substitute for understanding the application's code.

---

## 14. Future Enhancements

Possible future improvements include:

- Online payment-gateway integration
- Email/SMS notifications
- Multi-branch parking support
- QR-based booking/receipt verification
- Mobile application
- Advanced analytics
- Automated backups
- More detailed audit/reporting tools
- Optional hardware integration such as RFID or sensors

---

## 15. License / Academic Use

This project is intended for educational and academic use. Review and replace demo credentials, configuration values and other development settings before any production deployment.
