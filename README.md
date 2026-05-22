# Polije Autohub - Workshop Management System

A modern web-based workshop management system built for Polije Autohub to streamline workshop operations, spare part sales, inventory control, reporting, and accounting workflows.

---

## Overview

Polije Autohub - Workshop Management System is a Laravel-based application designed to support daily workshop operational activities in an integrated and structured way.

This system helps manage:

- Vehicle service operations
- Spare part sales
- Inventory management
- Accounting flow
- Role-based access control

The project is currently under active development, with **Phase 1 completed** and focused on core operational workflows.

---

## Tech Stack

- PHP 8+
- Laravel 11
- MySQL
- Blade
- Laravel Filament
- Spatie Laravel Permission
- Filament Shield
- mPDF

---

## Core Features

### Service Management

- Service transaction management
- Vehicle service workflow
- Service history tracking

### POS Sparepart

- Spare part sales transaction
- Integrated operational flow
- Inventory synchronization

### Inventory Management

- Spare part stock management
- Stock monitoring
- Inventory reporting

### Accounting

- Clean accounting flow integration
- Financial transaction recording
- Operational journal support

### Dashboard & Reporting

- Operational dashboard
- Inventory reports
- Transaction summaries

### Role & Permission Management

- Role-based access control
- Permission management using Spatie & Filament Shield

### API Development

- Internal API development
- Prepared for future integration and expansion

---

## User Roles

- Admin
- Kepala Unit
- Manager

---

## Project Status

> 🚧 Ongoing Development  
> ✅ Phase 1 Completed

The system is actively being developed and improved for future operational scalability and additional integrations.

---

## Installation

```bash
# Clone repository
git clone <repository-url>

# Move to project directory
cd polije-autohub

# Install backend dependencies
composer install

# Install frontend dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database credentials in .env

# Run database migration
php artisan migrate

# (Optional) Run database seeder
php artisan db:seed

# Create storage symbolic link
php artisan storage:link

# Run development server
php artisan serve
```

---

## Architecture Highlights

- Service-oriented operational flow
- Role & permission based architecture
- Structured accounting transaction flow
- Modular and scalable Laravel implementation

---

## Screenshots

> Screenshots will be added here.

---

## API Notes

The API layer is currently under active development and intended for internal system integration only.

Public API access is not available at this stage.

---

## License

This project is proprietary and private.  
Unauthorized distribution or commercial use is prohibited.

---

## Developed For

Polije Autohub - Workshop Unit  
Politeknik Negeri Jember
