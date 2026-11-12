# Testimonial SaaS – Project Rules

## WHY
Capture, manage, and embed customer testimonials ("Wall of Love"). This platform allows businesses to collect authentic social proof, curate their best testimonials, and embed a beautiful "Wall of Love" widget anywhere on the web.

## WHAT
This project is built with the following technology stack:

- **Laravel 11** – Backend framework (PHP 8.3+)
- **SQLite** – Default database (simple, file-based, zero-config for development)
- **Livewire** – Multi-file components for reactive UI without leaving PHP
- **Tailwind CSS** – Utility-first CSS framework for styling
- **Laravel Breeze** – Lightweight authentication scaffolding (login, register, password reset)

## HOW
Follow these development conventions at all times:

### Artisan & CLI
- Use `php artisan` commands for all code generation (models, controllers, migrations, components, etc.)
- Never manually create boilerplate files that Artisan can generate

### Code Style
- Use **strict typed PHP** in all files: `declare(strict_types=1);`
- Follow PSR-12 coding standards
- Use typed properties, return types, and parameter types everywhere
- Prefer `readonly` properties and constructor promotion where appropriate
- Use `enum` for status/type fields

### Testing
- Use **Pest** (not PHPUnit directly) for all tests
- Write feature tests for all HTTP endpoints
- Write unit tests for all service classes and complex logic
- Run tests before every commit: `php artisan test`

### Security
- **Never commit secrets** – all credentials go in `.env`
- **Never commit `.env` files** – only `.env.example` with placeholder values
- Use Laravel's built-in encryption for sensitive data
- Validate and authorize every request via Form Requests and Policies

### Git
- Keep commits atomic and descriptive
- Never push directly to `main`; always use feature branches and PRs
- `.env`, `storage/`, and `vendor/` are in `.gitignore`

### Livewire Components
- Organize Livewire components as **multi-file components** (`app/Livewire/ComponentName.php` + `resources/views/livewire/component-name.blade.php`)
- Keep component logic lean; extract business logic into Action or Service classes

### Database
- Write all schema changes as **migrations** (never edit existing migrations in production)
- Use Eloquent relationships and avoid raw queries unless necessary for performance
- Seed test data with Factories and Seeders
