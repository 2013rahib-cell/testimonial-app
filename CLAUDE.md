# Testimonial SaaS ("Wall of Love") – Claude Guide

> Read this first. Deeper detail lives in [`docs/PROJECT_OVERVIEW.md`](docs/PROJECT_OVERVIEW.md)
> (requirements, flows, routes) and [`database-design.md`](database-design.md) (schema / ERD).

## WHY
Businesses **collect** customer testimonials through a public link, **curate** them in an inbox,
and **embed** the approved ones as a "Wall of Love" widget on any website. Freemium:
Free (3 spaces, 100 testimonials/space) and Pro ($9.99/mo via Stripe: 25 spaces, 1,000/space).

## WHAT – actual stack (verify in composer.json / package.json)
- **Laravel 13**, PHP 8.3+ (dev machine runs 8.4), **SQLite** (`database/database.sqlite`)
- **Livewire 4** class components + Blade views, **Flux UI** components, **Tailwind CSS 4**
- **Auth: Laravel Fortify** (Livewire starter kit: login, register, reset, email verification, 2FA, passkeys).
  *Not* Breeze, despite older docs.
- **Stripe** (`stripe/stripe-php`): Checkout, Billing Portal, signed webhooks
- Frontend build: **vite-plus** (`npm run build` / `npm run dev`). On Windows it needs the
  Microsoft Visual C++ Redistributable (`winget install Microsoft.VCRedist.2015+.x64`).
- Tests: **PHPUnit 12** class-style tests in `tests/` (Pest is *not* installed; follow existing style).
  Playwright E2E specs in `e2e/`.

## Domain model (3 tables)
| Model | Key fields | Notes |
|---|---|---|
| `User` | `plan` (enum `UserPlan` free/pro), `stripe_customer_id`, `stripe_subscription_id`, `subscription_status` | Plan limits: `maxSpaces()`, `maxTestimonialsPerSpace()`. Billing fields are **not** fillable. |
| `Space` | `user_id`, unique `slug`, `name`, `title`, `subtitle`, `ask`, `theme` (enum `SpaceTheme`), `collect_*`/`require_*` toggles (company, social links, avatar), `enable_ratings`, `is_active` | One collection campaign. Public URL `/s/{slug}` (alias `/space/{slug}`). |
| `Testimonial` | `space_id`, `submitter_name/email/company/role/social_url/avatar_path`, `content`, `rating`, `status` (enum `TestimonialStatus`), `is_featured`, `is_hidden` | SRS term mapping: **"Wall of Love / heart" = `status = approved`**, **"Favorite / pin" = `is_featured`**, **"Hide" = `is_hidden`**. |

**Public embed shows only `status = approved AND is_hidden = false`** (`approved()->visible()` scopes).

## Codebase map
```
app/Livewire/Dashboard.php            metrics + daily/weekly SVG trend chart (DashboardAnalyticsService)
app/Livewire/Spaces/{Index,Create,Edit,Success,Inbox}.php   space CRUD + inbox curation
app/Livewire/Embed/Generator.php      widget builder: layout, theme, bg colour, animations, live preview, snippets
app/Livewire/Public/SubmitTestimonial.php   public form (no auth), consent, rate-limited, thank-you GIF
app/Livewire/Public/WallOfLoveEmbed.php     iframe page /embed/wall-of-love/{slug}?layout&theme&bg&animations
app/Livewire/Public/WallPage.php            shareable public wall page /wall/{slug}
resources/views/errors/*.blade.php          branded 403/404/419/500/503 pages (inline CSS, no Vite needed)
Dockerfile, docker/, render.yaml            production image + Render Blueprint (see README "Deployment")
app/Livewire/Settings/Billing.php     plan overview, Stripe checkout/portal
app/Http/Controllers/StripeWebhookController.php   POST /stripe/webhook (CSRF-exempt, signature-verified)
app/Actions/{Create,Update}SpaceAction.php   business logic (plan limit, unique slug generation)
app/Services/{StripeService,DashboardAnalyticsService}.php
app/Policies/SpacePolicy.php          owner-only access; used via Gate::authorize in Spaces components
app/Support/CssColor.php              whitelist for user colours placed in inline CSS
public/embed.js                       host-page script: injects auto-resizing iframe(s)
resources/views/livewire/**           Blade views (one per component)
resources/views/livewire/public/partials/testimonial-card.blade.php   shared card (embed + preview)
```

## HOW – rules
### Artisan & CLI
- Generate code with `php artisan make:*` (models, migrations, livewire, tests, `make:class`).
### Code style
- `declare(strict_types=1);` in every PHP file; PSR-12; typed properties/params/returns.
- Prefer `readonly` + constructor promotion; use **enums** for status/type fields.
- Mark Livewire model properties that must never change client-side with `#[Locked]`.
### Security (non-negotiable)
- **Tenant isolation:** every space/testimonial query is scoped through the owner
  (`$user->spaces()`, `$this->space->testimonials()`) or authorised with `Gate::authorize(..., $space)`.
- Never trust URL params rendered into CSS/HTML – sanitise (see `CssColor`).
- Stripe webhook must verify `STRIPE_WEBHOOK_SECRET`; unsigned events are only accepted in `local`/`testing`.
- Public submit form: validate per space toggles, explicit consent, `RateLimiter` (10/hour/IP/space),
  only store an avatar when the space collects avatars, social links must be http(s).
- Never commit `.env` or secrets; only `.env.example` placeholders.
### Testing
- Feature tests for every endpoint/component; unit tests for services. Run `php artisan test` before committing.
- `tests/Feature/BugFixRegressionTest.php` holds regression tests for audited bugs; keep it green.
### Git
- Atomic, descriptive commits on feature branches; never push directly to `main`.
### Database
- Schema changes only via new migrations; seed with factories/seeders.

## Local setup / run
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # demo user: see DatabaseSeeder
php artisan storage:link            # required for uploaded avatars
npm run build                       # or: composer run dev (server + vite + queue + logs)
php artisan serve
```
- `MAIL_MAILER=log`: email-verification links land in `storage/logs/laravel.log`
  (dashboard routes require a verified email).
- Without Stripe keys, billing uses the **demo checkout** (`MockPaymentGateway`, `Settings\MockCheckout`,
  route `billing.demo-checkout`): test card 4242… upgrades to Pro, 4000…0002 is declined, Pro users can cancel.
  It switches off automatically once `STRIPE_SECRET` is set (or with `BILLING_MOCK=false`). Card data is never stored.

## CI / Deploy
- GitHub Actions (`.github/workflows/tests.yml`) runs `composer setup` + `composer ci:check`
  (Pint `--test`, Larastan, PHPUnit). Keep all three green: run `composer test` locally.
- Livewire `#[Computed]` properties must be declared as `@property-read` on the class for Larastan.
- Dates are `CarbonImmutable` app-wide (`Date::use` in AppServiceProvider) – type model dates accordingly.
- Render: `render.yaml` (Docker, free plan). `docker/entrypoint.sh` migrates, seeds the demo user
  (`SEED_DEMO_DATA`), caches config. `trustProxies(at: '*')` in `bootstrap/app.php` is required for https.
- `fakerphp/faker` is a production dependency on purpose (demo seeding in the container).

## Gotchas
- Tailwind only compiles class names that appear literally – never build classes like `pt-{{ $n }}`.
- `public/embed.js` must capture `document.currentScript` at load time (it is null in callbacks).
- The embed page posts `{type:'wol-resize', space, height}` to the parent; `embed.js` only trusts its own origin.
