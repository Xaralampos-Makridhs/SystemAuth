# SystemAuth - Ολοκληρωμένο Σύστημα Ελέγχου Πρόσβασης

## 📋 Περιγραφή

**SystemAuth** είναι ένα σύστημα ελέγχου πρόσβασης (Authentication System) που αναπτύχθηκε σε PHP. Παρέχει ένα ολοκληρωμένο πλαίσιο εργασίας για τη διαχείριση χρήστη με ασφάλεια, περιλαμβάνοντας εγγραφή, σύνδεση, επαλήθευση email και ανάκτηση κωδικού πρόσβασης.

## 🎯 Κύριες Λειτουργίες

### 1. **Εγγραφή Χρήστη (User Registration)**
- Δημιουργία νέου λογαριασμού χρήστη
- Επικύρωση δεδομένων εισόδου (όνομα, email, κωδικός πρόσβασης)
- Ασφαλής αποθήκευση κωδικού πρόσβασης με BCRYPT hashing
- Αυτόματη δημιουργία token επαλήθευσης email
- Αποστολή email με σύνδεσμο επαλήθευσης (ισχύει για 1 ώρα)

### 2. **Σύνδεση Χρήστη (User Login)**
- Αυθεντικοποίηση με email και κωδικό πρόσβασης
- Προστασία από brute force attacks (5 απολογιστείες προσπάθειες σε 15 λεπτά)
- Καταγραφή προσπαθειών σύνδεσης (IP, email, ημερομηνία)
- Ασφαλής διαχείριση sessions με HTTPS cookies
- Απαίτηση επαληθευμένου email για σύνδεση

### 3. **Επαλήθευση Email (Email Verification)**
- Αποστολή token επαλήθευσης μέσω email
- Εξακρίβωση ορθότητας token
- Ενημέρωση κατάστασης χρήστη σε "επαληθευμένος"
- Ασφαλής χρήση SHA256 hashing για tokens
- Πρόληψη επαναχρησιμοποίησης tokens

### 4. **Ανάκτηση Κωδικού Πρόσβασης (Password Reset)**
- Αποστολή email με σύνδεσμο ανάκτησης (ισχύει για 30 λεπτά)
- Ασφαλή αλλαγή κωδικού πρόσβασης
- Ένα-προς-ένα χρήση tokens
- Αποτροπή ανεξουσιοδότητης ανάκτησης κωδικού

### 5. **Ασφαλής Διαχείριση Συνεδριών (Session Management)**
- Δημιουργία νέας συνεδρίας μετά τη σύνδεση
- Regenerate session ID για πρόληψη session fixation
- HTTPOnly cookies για πρόληψη XSS attacks
- Αυστηρή λειτουργία session

### 6. **Ταμπλό (Dashboard)**
- Μόνο για εξουσιοδοτημένους χρήστες
- Προβολή στοιχείων χρήστη

## 🔧 Τεχνικά Χαρακτηριστικά

### Δομή Έργου
```
SystemAuth/
├── Config/
│   └── Database.php          # Διαμόρφωση σύνδεσης βάσης δεδομένων
├── Services/
│   ├── AuthService.php       # Λογική ελέγχου πρόσβασης
│   └── MailService.php       # Υπηρεσία αποστολής email
├── public/
│   ├── register.php          # Σελίδα εγγραφής
│   ├── login.php             # Σελίδα σύνδεσης
│   ├── logout.php            # Αποσύνδεση
│   ├── verify-email.php      # Επαλήθευση email
│   ├── forgot-password.php   # Αίτημα ανάκτησης κωδικού
│   ├── reset-password.php    # Ανάκτηση κωδικού
│   └── dashboard.php         # Ταμπλό χρήστη
├── bootstrap.php             # Αρχικοποίηση εφαρμογής
├── authsystem.sql            # SQL schema βάσης δεδομένων
├── composer.json             # Εξαρτήσεις PHP
└── .env                       # Μεταβλητές περιβάλλοντος
```

### Τεχνολογίες
- **Γλώσσα**: PHP 8.3+
- **Βάση Δεδομένων**: MySQL 8.0+
- **Ασφάλεια**: BCRYPT, SHA256, PDO με Prepared Statements
- **Email**: PHPMailer 7.0+
- **Environment**: Dotenv 5.6+

## 📊 Δομή Βάσης Δεδομένων

### Πίνακας `users`
- `id` - Μοναδικό αναγνωριστικό χρήστη
- `name` - Όνομα χρήστη
- `email` - Email (μοναδικό)
- `password_hash` - Κατακερματισμένος κωδικός πρόσβασης
- `email_verified_at` - Ημερομηνία επαλήθευσης email
- `created_at` - Ημερομηνία δημιουργίας
- `updated_at` - Ημερομηνία τελευταίας ενημέρωσης

### Πίνακας `email_verified_tokens`
- `id` - Μοναδικό αναγνωριστικό
- `user_id` - Αναγνωριστικό χρήστη (Foreign Key)
- `token_hash` - Κατακερματισμένο token
- `expires_at` - Ημερομηνία λήξης
- `used_at` - Ημερομηνία χρήσης
- `created_at` - Ημερομηνία δημιουργίας

