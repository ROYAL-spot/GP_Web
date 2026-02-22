# Dr. Taylor GP Web Portal

A comprehensive web application for a General Practitioner's medical practice. This system streamlines the patient journey from initial booking to medical history submission.

## 🚀 Features
* **Dynamic Appointment Booking**: Real-time scheduling with automatic email notifications.
* **Secure Patient Portal**: Multi-step registration for new patients to capture medical history.
* **Automated PDF Generation**: Generates a professional medical history document using `TCPDF` upon portal completion.
* **SMTP Email Integration**: Uses `PHPMailer` with Gmail SMTP for reliable communication.
* **Environment Security**: Protected credentials using `phpdotenv`.
* **Responsive Design**: Fully mobile-friendly UI with centralized CSS architecture.

## 🛠️ Technical Stack
* **Backend**: PHP 8.x
* **Database**: MySQL
* **Libraries**: 
    * [PHPMailer](https://github.com/PHPMailer/PHPMailer) (Email)
    * [TCPDF](https://tcpdf.org/) (PDF Generation)
    * [phpdotenv](https://github.com/vlucas/phpdotenv) (Environment Variables)
* **Frontend**: HTML5, CSS3 (Flexbox/Grid), JavaScript (Mobile Navigation)

## 📋 Installation & Setup

### 1. Clone the Repository
```bash
git clone [https://github.com/ROYAL-spot/GP_Web.git](https://github.com/ROYAL-spot/GP_Web.git)
cd GP_Web