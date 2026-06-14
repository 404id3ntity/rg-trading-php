# OTP via Email Integration Guide

Hello! This document explains the new OTP (One-Time Password) via Email feature that has been integrated into the R&G Trading E-commerce platform, along with the steps needed to deploy and test it on your local or production environment.

## What Was Added

Successfully implemented an email-based OTP verification system to enhance the security of the application. Here are the key changes made:

1. **Backend Integration (Node.js)**
   - Added the `nodemailer` package to handle sending emails securely.
   - Updated the `auth.controller.js` to intercept Login and Registration attempts. Instead of logging the user in immediately, the system now generates a 6-digit OTP, saves it in the database, and sends it to the user's email.
   - Created a new `/api/auth/verify-otp` endpoint that validates the OTP submitted by the user. If correct, it issues the access token and completes the login/registration process.

2. **Frontend Integration (PHP)**
   - Modified `login.php` and `register.php`. The forms now act in two steps:
     - **Step 1:** Submit email/password (or registration details).
     - **Step 2:** A new form appears asking for the OTP that was sent to the user's email.
   - Integrated error handling and success messages (Flashes) to guide the user seamlessly through the OTP flow.

3. **Database Updates**
   - Added two new columns to the `users` table: `otp` (to store the generated code) and `otp_expires_at` (to ensure the OTP expires after 10 minutes for security purposes).
   - _Note: A bug was also fixed in the product controller (`ER_WRONG_ARGUMENTS`) to ensure products load smoothly without crashing._

---

## What You Need to Do (Setup Instructions)

To make the OTP feature work on your machine, please follow these exact steps:

### 1. Update the Database

The new feature requires new columns in your database.

- Open your **PHPMyAdmin** (or any MySQL client).
- Run the SQL file located at: `R-G_Ecommerce_Capstone_Backend/migrations/z_add_otp_columns.sql`
- _This will safely add the `otp` and `otp_expires_at` columns to your existing `users` table without deleting your data._

### 2. Configure Your Environment Variables (.env)

You need to set up the email address that will send the OTPs to your users.

- Open the `.env` file located inside the `R-G_Ecommerce_Capstone_Backend` folder.
- Find the **SMTP / Email** section and update it with your Gmail details:
  ```env
  SMTP_HOST=smtp.gmail.com
  SMTP_PORT=465
  SMTP_USER=butang_ang_gmail_niyo@gmail.com
  SMTP_PASS=butang_ang_gmail_niyoPAssword
  SMTP_FROM="R&G Trading" <butang_ang_gmail_niyo@gmail.com>
  ```
- **IMPORTANT:** For `SMTP_PASS`, DO NOT use your normal Gmail password. You must generate an **App Password**.
  - Go to your Google Account > Security > 2-Step Verification > App Passwords.
  - Create a new App Password for "Mail" and paste the 16-character code into the `.env` file.

### 3. Install New Dependencies

Because a new email package (`nodemailer`) was added, you need to install it.

- Open your terminal and navigate to the backend folder:
  `cd R-G_Ecommerce_Capstone_Backend`
- Run the installation command:
  `npm install`

### 4. Restart the Backend Server

Whenever you change the `.env` file or install new packages, you must restart your Node.js server.

- In your terminal running the backend, press `Ctrl + C` to stop it.
- Run it again:
  `npm start`

---

## Testing the OTP Feature

1. Open your browser and go to the Registration or Login page (`http://localhost/rg-trading-php/index.php`).
2. Try to register a new account or log in with an existing one. Make sure you use a **real, accessible email address**.
3. You will be redirected to the OTP Verification step.
4. Check your Gmail inbox (or spam folder) for an email from "R&G Trading" containing your 6-digit code.
5. Enter the code to successfully access the dashboard!

_If you experience any "Too many authentication attempts" error while testing, do not worry—this is a built-in security feature to prevent spam. Just restart your backend server (`npm start`) to reset the limit._
