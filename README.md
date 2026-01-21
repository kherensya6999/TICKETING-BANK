# IT Security Ticketing System - Bank Sumut

Sistem ticketing IT Security lengkap dengan fitur ITIL, Security Incident Management, dan SLA Monitoring.

## Tech Stack

### Backend
- Laravel 10
- PHP 8.1+
- MySQL
- JWT Authentication
- Queue Jobs

### Frontend
- React 18
- TypeScript
- Redux Toolkit
- React Router
- Axios
- Vite

## Installation

### Backend Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Start queue worker
php artisan queue:work
```

### Frontend Setup

```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

## Features

- ✅ Complete ITIL Implementation
- ✅ Security Incident Management
- ✅ Automated SLA Monitoring
- ✅ Auto-Assignment Rules
- ✅ Real-time Notifications
- ✅ Knowledge Base
- ✅ Comprehensive Reporting

## Database Schema

15+ tables including:
- Users & Authentication
- Tickets & Categories
- SLA Policies & Tracking
- Security Incidents
- Teams & Assignments
- Notifications & Audit Logs

## API Endpoints

- `POST /api/auth/login` - User login
- `GET /api/tickets` - List tickets
- `POST /api/tickets` - Create ticket
- `GET /api/tickets/{id}` - Get ticket details
- `PUT /api/tickets/{id}` - Update ticket
- `POST /api/tickets/{id}/resolve` - Resolve ticket
- `POST /api/tickets/{id}/comments` - Add comment

## License

MIT
