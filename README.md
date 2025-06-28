# 🚀 Laravel API Demo - Modern Development Practices

> **A production-ready Laravel API showcasing modern development practices, JWT authentication, and code quality standards.**

This repository demonstrates a complete Laravel API implementation with best practices, perfect for tech talks, workshops, and learning modern PHP development.

## 🎯 What This Demo Covers

### 🔐 Authentication & Security
- **JWT Authentication** using `php-open-source-saver/jwt-auth`
- **Authorization Policies** for Posts and Comments
- **Form Request Validation** with custom rules
- **Secure API endpoints** with proper middleware

### 🏗️ Architecture & Design
- **RESTful API Design** with proper resource routing
- **Eloquent Relationships** (Posts ↔ Comments)
- **API Resources** for consistent JSON responses
- **Shallow Resource Routing** for cleaner URLs

### 🛠️ Code Quality & Standards
- **PHPStan Level 10** static analysis
- **Larastan** for Laravel-specific analysis
- **Type Hints & Return Types** throughout
- **PHPDoc Annotations** for better IDE support
- **Clean Code Principles** applied

### 📊 Database & Models
- **Eloquent ORM** with proper relationships
- **Database Migrations** for version control
- **Mass Assignment Protection** with `$fillable`
- **Timestamps** and proper indexing

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js (for frontend if needed)

### Installation

```bash
# Clone the repository
git clone <your-repo-url>
cd laravel-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Run migrations
php artisan migrate

# Start the development server
php artisan serve
```

### Testing the API

```bash
# Run PHPStan analysis
composer phpstan

# Run tests
php artisan test
```

## 📚 API Endpoints

### Authentication
```http
POST /api/register
POST /api/login
POST /api/logout
POST /api/refresh
GET  /api/user-profile
```

### Posts
```http
GET    /api/posts          # List all posts
POST   /api/posts          # Create a new post
GET    /api/posts/{id}     # Get a specific post
PUT    /api/posts/{id}     # Update a post
DELETE /api/posts/{id}     # Delete a post
```

### Comments
```http
GET    /api/posts/{post}/comments  # List comments for a post
POST   /api/posts/{post}/comments  # Create a comment for a post
GET    /api/comments/{id}          # Get a specific comment (shallow)
PUT    /api/comments/{id}          # Update a comment (shallow)
DELETE /api/comments/{id}          # Delete a comment (shallow)
```

## 🔧 Key Features Explained

### 1. JWT Authentication
```php
// User model implements JWTSubject
class User extends Authenticatable implements JWTSubject
{
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
```

### 2. Resource Controllers with Type Safety
```php
class PostController extends Controller
{
    public function index(): PostCollection
    {
        return PostCollection::make(Post::with('comments')->get());
    }
    
    public function store(StorePostRequest $request): PostResource
    {
        $post = Post::create($request->validated());
        return PostResource::make($post);
    }
}
```

### 3. Eloquent Relationships
```php
class Post extends Model
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
```

### 4. Form Request Validation
```php
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ];
    }
}
```

### 5. API Resources
```php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

## 🧪 Code Quality Tools

### PHPStan Configuration
```yaml
# phpstan.neon
parameters:
    level: 10
    paths:
        - app
        - routes/api.php
    excludePaths:
        - app/Models/User.php  # JWT interface issues
    ignoreErrors:
        - '#Method .*::post\(\) should return.*#'
        - '#Method .*::comments\(\) should return.*#'
```

### Running Analysis
```bash
# Full analysis
./vendor/bin/phpstan analyse --memory-limit=512M

# Quick check
composer phpstan
```

## 🏛️ Project Structure

```
laravel-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # API Controllers
│   │   ├── Requests/        # Form Request Validation
│   │   ├── Resources/       # API Resources
│   │   └── Middleware/      # Custom Middleware
│   ├── Models/              # Eloquent Models
│   └── Policies/            # Authorization Policies
├── database/
│   └── migrations/          # Database Schema
├── routes/
│   └── api.php             # API Routes
├── config/
│   ├── auth.php            # Authentication Config
│   └── passport.php        # JWT Config
└── tests/                  # Test Suite
```

## 🎓 Learning Outcomes

This demo showcases:

1. **Modern PHP Development** with type safety and static analysis
2. **Laravel Best Practices** for API development
3. **Authentication Patterns** using JWT
4. **Code Quality Standards** with PHPStan
5. **RESTful API Design** principles
6. **Database Design** with proper relationships
7. **Security Considerations** in API development

## 🤝 Contributing

This is a demo repository, but contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Ensure PHPStan passes
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- **Laravel Team** for the amazing framework
- **PHPStan** for static analysis tools
- **Larastan** for Laravel-specific analysis
- **JWT Auth** for authentication implementation

---

**Perfect for:** Tech talks, workshops, learning Laravel, API development demonstrations, and showcasing modern PHP practices! 🚀