### Πίνακας `password_reset_tokens`
- `id` - Μοναδικό αναγνωριστικό
- `user_id` - Αναγνωριστικό χρήστη (Foreign Key)
- `token_hash` - Κατακερματισμένο token
- `expires_at` - Ημερομηνία λήξης
- `used_at` - Ημερομηνία χρήσης
- `created_at` - Ημερομηνία δημιουργίας

### Πίνακας `login_attempts`
- `id` - Μοναδικό αναγνωριστικό
- `email` - Email του χρήστη
- `ip_address` - IP διεύθυνση
- `attempted_at` - Ημερομηνία/Ώρα προσπάθειας
- `successful` - Κατάσταση προσπάθειας (1=επιτυχής, 0=αποτυχής)

## 🔒 Ασφάλεια

### Μέτρα Ασφάλειας
1. **Hashing Κωδικών**: BCRYPT με αυτόματη salt generation
2. **Token Generation**: Χρήση `random_bytes()` με 32 bytes
3. **Token Storage**: SHA256 hashing αντί αποθήκευσης plain text
4. **Prepared Statements**: Προστασία από SQL injection
5. **Session Security**:
   - HTTPOnly cookies
   - Session regeneration μετά τη σύνδεση
   - Strict session mode
6. **Brute Force Protection**: Περιορισμός σε 5 απολογιστείες προσπάθειες/15 λεπτά
7. **Token Expiration**:
   - Email verification: 1 ώρα
   - Password reset: 30 λεπτά
8. **Input Validation**: Email format, κωδικό μήκος ≥ 8 χαρακτήρες

## 📝 Απαιτήσεις

### Περιβάλλον
- PHP 8.3 ή νεότερη έκδοση
- MySQL 8.0 ή νεότερη έκδοση
- Composer (για διαχείριση εξαρτήσεων)

### Εξαρτήσεις
```json
{
    "require": {
        "vlucas/phpdotenv": "^5.6",
        "phpmailer/phpmailer": "^7.0"
    }
}
```

## 🚀 Εγκατάσταση

1. **Κλωνοποίηση repository**
   ```bash
   git clone https://github.com/Xaralampos-Makridhs/SystemAuth.git
   cd SystemAuth
   ```

2. **Εγκατάσταση εξαρτήσεων**
   ```bash
   composer install
   ```

3. **Δημιουργία `.env` αρχείου**
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=authsystem
   DB_USERNAME=root
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

4. **Δημιουργία βάσης δεδομένων**
   ```bash
   mysql -u root < authsystem.sql
   ```

5. **Εκκίνηση ενσωματωμένου διακομιστή PHP**
   ```bash
   php -S localhost:8000 -t public/
   ```

## 📖 Ροή Χρήστη

### Εγγραφή
1. Χρήστης συμπληρώνει φόρμα εγγραφής
2. Σύστημα επικυρώνει δεδομένα
3. Δημιουργείται λογαριασμός και token επαλήθευσης
4. Αποστέλλεται email με σύνδεσμο επαλήθευσης
5. Χρήστης κάνει κλικ στον σύνδεσμο για επαλήθευση

### Σύνδεση
1. Χρήστης εισάγει email και κωδικό
2. Σύστημα ελέγχει προσπάθειες brute force
3. Επικυρώνεται ταυτότητα χρήστη
4. Ελέγχεται εάν το email είναι επαληθευμένο
5. Δημιουργείται νέα σύνεδρος
6. Χρήστης ανακατευθύνεται στο dashboard

### Ανάκτηση Κωδικού
1. Χρήστης κάνει κλικ "Ξέχασα τον κωδικό"
2. Εισάγει το email του
3. Σύστημα δημιουργεί token ανάκτησης
4. Αποστέλλεται email με σύνδεσμο ανάκτησης
5. Χρήστης κάνει κλικ στον σύνδεσμο και θέτει νέο κωδικό
6. Κωδικός ενημερώνεται με ασφάλεια

## 📧 Υπηρεσία Email

Το σύστημα χρησιμοποιεί **PHPMailer** για αποστολή emails με:
- Υποστήριξη SMTP
- UTF-8 encoding
- HTML body και plain text fallback
- Secure connections (TLS/SSL)

## 🔐 Best Practices Ασφάλειας

- ✅ Χρήση prepared statements για όλα τα queries
- ✅ Κατακερματισμός κωδικών με BCRYPT
- ✅ Δημιουργία ασφαλών tokens με `random_bytes()`
- ✅ Session regeneration μετά τη σύνδεση
- ✅ HTTPOnly και Secure cookies
- ✅ Περιορισμός προσπαθειών σύνδεσης
- ✅ Token expiration
- ✅ Input validation και sanitization
- ✅ Error logging (όχι εκθέσεις στον χρήστη)
- ✅ Database transactions για ακεραιότητα δεδομένων

## 📄 Άδεια Χρήσης

Αυτό το έργο δεν έχει ακόμα καθορισμένη άδεια χρήσης.

## 👨‍💻 Δημιουργός

**Xaralampos Makridhs** - [GitHub Profile](https://github.com/Xaralampos-Makridhs)

---

*Τελευταία ενημέρωση: 3 Μάη 2026*
