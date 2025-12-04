# OpenBiz Suite - Testing Guide

## Current Status

The project has been successfully developed with 16 commits:

### ✅ Completed Features

**Phase 1 - Foundation (100% Complete)**
- Docker infrastructure with all required services
- Laravel 11 application setup
- Multi-tenancy system with automatic tenant scoping
- Authentication with Laravel Sanctum
- Two-Factor Authentication (2FA) with QR codes and recovery codes
- Role-Based Access Control (RBAC) with Spatie Permissions
- Audit logging with Spatie ActivityLog
- Filament admin panel with multi-tenant support

**Phase 2 - HR Module (100% Complete)**
- Employee, Department, and Position management
- Time tracking system with clock in/out and approval workflow
- Leave management with types, requests, and automatic balance tracking
- Employee document management with file uploads

**Phase 3 - Asset Management (Models Complete)**
- Asset categories and asset tracking
- QR code generation for assets
- Asset assignment workflow
- Asset maintenance tracking

### Testing Setup (Option 1: Using Docker)

1. **Install composer dependencies**:
   ```bash
   docker run --rm -v "${PWD}/src:/app" -w /app composer:latest install
   ```

2. **Copy environment file**:
   ```bash
   cp src/.env.example src/.env
   ```

3. **Generate application key**:
   ```bash
   docker run --rm -v "${PWD}/src:/app" -w /app php:8.3-cli php artisan key:generate
   ```

4. **Start all services**:
   ```bash
   docker-compose up -d
   ```

5. **Run migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Seed database**:
   ```bash
   docker-compose exec app php artisan db:seed
   ```

7. **Access the application**:
   - Web: http://localhost
   - Admin Panel: http://localhost/admin
   - phpMyAdmin: http://localhost:8080

### Testing Setup (Option 2: Using Local PHP with OpenSSL fix)

1. **Install OpenSSL extension**:
   ```powershell
   # Reinstall PHP with OpenSSL support
   scoop uninstall php
   scoop install php
   ```

2. **Enable OpenSSL in php.ini**:
   Find your php.ini file and uncomment:
   ```ini
   extension=openssl
   ```

3. **Install dependencies**:
   ```bash
   cd src
   composer install
   ```

4. **Setup environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=openbiz_suite
   DB_USERNAME=root
   DB_PASSWORD=secret
   ```

6. **Run migrations and seeders**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Start development server**:
   ```bash
   php artisan serve
   ```

### Test Users (After Seeding)

- **Super Admin**
  - Email: admin@demo.com
  - Password: password

- **HR Manager**
  - Email: hr@demo.com
  - Password: password

- **Employee**
  - Email: user@demo.com
  - Password: password

### Features to Test

#### 1. Authentication & 2FA
- [ ] Register new user
- [ ] Login with credentials
- [ ] Enable 2FA
- [ ] Scan QR code with Google Authenticator
- [ ] Login with 2FA code
- [ ] Use recovery codes

#### 2. Multi-Tenancy
- [ ] Switch between tenants
- [ ] Verify data isolation between tenants
- [ ] Check tenant-scoped queries

#### 3. User & Role Management
- [ ] Create users
- [ ] Assign roles
- [ ] Test permissions (Super Admin, Admin, HR Manager, Employee)
- [ ] Verify role-based access

#### 4. Employee Management
- [ ] Create departments
- [ ] Create positions
- [ ] Add employees with full details
- [ ] Upload employee documents
- [ ] View employee profiles

#### 5. Time Tracking
- [ ] Create timesheet entries
- [ ] Submit timesheets for approval
- [ ] Approve/reject timesheets (as manager)
- [ ] View timesheet reports

#### 6. Leave Management
- [ ] Create leave types (Annual, Sick, etc.)
- [ ] Submit leave requests
- [ ] Approve/reject leave requests
- [ ] Check leave balances
- [ ] View leave calendar

#### 7. Document Management
- [ ] Upload employee documents
- [ ] View documents
- [ ] Track document expiry
- [ ] Filter documents by type

#### 8. Asset Management
- [ ] Create asset categories
- [ ] Add assets
- [ ] Generate QR codes for assets
- [ ] Assign assets to employees
- [ ] Track asset maintenance
- [ ] View asset history

#### 9. Audit Logging
- [ ] View activity logs
- [ ] Filter logs by user/model
- [ ] Track all CRUD operations

### Common Issues & Fixes

**Issue: Composer OpenSSL Error**
```bash
# Fix by enabling OpenSSL in php.ini or use Docker
docker run --rm -v "${PWD}/src:/app" -w /app composer:latest install
```

**Issue: Database Connection Error**
```bash
# Check database credentials in .env
# Ensure MySQL is running (via Docker or local)
docker-compose up -d mysql
```

**Issue: Permission Denied on storage**
```bash
# Fix storage permissions
chmod -R 775 src/storage src/bootstrap/cache
```

**Issue: Migration Errors**
```bash
# Fresh migration
php artisan migrate:fresh --seed
```

### API Testing

The project includes REST API endpoints that can be tested with tools like Postman or cURL:

**Register User**:
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

**Login**:
```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@demo.com",
    "password": "password"
  }'
```

### Next Steps

Continue development with:
- Phase 4: API Gateway (REST & GraphQL)
- Phase 5: LMS Module
- Phase 6: Advanced Features (Workflow Engine, AI Integration)
- Phase 7: Testing & Polish

### Support

For issues or questions:
1. Check logs: `src/storage/logs/laravel.log`
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Review error messages in browser console
