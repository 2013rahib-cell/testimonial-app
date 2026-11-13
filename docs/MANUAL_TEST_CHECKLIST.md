# Manual Test Checklist

Run the app (`php artisan serve`, then open http://127.0.0.1:8000). Seed demo data first with `php artisan db:seed`.
Demo login: **demo@example.com / password**. Tick each box; note anything odd next to it.

Tip: test the public pages in a **private/incognito window** (logged out), and the dashboard in a normal window.

## 1. Public site (logged out)
- [ ] `/` – landing page loads; "Log in", "Start free", "See how it works" work
- [ ] `/register` – create an account → you land on "verify email". The link is in `storage/logs/laravel.log` (search `verify-email`); open it → dashboard
- [ ] `/login` – wrong password shows an error; correct login works
- [ ] `/forgot-password` – submitting shows a status message (email goes to the log)
- [ ] `/this-does-not-exist` – branded "Page not found" page with a working button

## 2. Customer submission – `/s/test-product`
- [ ] Click **Send** with everything empty → red errors incl. "Please confirm we may share your testimonial"
- [ ] Stars change the rating ("x of 5 Stars")
- [ ] Social link `linkedin.com/in/me` is accepted; `javascript:alert(1)` is rejected
- [ ] Fill valid data + tick consent → **Thank you** with the GIF
- [ ] "Submit another response" → empty form, consent unticked
- [ ] `/space/test-product` shows the same form
- [ ] Space with photo upload (e.g. `/s/saas-product-alpha`) – upload a JPG/PNG < 2 MB → "Uploading photo…" then submit works

## 3. Dashboard – `/dashboard` (logged in as demo)
- [ ] Metric cards show numbers (total, unique customers, approved, avg rating)
- [ ] Chart switches between **Daily** and **Weekly**
- [ ] Free-plan **upgrade banner** shows and can be dismissed
- [ ] Sidebar: Dashboard, Spaces, Embed Widget, Upgrade to Pro, Settings all navigate; mobile (narrow window) menu opens/closes

## 4. Spaces – `/spaces`
- [ ] List shows spaces with testimonial counts and "x / 3 spaces"
- [ ] Copy public URL works; toggle active/inactive works (inactive space page says "Space Inactive")
- [ ] **Create**: `/spaces/create` → fill name, pick a theme, toggle fields → success page with the share link
- [ ] Creating a 4th space on Free shows the limit message (delete one afterwards)
- [ ] **Edit**: change the title → "Space updated successfully!"; change the slug → URL bar updates to the new slug
- [ ] Delete a throwaway space (confirm dialog appears)

## 5. Inbox – `/spaces/test-product/inbox`
- [ ] Your test submission is there with its social link
- [ ] ★ Favorite pins it to the top ("Pinned")
- [ ] ♥ Heart approves it (badge turns "Approved"); click again to un-approve
- [ ] ⋯ → **Edit Text** → change → Save; Cancel works
- [ ] ⋯ → **Hide from Public** (card fades, "Hidden" badge) → **Show Publicly**
- [ ] Filter tabs (All / Pending / Approved / Favorites / Hidden) show correct items and counts
- [ ] ⋯ → **Delete** asks for confirmation, then removes it

## 6. Embed generator – `/embeds`
- [ ] Switch space in the dropdown → preview changes
- [ ] Masonry ⇄ Carousel, Light ⇄ Dark, colour presets, typing `#1e1b4b` (applies when you leave the field), Animations toggle – preview updates live
- [ ] **Copy Code** (iFrame and JavaScript tabs) shows "Copied!"
- [ ] **Public Wall Page** and **Open Standalone** open in new tabs
- [ ] Only *approved & not hidden* testimonials appear

## 7. Public wall & embed
- [ ] `/wall/saas-product-alpha` – wall page, "Copy link", "Leave a testimonial" works
- [ ] `/embed/wall-of-love/saas-product-alpha?layout=carousel&theme=dark` – carousel arrows scroll
- [ ] Embed script: save this as `test.html` anywhere and open it in the browser – the wall appears and grows to fit:
  ```html
  <div id="wall-of-love" data-space="saas-product-alpha"></div>
  <script src="http://127.0.0.1:8000/embed.js" defer></script>
  ```

## 8. Settings
- [ ] Profile: change name → saved
- [ ] Billing: "Demo mode" badge, plan card + limits
- [ ] **Upgrade to Pro** → demo checkout page (order summary $9.99, card form)
- [ ] Pay with `4000 0000 0000 0002` → "Your card was declined", still Free
- [ ] Pay with `1234 5678 9012 3456`, expiry `01/20`, CVC `12` → field errors
- [ ] Pay with `4242 4242 4242 4242`, any future expiry (e.g. `12/30`), CVC `123` → back on Billing with "Payment successful… Welcome to Pro!", plan **Pro**, banner gone from dashboard, limit 25 spaces
- [ ] **Cancel subscription** (confirm dialog) → back to Free
- [ ] Appearance: Light / Dark / System switches the theme
- [ ] Security: password change form; 2FA / passkeys section loads
- [ ] Log out from the user menu

## 9. Security spot-checks
- [ ] Logged in as your *new* user, open `/spaces/test-product/inbox` → **403 Access denied**
- [ ] Logged out, open `/dashboard` → redirected to login
