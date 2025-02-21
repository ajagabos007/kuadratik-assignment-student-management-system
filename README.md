# Student Management System API

This project is a simple REST API built with Laravel to manage student data and track attendance. It uses JWT-based authentication powered by the [JWT Auth](https://github.com/PHP-Open-Source-Saver/jwt-auth?tab=readme-ov-file) package. The API supports user authentication, student CRUD operations, and attendance tracking.

## Features

- **User Authentication:** Secure login using JWT tokens.
- **Student CRUD:** 
  - Admins can create, update, delete, and retrieve any student record.
  - Students can view only their own profiles.
- **Attendance Tracking:** 
  - Students can check in to a class.
  - Teachers/Admins can view attendance records.
- **API Documentation:** Detailed endpoint information is provided below.

## Prerequisites

- PHP >= 8.x
- Composer
- Laravel >= 11.x
- MySQL or another supported database

## Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ajagabos007/kuadratik-assignment-student-management-system
   cd kuadratik-assignment-student-management-system

2. **Install Dependencies**
   ```bash
    composer install

3. **Configure Environment**
- Copy the example environment file and update it:
   ```bash
    cp .env.example .env
- Set your database credentials and other environment variables in the .env file.

4. **Generate Application Key**
    ```bash
    php artisan key:generate

5. **JWT Configuration**
- Publish the JWT configuration file:
    ``bash 
    php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
- Generate the JWT secret key:
    ```bash 
    php artisan jwt:secret

- Generate certificate 
    ```bash 
    php artisan jwt:generate-certs
-- see more commands on **[Lavaravel JWT Auth.](https://laravel-jwt-auth.readthedocs.io/en/latest/laravel-installation/)**


6. **Run Migrations**
    ```bash
    php artisan migrate

7. **Seed the Database (Optional) If you have seeders for test data, run:**
    ```bash
    php artisan db:seed

## API Documentation

- **[POSTMAN API Docs.](https://documenter.getpostman.com/view/22268604/2sAYdbQDTn)**

