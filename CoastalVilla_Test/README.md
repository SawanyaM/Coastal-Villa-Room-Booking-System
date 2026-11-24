# Coastal Villa and Room Booking System

## 1. Project Overview

The Coastal Villa and Room Booking System is a dynamic web-based application developed to manage room reservations for a coastal villa.

The system allows guests to view available rooms, check room availability, create accounts, log in, make bookings, and manage their reservations. Administrators can manage rooms and handle customer bookings through an admin dashboard.

## 2. Main Features

### Guest Features
- User registration and login.
- Secure password storage using password hashing.
- View available rooms and room details.
- Search for room availability using check-in and check-out dates.
- Make room reservations.
- View personal bookings.
- Cancel pending bookings.
- Logout securely.

### Admin Features
- Admin login and dashboard.
- View total rooms and booking statistics.
- Add new rooms.
- View and edit room details.
- Delete rooms when they are not linked to bookings.
- Activate and deactivate rooms.
- View and manage customer bookings.
- Confirm or cancel pending bookings.

### JavaScript Features
- Booking form validation.
- Check-in and check-out date validation.
- Automatic calculation of the number of nights.
- Automatic calculation of the estimated booking cost.
- Dynamic updates to booking information.
- Registration password confirmation.
- Login form validation.

### Responsive Design
The website includes responsive layouts for desktop, tablet, and mobile devices.

## 3. Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL / MariaDB
- PDO (PHP Data Objects)
- XAMPP
- phpMyAdmin
- Visual Studio Code

## 4. Software Requirements

The following software is required to run the project:

- Windows operating system.
- XAMPP with Apache and MySQL/MariaDB.
- PHP 8.3 or a compatible version.
- phpMyAdmin.
- Visual Studio Code or another code editor.
- Google Chrome or another modern web browser.

## 5. Project Installation and Setup

### Step 1: Install XAMPP

Install XAMPP and start the following services:

- Apache
- MySQL

### Step 2: Copy the Project

Copy the `CoastalVilla` project folder into the XAMPP `htdocs` directory.

Example location:

`C:\xampp\htdocs\CoastalVilla`

### Step 3: Create the Database

1. Open phpMyAdmin using:

   `http://localhost/phpmyadmin`

2. Create a database named:

   `coastal_villa_db`

3. Select the newly created database.
4. Open the Import tab.
5. Select the `database/db_structure.sql` file.
6. Click Import to create the database tables and insert the sample records.

### Step 4: Configure the Database Connection

Open:

`config/database.php`

Check that the database connection settings match your local XAMPP configuration.

Example:

- Host: `localhost`
- Database name: `coastal_villa_db`
- Username: `root`
- Password: Leave empty if using the default local XAMPP configuration.

### Step 5: Run the Application

Open a web browser and visit:

`http://localhost/CoastalVilla/`

The home page of the Coastal Villa system should appear.

## 6. User Accounts

The database includes sample guest and administrator accounts.

- Guest accounts can register through the registration page.
- Administrator accounts must have the `admin` role in the `users` table.
- New registrations are assigned the `guest` role by default.

For a new administrator account, register an account and change its role to `admin` through phpMyAdmin.

**Important:** Use the passwords selected during account registration. Passwords are stored in hashed format in the database.

## 7. Database Structure

The system uses a relational database named `coastal_villa_db`.

It contains three main tables:

### Users
Stores user information, login details, and account roles.

### Rooms
Stores room names, room types, descriptions, facilities, prices, guest limits, images, and room status.

### Bookings
Stores customer reservations, room details, check-in and check-out dates, total amounts, and booking statuses.

The database structure and sample records are provided in:

`database/db_structure.sql`

## 8. Testing

The following functions were tested during development:

- User registration.
- Password confirmation validation.
- Login with incorrect credentials.
- Successful guest login.
- Successful administrator login.
- Guest and administrator redirection.
- Logout functionality.
- Protected page access.
- Room management operations.
- Room booking and price calculation.
- Booking date validation.
- Responsive mobile layout.

## 9. AI Tool Declaration

ChatGPT was used as an AI assistance tool during the development of this project. It was used for programming guidance, debugging, understanding PHP and JavaScript functionality, improving code, and preparing project documentation.

The project was reviewed, modified, and tested during the development process by the student.

## 10. Project Folder Structure

```text
CoastalVilla/
│
├── admin/
│   ├── bookings.php
│   ├── dashboard.php
│   └── rooms.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
│
├── config/
│   └── database.php
│
├── database/
│   └── db_structure.sql
│
├── includes/
│   └── availability-search.php
│
├── availability.php
├── booking.php
├── index.php
├── login.php
├── logout.php
├── my-bookings.php
├── README.md
├── register.php
├── room-details.php
└── room.php
```

---

**Project Name:** Coastal Villa and Room Booking System

**Project Type:** Dynamic Web-Based Application