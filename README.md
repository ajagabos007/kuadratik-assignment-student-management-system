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

- PHP >= 7.4
- Composer
- Laravel >= 8.x
- MySQL or another supported database

## Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone <https://github.com/ajagabos007/kuadratik-assignment-student-management-system>
   cd <kuadratik-assignment-student-management-system>

2. **Install Dependencies**
   ```bash
    composer install

2. **Configure Environment**
- Copy the example environment file and update it
   ```bash
    cp .env.example .env