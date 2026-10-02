# Clinic Appointment Management System (MediBook)

MediBook is a web-based Clinic Appointment Management System built to streamline healthcare scheduling and patient management. It allows patients to explore specialist doctors, book appointment slots online, and manage their schedules easily. Administrators can manage doctor profiles, oversee patient bookings, and update appointment statuses in real-time.

---

## 👥 Group Information

- **Course:** Web & Internet Programming (Mini Full-Stack Project)
- **Department:** Department of Computer Science & Engineering, Southeast University
- **Course Instructor:** Bidyarthi Paul (Lecturer, Dept. of CSE)

### Group Members:
1. **Nazmul Huda** - ID: 2023100000656
2. **Md. Mehedi Hasan** - ID: 2023200000273
3. **Sabikun Nahar Meem** - ID: 2023200000303
4. **Pranto Roy** - ID: 2023200000236

---

## 🛠️ Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript (ES6), FontAwesome Icons
- **Backend:** PHP (v8.0+)
- **Database:** MySQL
- **Environment:** XAMPP / WAMP / Localhost server environment

---

## 📁 Project Folder Structure


clinic-main/
│
├── dashboard/
│   └── admin_dashboard.php      # Admin control panel for doctor & appointment management
│
├── database/
│   └── clinic_db.sql            # MySQL database script with structure and sample data
│
├── doctor/
│   ├── doctor.css               # Styles for doctor page
│   └── doctor.php               # Doctor directory page
│
├── img/
│   └── d1.png                   # Image assets
│
├── includes/
│   ├── config.php               # Project base configuration and BASE_URL setup
│   ├── db.php                   # Database connection file
│   ├── footer.php               # Reusable page footer component
│   ├── head.php                 # HTML head element with CSS & icon dependencies
│   └── header.php               # Dynamic top navigation header
│
├── patient/
│   └── patient_dashboard.php    # Patient dashboard for tracking personal appointments
│
├── style/
│   ├── appointment.css          # Styles for booking form
│   ├── dashboard.css            # Styles for admin & patient dashboards
│   ├── my-appointment.css       # Styles for appointment history table
│   └── style.css                # Primary stylesheet
│
├── appointment.php              # Appointment booking page
├── index.php                    # Landing home page
├── login.php                    # User/Admin authentication login
├── logout.php                   # Session termination script
├── my-appointment.php           # Patient appointment history
├── register.php                 # Patient account registration page
├── success.php                  # Appointment booking success confirmation
└── README.md                    # Project documentation


---

## 🗄️ Database Information

- **Database Name:** `clinic_db`
- **Main Tables:**
  - `users`: Stores user account details, hashed passwords, and roles (`patient`, `admin`).
  - `patients`: Stores patient profile information and phone numbers.
  - `doctors`: Stores doctor details, names, specializations, and emails.
  - `appointments`: Stores booking transactions, dates, times, medical messages, and status (`Pending`, `Confirmed`, `Completed`, `Cancelled`).

---

## 🚀 How to Run the Project (Setup Instructions)

Follow these steps to run MediBook on your local system:

1. **Prerequisites:**
   - Install **XAMPP** or any local PHP server environment.

2. **Clone / Download the Repository:**
   - Clone or extract the project folder inside your XAMPP `htdocs` directory:
     `C:/xampp/htdocs/clinic-main`

3. **Start XAMPP Control Panel:**
   - Start **Apache** and **MySQL** modules.

4. **Import Database:**
   - Open your browser and go to `http://localhost/phpmyadmin/`
   - Create a new database named **`clinic_db`**
   - Click **Import**, select `database/clinic_db.sql` from the project folder, and click **Go**.

5. **Run Application:**
   - Open your browser and visit: `http://localhost/clinic-main/`

---

## 🔐 Login Credentials (Demo Accounts)

### Admin Account:
- **Email:** `admin*****@.gmailcom`
- **Password:** `******`

### Patient Account:
- **Email:** `*****@seu.com`
- **Password:** `******` *(or register a new patient account via `register.php`)*

---

## 📌 Important Notes & Dependencies

- Ensure that the base URL in `includes/config.php` matches your local server path:
  `define('BASE_URL', '/clinic-main/');`
- PHP MySQLi extension must be enabled.
- FontAwesome and Google Fonts are loaded via CDN (requires an active internet connection for styling icons properly).
