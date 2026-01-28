# 06-DEVELOPMENT-GUIDE.md

## Development Environment Setup

### Prerequisites
- Docker and Docker Compose
- Git
- VS Code (recommended)

### Environment Overview
The project uses three Docker containers:
- `php_dev_php8.2`: PHP 8.2.30 + Composer 2.9.3
- `php_dev_nodejs_20`: Node.js 20.19.6 + npm 10.8.2
- `php_dev_nginx`: Nginx web server

### Initial Setup
1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd laravel-dashboard1
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   # Edit .env with your database and other settings
   ```

3. **Install Dependencies**
   ```bash
   # PHP dependencies (via container)
   docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar install'
   
   # Node.js dependencies (via container)
   docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm install'
   ```

4. **Database Setup**
   ```bash
   # Run migrations
   docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan migrate'
   
   # Seed database
   docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan db:seed'
   ```

5. **Build Assets**
   ```bash
   docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
   ```

### Development Workflow

#### Starting Development Server
```bash
# Using Composer script (concurrent servers)
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar run dev'

# Or manually:
# Terminal 1: Laravel server
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan serve --host=0.0.0.0 --port=8000'

# Terminal 2: Queue worker
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan queue:work'

# Terminal 3: Vite dev server
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'

# Terminal 4: Logs
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan pail'
```

#### Access Points
- **Frontend**: http://localhost:5174/management/login
- **API**: http://localhost/api/v1/
- **Laravel App**: http://localhost:8000

## Code Organization Conventions

### Backend (Laravel)

#### Controllers
- **Location**: `app/Http/Controllers/Api/`
- **Naming**: `{Resource}Controller.php`
- **Structure**:
  ```php
  class UserController extends Controller
  {
      public function __construct(protected UserService $userService) {}
      
      public function index(Request $request): JsonResponse
      {
          // Validation and delegation to service
      }
  }
  ```

#### Services
- **Location**: `app/Services/`
- **Naming**: `{Resource}Service.php`
- **Purpose**: Business logic, data manipulation
- **Pattern**: Constructor injection, comprehensive error handling

#### Models
- **Location**: `app/Models/`
- **Traits**: `HasRoles`, `HasApiTokens`, `SoftDeletes`
- **Relationships**: Defined using Eloquent methods
- **Accessors**: Custom attribute getters (e.g., `getProfileImageUrlAttribute`)

#### Requests
- **Location**: `app/Http/Requests/`
- **Naming**: `{Action}{Resource}Request.php`
- **Purpose**: Input validation and authorization

### Frontend (Vue.js)

#### Component Structure
```
resources/js/vue3_dashboard_admin/
├── components/     # Reusable components
├── views/         # Page components
├── layouts/       # Layout components
├── stores/        # Pinia stores
├── router/        # Route definitions
├── composables/   # Vue composables
├── types/         # TypeScript definitions
└── utils/         # Utility functions
```

#### Naming Conventions
- **Components**: PascalCase (`UserManagement.vue`)
- **Files**: kebab-case (`user-management.vue`)
- **Stores**: camelCase (`useUserStore`)
- **Types**: PascalCase (`UserInterface`)

## Development Commands

### PHP/Laravel (via Docker)
```bash
# Artisan commands
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan [command]'

# Composer commands
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php composer_2.9.3.phar [command]'

# Testing
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan test'
```

### Node.js (via Docker)
```bash
# npm commands
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm [command]'

# Build commands
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run dev'
```

## Testing Strategy

### Backend Testing
- **Framework**: PHPUnit
- **Location**: `tests/`
- **Types**: Feature tests, Unit tests
- **Run Tests**:
  ```bash
  docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan test'
  ```

### Frontend Testing
- **Not implemented**: No test setup currently
- **Recommended**: Vue Test Utils + Vitest

## Code Quality Tools

### Laravel Pint
```bash
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && ./vendor/bin/pint'
```

### Pre-commit Hooks (Recommended)
- PHP CodeSniffer
- ESLint for Vue.js
- Prettier for formatting

## Deployment

### Production Build
```bash
# Build frontend assets
docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm run build'

# Optimize Laravel
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan config:cache'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan route:cache'
docker exec php_dev_php8.2 sh -c 'cd laravel/dashboard1 && php artisan view:cache'
```

### Environment Variables
- Set `APP_ENV=production`
- Configure database credentials
- Set `APP_KEY` from `php artisan key:generate`
- Configure Sanctum settings

## Debugging

### Laravel Debugging
- **Logs**: `storage/logs/laravel.log`
- **Debugbar**: Laravel Debugbar package (install if needed)
- **Telescope**: Laravel Telescope for request monitoring

### Vue.js Debugging
- **Vue DevTools**: Browser extension
- **Console Logging**: Use Vue's dev mode
- **Hot Reload**: Enabled in development

### Docker Debugging
```bash
# Access container shell
docker exec -it php_dev_php8.2 sh
docker exec -it php_dev_nodejs_20 sh

# View logs
docker logs php_dev_php8.2
docker logs php_dev_nginx
```

## Performance Optimization

### Backend
- **Caching**: Use Redis for sessions/cache
- **Queue Jobs**: Move heavy tasks to background
- **Database**: Add proper indexes
- **Assets**: Use CDN for static files

### Frontend
- **Code Splitting**: Vue Router lazy loading
- **Asset Optimization**: Vite build optimization
- **Image Optimization**: Compress uploaded images

## Security Considerations

### Authentication
- Sanctum tokens with expiration
- Rate limiting on auth endpoints
- Password hashing with bcrypt

### Authorization
- Role-based permissions
- Middleware protection on routes
- Super admin protections

### Data Validation
- Form request validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)

### File Upload Security
- File type validation
- Size limits
- Secure storage paths

## Common Issues & Solutions

### Permission Denied
- Ensure Docker containers are running
- Check file permissions in project directory
- Verify user has sudo access if needed

### Database Connection Failed
- Verify `.env` database configuration
- Ensure database container is running
- Check database credentials

### Node Modules Issues
- Delete `node_modules` and `package-lock.json`
- Re-run `npm install`
- Check Node.js version compatibility

### Build Failures
- Clear Laravel caches: `php artisan cache:clear`
- Rebuild frontend: `npm run build`
- Check for syntax errors

## Contributing Guidelines

### Code Style
- Follow PSR-12 for PHP
- Use Vue.js style guide
- Consistent naming conventions
- Comprehensive documentation

### Git Workflow
- Feature branches from `main`
- Pull requests for code review
- Semantic commit messages
- Pre-commit hooks for quality checks

### Documentation
- Update API documentation for endpoint changes
- Document new features in this guide
- Keep README updated

## Monitoring & Maintenance

### Logs
- Laravel logs in `storage/logs/`
- Nginx access/error logs
- Application-specific logging in services

### Backups
- Database backups
- File storage backups
- Configuration backups

### Updates
- Regular dependency updates
- Security patches
- Framework upgrades following Laravel release cycle