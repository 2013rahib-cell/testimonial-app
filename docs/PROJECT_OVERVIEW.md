# Project Overview – Testimonial SaaS ("Wall of Love")

Condensed from the original SRS (technical spec) and SRD (business requirements), and
reconciled with the code as it actually exists. Start with [`../CLAUDE.md`](../CLAUDE.md).

## 1. Actors
| Actor | Auth | Goal |
|---|---|---|
| **Admin / Subscriber** | Fortify session login (email verified) | Create spaces, curate testimonials, embed the widget, manage the plan |
| **End customer / Submitter** | none | Leave a testimonial on a space's public page |
| **Website visitor** | none | Sees the embedded Wall of Love (iframe) |
| **Stripe** | webhook signature | Notifies payment/subscription changes |

## 2. Functional requirements → implementation

| SRD requirement | Where it lives |
|---|---|
| Register / login / password reset | Fortify + `resources/views/livewire/auth/*` |
| Dashboard metrics (total testimonials, unique submitters, approved, avg rating) | `Livewire\Dashboard` + `Services\DashboardAnalyticsService::getSummaryMetrics()` |
| Trend graph (daily / weekly density) | `DashboardAnalyticsService::getTimeSeriesData()` → SVG path in `livewire/dashboard.blade.php` |
| Upgrade nudge banner for Free users (hidden for Pro) | `livewire/dashboard.blade.php` (dismissible per session) |
| Spaces: title, subtitle, ask, themes | `Spaces\Create` / `Spaces\Edit` → `CreateSpaceAction` / `UpdateSpaceAction`; themes = `Enums\SpaceTheme` |
| Dynamic fields: name+email always required; toggle visible/required for company, social links, photo; optional 5-star rating | Space booleans `collect_*`, `require_*`, `enable_ratings`; enforced in `Public\SubmitTestimonial::submit()` |
| Unique public URL per space | `/s/{slug}` (`spaces.submit`) and `/space/{slug}` (`spaces.submit.space`) |
| Mandatory social-sharing consent checkbox | `SubmitTestimonial::$consent` (`accepted` rule, never pre-checked) |
| Success state with humorous GIF | `public/images/thank-you.gif` in `submit-testimonial.blade.php` |
| Inbox: list per space, Favorite (pin), Wall of Love (heart = approve), ⋯ menu Edit / Hide / Delete | `Spaces\Inbox` (filters: all/pending/approved/favorites/hidden) |
| Embed generator: masonry/carousel, dark/light, bg colour, animations, live preview, copyable JS + iframe | `Embed\Generator` + `livewire/embed/generator.blade.php` |
| Embed endpoint: stripped-down HTML of approved testimonials | `Public\WallOfLoveEmbed` at `/embed/wall-of-love/{slug}` (layout `layouts.public`) |
| Plan limits Free 3/100, Pro 25/1000 | `Enums\UserPlan::maxSpaces()` / `maxTestimonialsPerSpace()`; enforced in `CreateSpaceAction` and `SubmitTestimonial` |
| Stripe Checkout + webhook upgrade to Pro | `Services\StripeService`, `Settings\Billing`, `StripeWebhookController` |

> SRS wording vs code: the SRS mentions `/embed/{slug}` and `is_wall_of_love`; the code uses
> `/embed/wall-of-love/{slug}` and `status = approved`. `config_json` toggles are individual boolean columns.

## 3. Key flows

### Collect
1. Admin creates a Space → redirected to `spaces.success` with the shareable link.
2. Customer opens `/s/{slug}` → fills form → `submit()` validates per-space rules, checks
   `is_active`, plan quota and rate limit → creates `Testimonial(status = pending)` → thank-you GIF.

### Curate (Inbox `/spaces/{slug}/inbox`)
- ★ `toggleFavorite` → `is_featured` (pinned first everywhere)
- ♥ `toggleApproval` → `status` approved ⇄ pending (approved = eligible for the wall)
- ⋯ `startEdit/saveEdit` (fix typos), `toggleHidden`, `deleteTestimonial`

### Embed
- Generator builds `/embed/wall-of-love/{slug}?layout=masonry|carousel&theme=light|dark&bg=%23hex&animations=1|0`.
- **iframe snippet**: paste directly.
- **script snippet**: `<div id="wall-of-love" data-space="slug" …>` + `<script src="{APP_URL}/embed.js" defer>`.
  `embed.js` injects the iframe; the embed page posts `wol-resize` messages so the iframe auto-sizes.

### Billing
1. Free user clicks Upgrade (Settings → Billing) → `StripeService::createProCheckoutSession()` →
   Stripe Checkout (`client_reference_id` = user id).
2. Stripe → `POST /stripe/webhook` (`checkout.session.completed`, `customer.subscription.*`,
   `invoice.*`) → user `plan` = pro / free, `subscription_status` updated.
3. Pro users get "Manage Billing" → Stripe Billing Portal.

## 4. Routes
| Method | URI | Name | Auth |
|---|---|---|---|
| GET | `/` | home | – |
| GET | `/s/{slug}`, `/space/{slug}` | spaces.submit(.space) | – |
| GET | `/embed/wall-of-love/{slug}` | wall-of-love.embed | – |
| GET | `/wall/{slug}` | wall.show (shareable public wall) | – |
| POST | `/stripe/webhook` | stripe.webhook | Stripe signature, CSRF-exempt |
| GET | `/dashboard` | dashboard | auth+verified |
| GET | `/spaces`, `/spaces/create`, `/spaces/{slug}/edit|success|inbox` | spaces.* | auth+verified, owner (SpacePolicy) |
| GET | `/embeds` | embed.generator | auth+verified |
| GET | `/settings/profile|billing|appearance|security` | … | auth |
| POST | `/settings/billing/checkout|portal` | billing.* | auth |

## 5. Environment
| Key | Purpose |
|---|---|
| `APP_URL` | Used in embed snippets – must be the public URL in production |
| `DB_CONNECTION=sqlite` | `database/database.sqlite` |
| `STRIPE_KEY`, `STRIPE_SECRET` | API keys (Billing shows "not configured" when empty) |
| `STRIPE_WEBHOOK_SECRET` | **Required in production**; webhook returns 500 without it |
| `STRIPE_PRICE_ID` | Optional; otherwise an ad-hoc $9.99/month price is used |

Demo data: `php artisan migrate --seed` → login `demo@example.com` / `password` (Free plan,
sample spaces incl. `/s/test-product`).

## 6. Quality & tooling
- `php artisan test` – PHPUnit feature + unit tests (incl. `BugFixRegressionTest`).
- `npm run test:e2e` – Playwright (`e2e/space-submission.spec.ts`, config `playwright.config.ts`).
- Project subagents: `.claude/agents/security-reviewer.md` (tenant isolation audit),
  `.claude/agents/qa-tester.md` (Playwright UI checks).

## 7. Audit log (2026-11-13)
Fixed: unsigned Stripe webhook accepted in any env (free Pro upgrade); CSS injection via embed `bg`;
social link validated but never stored (new `submitter_social_url` column); consent pre-checked;
avatar stored even when the space doesn't collect it; no submission rate limit; embed iframe never
auto-resized (cut off); `embed.js` origin detection and message-origin check; StripeService crashed
without keys; raw Stripe errors shown to users; Edit page stale after slug change; dynamic Tailwind
class in inbox; default Laravel welcome page and starter-kit sidebar links; `SpacePolicy` unused;
missing `public/storage` link and frontend build.
