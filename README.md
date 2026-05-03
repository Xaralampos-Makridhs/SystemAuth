#  SystemAuth - Comprehensive Access Control System

## Description

**SystemAuth** is an authentication system developed in PHP. It provides a secure and complete framework for user management, including registration, login, email verification, and password recovery.

---

## Main Features

###  1. User Registration

* Create new user accounts
* Input data validation (name, email, password)
* Secure password storage using **BCRYPT hashing**
* Automatic email verification token generation
* Verification email with link valid for **1 hour**

---

###  2. User Login

* Authentication with email and password
* Protection against brute force attacks:

  * Maximum **5 failed attempts within 15 minutes**
* Login attempt logging:

  * IP address
  * Email
  * Timestamp
* Secure session management with HTTPS cookies
* Requires verified email before login

---

###  3. Email Verification

* Verification token sent via email
* Token validity checking
* User status updated to verified
* Secure **SHA256 hashing** for tokens
* Prevention of token reuse

---

###  4. Password Reset

* Recovery link sent by email
* Reset link valid for **30 minutes**
* Secure password change
* One-time token usage
* Protection against unauthorized password recovery

---

###  5. Secure Session Management

* New session creation after login
* Session ID regeneration to prevent session fixation
* HTTPOnly cookies to reduce XSS risk
* Strict session mode

---

### 6. Dashboard

* Accessible only to authenticated users
* Displays user information

---

##  Technical Features

###  Project Structure

```bash
SystemAuth/
├── Config/
│   └── Database.php              # Database connection configuration
├── Services/
│   ├── AuthService.php           # Authentication logic
│   └── MailService.php           # Email sending service
├── public/
│   ├── register.php              # Registration page
│   ├── login.php                 # Login page
│   ├── logout.php                # Logout
│   ├── verify-email.php          # Email verification
│   ├── forgot-password.php       # Password reset request
│   ├── reset-password.php        # Password reset
│   └── dashboard.php             # User dashboard
├── bootstrap.php                 # Application initialization
├── authsystem.sql                # Database schema
├── composer.json                 # PHP dependencies
└── .env                          # Environment variables
```

---

###  Technologies

* **Language:** PHP 8.3+
* **Database:** MySQL 8.0+
* **Security:** BCRYPT, SHA256, PDO with Prepared Statements
* **Email:** PHPMailer 7.0+
* **Environment:** Dotenv 5.6+
* 
---

##  Security

###  Security Measures

1. **Password Hashing:** BCRYPT with automatic salt generation
2. **Token Generation:** `random_bytes(32)`
3. **Token Storage:** SHA256 hashing instead of plain text
4. **SQL Injection Protection:** Prepared statements
5. **Session Security:**

   * HTTPOnly cookies
   * Session regeneration after login
   * Strict session mode
6. **Brute Force Protection:** 5 failed attempts per 15 minutes
7. **Token Expiration:**

   * Email verification: 1 hour
   * Password reset: 30 minutes
8. **Input Validation:**

   * Valid email format
   * Password ≥ 8 characters

---

##  Requirements

### Environment

* PHP 8.3 or newer
* MySQL 8.0 or newer
* Composer

---

###  Dependencies

```json
{
  "require": {
    "vlucas/phpdotenv": "^5.6",
    "phpmailer/phpmailer": "^7.0"
  }
}
```

---

##  Installation

### 1. Clone Repository

```bash
git clone https://github.com/Xaralampos-Makridhs/SystemAuth.git
cd SystemAuth
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create `.env` File

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=authsystem
DB_USERNAME=
DB_PASSWORD=

MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM=noreply@authsystem.local
MAIL_FROM_NAME=AuthSystem

APP_URL=http://localhost:8000
```

---

### 4. Create Database

```bash
mysql -u root < authsystem.sql
```

---

### 5. Start Server

```bash
php -S localhost:8000 -t public/
```

---

##  User Flow

###  Registration

1. User fills out the registration form
2. System validates input
3. Account is created
4. Verification email is sent
5. User verifies email

---

###  Login

1. User enters credentials
2. System checks brute force attempts
3. Identity is verified
4. Email verification is checked
5. Session is created
6. Redirect to dashboard

---

###  Password Recovery

1. User clicks **Forgot Password**
2. Enters email
3. System generates token
4. Email is sent
5. User sets new password

---

##  Email Service

The system uses **PHPMailer** with:

* SMTP support
* UTF-8 encoding
* HTML + plain text fallback
* Secure TLS connection

---
