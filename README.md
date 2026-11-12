# 🧡 Testimonial SaaS — Wall of Love

A multi-tenant SaaS platform for collecting, curating, and embedding customer testimonials. Built as a **BS23 Agentic AI Assignment** using Laravel 11, Livewire, Tailwind CSS, and Stripe billing.

---

## 🚀 How to Run Locally

Follow these steps in order to get the application running on your local machine.

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ & npm
- SQLite (bundled with PHP)
- A [Stripe](https://stripe.com) account (for billing features)
- [Stripe CLI](https://stripe.com/docs/stripe-cli) (for forwarding webhook events locally)

### Step-by-Step Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies and build frontend assets
npm install && npm run build

# 3. Copy the environment file and configure your variables
cp .env.example .env

# 4. Generate the application encryption key
php artisan key:generate

# 5. Run database migrations (creates the SQLite database automatically)
php artisan migrate

# 6. Start the development server
php artisan serve
```

> **Note:** Open `http://localhost:8000` in your browser after `php artisan serve` starts.

### Stripe Webhook Forwarding (Local Development)

Stripe webhooks cannot reach `localhost` by default. Use the Stripe CLI to forward live events to your local server:

```bash
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

Copy the **webhook signing secret** printed by the CLI and set it in your `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

---

## ✅ Feature Status

### Completed Features

| Feature | Status |
|---------|--------|
| 🔐 **Authentication** (register, login, password reset via Laravel Breeze) | ✅ Done |
| 📊 **Dashboard Analytics** (testimonial counts, approval stats, space overview) | ✅ Done |
| 🛠️ **Space Builder** (create/edit spaces with themes, logo upload, custom CTA, toggle active/inactive) | ✅ Done |
| 🌐 **Public Submission Page** (branded page with funny GIF encouragement for submitters) | ✅ Done |
| 📥 **Inbox Management** (approve, reject, feature testimonials from a Livewire-powered inbox) | ✅ Done |
| 🧱 **Masonry / Carousel Embed Code Generator** (copy-paste `<script>` tag for any website) | ✅ Done |
| 💳 **Freemium Stripe Billing & Webhooks** (Free vs Pro plan, Stripe Checkout, subscription lifecycle via webhooks) | ✅ Done |

### Known Limitations

- **Stripe CLI required locally** — Live Stripe webhook events cannot be received without running `stripe listen --forward-to ...` on your local machine. The Stripe dashboard's "Test" webhook delivery can be used as an alternative.
- **No video testimonial upload** — The schema supports `video_url` for linking external videos, but in-browser video recording/upload is not yet implemented.
- **Email notifications not wired** — Approval/rejection notifications to submitters are scaffolded but not connected to a mail driver (configure `MAIL_*` in `.env`).
- **SQLite for development only** — The default database is SQLite. For production, switch to MySQL/PostgreSQL by updating `DB_CONNECTION` in `.env`.

---

## 🤖 Agentic Tools Used

This project was built using an **agentic AI development workflow**. Below is a detailed account of how AI tooling accelerated development.

### Context Rules: `CLAUDE.md` / `GEMINI.md`

Both `CLAUDE.md` and `GEMINI.md` files are present in the project root and contain identical project rules. These files act as **persistent context anchors** — every AI agent session (whether Claude or Gemini/Antigravity) automatically reads these rules before generating any code. This enforced:

- Strict PHP typing (`declare(strict_types=1)`) across all files
- PSR-12 code style compliance
- Multi-file Livewire component structure
- Pest for all tests (not raw PHPUnit)
- Atomic, descriptive git commits with feature branches

### `database-design.md`

The `database-design.md` file was authored upfront and served as the **single source of truth** for the entire schema. Before writing a single migration, the agent referenced this document to ensure:

- Correct column types, nullability, and defaults
- Proper Eloquent relationships (`HasMany`, `BelongsTo`, `HasManyThrough`)
- Freemium plan limit enforcement strategy (via Action classes, not controllers)
- Key indexes for performance (Stripe customer ID lookup, slug uniqueness, etc.)

### `security-reviewer` Subagent (Tenant Isolation & Mass-Assignment Audit)

A dedicated **`security-reviewer` subagent** was invoked after the Space and Testimonial CRUD was complete. Its responsibilities:

- **Tenant isolation audit**: Verified that every query fetching spaces or testimonials was scoped to `auth()->user()`, preventing cross-user data leakage.
- **Mass-assignment audit**: Confirmed `$fillable` arrays on all models were explicit and that no wildcard `$guarded = []` was used.
- **Policy review**: Checked that `SpacePolicy` and `TestimonialPolicy` were registered and applied to all relevant Livewire components and controllers.

### Parallel Git Branching

The two most complex features were developed in **parallel on isolated branches** to avoid blocking each other:

| Branch | Feature |
|--------|---------|
| `feature/stripe-billing` | Stripe Checkout session, webhook handler (`/stripe/webhook`), plan limit enforcement, upgrade/downgrade flow |
| `feature/embed-generator` | Embed code generator UI, public Wall of Love endpoint (`/embed/{slug}`), Masonry & Carousel layout rendering |

Each branch was reviewed and merged into `main` independently, keeping the commit history clean.

### `qa-tester` Subagent (Playwright End-to-End Verification)

A **`qa-tester` subagent** was used to run Playwright end-to-end tests against the running dev server. This covered:

- Full registration → space creation → public submission → inbox approval → embed code copy flow
- Stripe Checkout redirect and post-payment plan upgrade verification
- Embed widget rendering on a blank HTML page (cross-origin iframe/script injection test)
- Responsive layout checks for the public submission page and Wall of Love

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.3+) |
| Database | SQLite (dev) / MySQL or PostgreSQL (prod) |
| Frontend | Livewire 3 + Tailwind CSS v3 |
| Auth | Laravel Breeze |
| Payments | Stripe (Checkout + Webhooks) |
| Testing | Pest (feature + unit) |
| E2E Tests | Playwright |

---

## 📁 Project Structure Highlights

```
app/
├── Actions/          # Business logic (CreateSpaceAction, CreateTestimonialAction, etc.)
├── Livewire/         # Multi-file Livewire components (PHP + Blade)
├── Models/           # Eloquent models (User, Space, Testimonial)
├── Policies/         # Authorization policies (SpacePolicy, TestimonialPolicy)
└── Http/
    └── Controllers/  # Thin controllers (Stripe webhook handler, embed endpoint)
resources/views/
└── livewire/         # Blade views paired with Livewire components
database/
├── migrations/       # All schema changes as migrations
├── factories/        # Model factories for testing
└── seeders/          # Test data seeders
e2e/                  # Playwright end-to-end tests
```

---

## 📄 License

This project is for educational purposes as part of the **BS23 Agentic AI Assignment**.
