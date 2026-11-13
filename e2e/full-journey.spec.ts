import { test, expect, Browser, BrowserContext, Page } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

// Portable paths: run from the project root (`npx playwright test e2e/full-journey.spec.ts`).
const BASE = process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8000';
const SCRATCH = path.resolve('test-results', 'full-journey');
const SHOTS = path.join(SCRATCH, 'qa-shots');
const LOG_FILE = path.resolve('storage', 'logs', 'laravel.log');
fs.mkdirSync(SHOTS, { recursive: true });

const VIEWPORTS = [
  { name: 'desktop', width: 1280, height: 800 },
  { name: 'mobile', width: 390, height: 844 },
];

const RUN = Date.now().toString(36);
const findings: string[] = [];
const note = (m: string) => { findings.push(m); console.log('FINDING: ' + m); };

test.afterAll(() => {
  fs.writeFileSync(path.join(SCRATCH, 'qa-findings.txt'), findings.join('\n'));
});

type Ctx = { context: BrowserContext; page: Page; issues: string[]; vp: string };

async function newCtx(browser: Browser, vp: (typeof VIEWPORTS)[number]): Promise<Ctx> {
  const context = await browser.newContext({
    viewport: { width: vp.width, height: vp.height },
    permissions: ['clipboard-read', 'clipboard-write'],
  });
  const page = await context.newPage();
  const issues: string[] = [];
  page.on('console', (m) => {
    if (m.type() === 'error') issues.push(`console.error: ${m.text()} @ ${page.url()}`);
  });
  page.on('pageerror', (e) => issues.push(`pageerror: ${e.message} @ ${page.url()}`));
  page.on('response', (r) => {
    if (r.status() >= 400 && !r.url().includes('does-not-exist') && !r.url().includes('/stripe/'))
      issues.push(`HTTP ${r.status()} ${r.url()} (page ${page.url()})`);
  });
  page.on('requestfailed', (r) => {
    const f = r.failure()?.errorText ?? '';
    if (!f.includes('ABORTED')) issues.push(`requestfailed ${r.url()} ${f}`);
  });
  return { context, page, issues, vp: vp.name };
}

/** Generic audit: overflow, broken images, dead anchors, low-contrast text, screenshot, + console/network issues. */
async function audit(c: Ctx, label: string, opts: { links?: boolean } = {}) {
  const { page } = c;
  await page.waitForLoadState('networkidle').catch(() => {});
  const tag = `${c.vp}-${label}`;
  await page.screenshot({ path: path.join(SHOTS, `${tag}.png`), fullPage: true });

  const overflow = await page.evaluate(() => {
    const de = document.documentElement;
    return { sw: de.scrollWidth, cw: de.clientWidth };
  });
  if (overflow.sw > overflow.cw + 1) note(`[${tag}] horizontal overflow: scrollWidth ${overflow.sw} > ${overflow.cw}`);
  expect.soft(overflow.sw, `[${tag}] horizontal overflow`).toBeLessThanOrEqual(overflow.cw + 1);

  const broken = await page.evaluate(() =>
    Array.from(document.images).filter((i) => i.complete && i.naturalWidth === 0 && i.src && getComputedStyle(i).display !== 'none').map((i) => i.src),
  );
  if (broken.length) note(`[${tag}] broken images: ${broken.join(', ')}`);
  expect.soft(broken, `[${tag}] broken images`).toEqual([]);

  const lowContrast = await page.evaluate(() => {
    const parse = (s: string) => { const m = s.match(/rgba?\(([^)]+)\)/); if (!m) return null; const p = m[1].split(/[ ,\/]+/).map(Number); return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 }; };
    const lum = (c: any) => { const f = (v: number) => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }; return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b); };
    const out: string[] = [];
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    const seen = new Set<Element>();
    while (walker.nextNode()) {
      const n = walker.currentNode; const el = n.parentElement; if (!el || seen.has(el) || !n.textContent?.trim()) continue; seen.add(el);
      const cs = getComputedStyle(el); const r = el.getBoundingClientRect();
      if (r.width === 0 || r.height === 0 || cs.visibility === 'hidden' || cs.display === 'none' || +cs.opacity === 0) continue;
      const fg = parse(cs.color); if (!fg) continue; if (cs.backgroundColor.includes('oklch')||cs.backgroundColor.includes('color(')) continue;
      let bg: any = null; let e: Element | null = el; let hasImg = false;
      while (e) { const s = getComputedStyle(e); if (s.backgroundImage !== 'none') { hasImg = true; break; } const b = parse(s.backgroundColor); if (b && b.a > 0.95) { bg = b; break; } if (!b && s.backgroundColor !== 'transparent') { hasImg = true; break; } e = e.parentElement; }
      if (hasImg) continue; if (!bg) bg = { r: 255, g: 255, b: 255, a: 1 };
      const l1 = lum(fg), l2 = lum(bg); const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
      if (ratio < 3 && out.length < 8) out.push(`"${n.textContent!.trim().slice(0, 30)}" ratio ${ratio.toFixed(2)} (${cs.color} on rgb(${bg.r},${bg.g},${bg.b}))`);
    }
    return out;
  });
  if (lowContrast.length) note(`[${tag}] low contrast text (<3:1): ${lowContrast.join(' | ')}`);

  const hrefs = await page.evaluate(() => Array.from(document.querySelectorAll('a')).map((a) => ({ h: a.getAttribute('href') ?? '', t: (a.textContent ?? '').trim().slice(0, 25) })));
  const dead = hrefs.filter((x) => x.h === '' || x.h === '#' || x.h.startsWith('javascript:'));
  if (dead.length) note(`[${tag}] dead anchors: ${dead.map((d) => `"${d.t}"->${d.h || '(empty)'}`).join(', ')}`);
  expect.soft(dead.length, `[${tag}] dead anchors`).toBe(0);
  if (opts.links !== false) {
    const urls = [...new Set(hrefs.map((x) => x.h).filter((h) => (h.startsWith('/') || h.startsWith(BASE)) && !h.includes('logout')))];
    for (const u of urls) {
      const resp = await page.request.get(u.startsWith('/') ? BASE + u : u, { maxRedirects: 5 }).catch(() => null);
      if (!resp || resp.status() >= 400) {
        // authed-only pages when logged out give 302->login (200). Real failures only
        note(`[${tag}] link ${u} -> ${resp ? resp.status() : 'no response'}`);
        expect.soft(resp?.status() ?? 0, `[${tag}] link ${u}`).toBeLessThan(400);
      }
    }
  }

  const iss = c.issues.splice(0);
  if (iss.length) note(`[${tag}] runtime issues: ${iss.join(' || ')}`);
  expect.soft(iss, `[${tag}] console/network issues`).toEqual([]);
}

async function login(c: Ctx, email: string, password: string) {
  await c.page.goto('/login');
  await c.page.fill('input[name=email]', email);
  await c.page.fill('input[name=password]', password);
  await c.page.click('[data-test=login-button]');
  await c.page.waitForLoadState('networkidle');
}

async function logout(c: Ctx) {
  const { page } = c;
  await page.waitForLoadState('networkidle');
  const btn = page.locator('[data-test=logout-button]').filter({ visible: true });
  if (!(await btn.count())) {
    if ((page.viewportSize()?.width ?? 0) >= 1024) await page.locator('[data-test=sidebar-menu-button]').first().click();
    else await page.locator('header button[type=button]').nth(1).click(); // mobile header profile dropdown
  }
  await expect(btn.first()).toBeVisible();
  await btn.first().click();
  await page.waitForLoadState('networkidle');
}

function readVerifyLink(email: string): string | null {
  if (!fs.existsSync(LOG_FILE)) return null;
  const txt = fs.readFileSync(LOG_FILE, 'utf8');
  const idx = txt.lastIndexOf(email);
  if (idx < 0) return null;
  const chunk = txt.slice(idx).replace(/=\r?\n/g, '').replace(/=3D/g, '=').replace(/&amp;/g, '&');
  const m = chunk.match(/https?:\/\/[^\s"'<>]*email\/verify\/[^\s"'<>]+/);
  return m ? m[0] : null;
}

for (const vp of VIEWPORTS) {
  test.describe.serial(`Full journey @ ${vp.name} ${vp.width}x${vp.height}`, () => {
    let c: Ctx;
    let qaName = `QA User ${vp.name} ${RUN}`;
    let qaText = `QA testimonial ${vp.name} ${RUN} great product`;
    let throwText = `Throwaway ${vp.name} ${RUN} delete me`;
    const newEmail = `qa-${vp.name}-${RUN}@example.com`;
    const newPass = 'Passw0rd!QA-123';
    const mySlug = `qa-space-${vp.name}-${RUN}`;
    let myNewSlug = `qa-renamed-${vp.name}-${RUN}`;
    let verified = false;
    let rateLimited = false;

    test.beforeAll(async ({ browser }) => { c = await newCtx(browser, vp); });
    test.beforeEach(() => { test.setTimeout(75000); });
    test.afterAll(async () => { await c.context.close(); });

    // ---------- 1. Public ----------
    test('1a landing page', async () => {
      await c.page.goto('/');
      await expect(c.page.locator('body')).not.toContainText('Laravel is a web application framework');
      const login = c.page.locator('a[href$="/login"]').first();
      const reg = c.page.locator('a[href$="/register"]').first();
      await expect(login).toBeVisible();
      await expect(reg).toBeVisible();
      await audit(c, 'landing');
      await login.click();
      await expect(c.page).toHaveURL(/\/login$/);
      await c.page.goBack();
      await c.page.locator('a[href$="/register"]').first().click();
      await expect(c.page).toHaveURL(/\/register$/);
    });

    test('1b login page + bad credentials', async () => {
      await c.page.goto('/login');
      await expect(c.page.locator('input[name=email]')).toBeVisible();
      await audit(c, 'login');
      await c.page.fill('input[name=email]', 'demo@example.com');
      await c.page.fill('input[name=password]', 'wrong-password');
      await c.page.click('[data-test=login-button]');
      await expect(c.page.locator('body')).toContainText(/do not match|credentials|incorrect|invalid/i);
      c.issues.splice(0);
    });

    test('1c register new user and email verification', async () => {
      await c.page.goto('/register');
      await audit(c, 'register');
      await c.page.fill('input[name=name]', `QA ${vp.name}`);
      await c.page.fill('input[name=email]', newEmail);
      await c.page.fill('input[name=password]', newPass);
      await c.page.fill('input[name=password_confirmation]', newPass);
      await c.page.click('[data-test=register-user-button]');
      await c.page.waitForLoadState('networkidle');
      const url = c.page.url();
      note(`[${vp.name}] after register, URL = ${url} (email verification ${/email[/]verify/.test(url) ? 'REQUIRED' : 'not enforced'})`);
      await expect(c.page).toHaveURL(/email[/]verify|dashboard/);
      await audit(c, 'after-register');
      if (/email[/]verify/.test(url)) {
        await c.page.goto('/dashboard');
        await expect(c.page).toHaveURL(/email[/]verify/); // unverified users are blocked
        const link = readVerifyLink(newEmail);
        if (link) {
          await c.page.goto(link);
          await c.page.waitForLoadState('networkidle');
          verified = true;
          await c.page.goto('/dashboard');
          await expect(c.page).toHaveURL(/dashboard/);
        } else note(`[${vp.name}] verification link not found in laravel.log`);
      } else verified = true;
      c.issues.splice(0);
      await c.page.goto('/dashboard').catch(() => {});
      if (/dashboard/.test(c.page.url())) await logout(c);
    });

    test('1d forgot-password', async () => {
      await c.page.goto('/forgot-password');
      await expect(c.page.locator('input[name=email]')).toBeVisible();
      await audit(c, 'forgot-password');
    });

    test('1e 404 page', async () => {
      const r = await c.page.goto('/does-not-exist');
      expect(r?.status()).toBe(404);
      await expect(c.page.locator('body')).toContainText(/404|not found/i);
      const links = await c.page.locator('a').count();
      note(`[${vp.name}] 404 page anchors: ${links}`);
      c.issues.splice(0);
      await audit(c, '404', { links: false });
    });

    // ---------- 2. Customer flow ----------
    test('2a /s/test-product validation then submit', async () => {
      await c.page.goto('/s/test-product');
      await expect(c.page.locator('h1')).toBeVisible();
      await expect(c.page.locator('input#consent')).not.toBeChecked();
      await audit(c, 'submit-form');
      await c.page.locator('button[type=submit]').click();
      // empty submit: browser-native required validation must block it (no Livewire round trip)
      for (const f of ['name', 'email', 'content']) {
        expect(await c.page.locator('#' + f).evaluate((e: HTMLInputElement) => e.validity.valid), f + ' should be natively invalid').toBe(false);
      }
      await expect(c.page.locator('[data-testid=confirmation-gif]')).toHaveCount(0);
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-submit-empty.png`), fullPage: true });
      // server-side validation: bad values + consent unticked
      const limitedNow = async () => (await c.page.locator('body').textContent())?.includes('Too many submissions');
      await c.page.fill('#name', 'Q');
      await c.page.fill('#email', 'qa@example.com');
      await c.page.fill('#content', 'short');
      await c.page.locator('button[type=submit]').click();
      await expect.poll(async () => (await c.page.locator('body').textContent()) ?? '', { timeout: 15000 }).toMatch(/Too many submissions|at least \d+/);
      if (await limitedNow()) {
        rateLimited = true;
        note(`[${vp.name}] rate limit (10/hour/IP/space) already reached: ${(await c.page.locator('body').textContent())!.match(/Too many submissions[^.]*./)?.[0]}. Rate limiter check runs BEFORE validation so validation errors are masked. Submission steps skipped; using leftover QA testimonials from earlier runs.`);
        await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-submit-ratelimited.png`), fullPage: true });
        return;
      }
      const body = c.page.locator('body');
      await expect(body).toContainText(/content.*at least 10|at least 10/i);
      await expect(body).toContainText(/name.*at least 2|at least 2/i);
      await expect(body).toContainText(/consent|permission|must be accepted|accept/i);
      const errTexts = await c.page.locator('span.text-red-500').allTextContents();
      note(`[${vp.name}] server validation messages: ${errTexts.join(' / ')}`);
      expect(errTexts.length).toBeGreaterThanOrEqual(3);
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-submit-validation.png`), fullPage: true });
      c.issues.splice(0);

      await c.page.locator('button[title="5 Star"]').click();
      await c.page.fill('#content', qaText);
      await c.page.fill('#name', qaName);
      await c.page.fill('#email', `qa-cust-${vp.name}-${RUN}@example.com`);
      if (await c.page.locator('#company').count()) await c.page.fill('#company', 'QA Corp');
      if (await c.page.locator('#role').count()) await c.page.fill('#role', 'Tester');
      if (await c.page.locator('#socialLinks').count()) await c.page.fill('#socialLinks', 'linkedin.com/in/qa-user');
      else note(`[${vp.name}] social link field #socialLinks not present on /s/test-product`);
      await c.page.locator('#consent').check();
      await c.page.locator('button[type=submit]').click();
      const gif = c.page.locator('[data-testid=confirmation-gif]');
      await expect(gif).toBeVisible();
      await expect.poll(() => gif.evaluate((i: HTMLImageElement) => i.complete && i.naturalWidth > 0)).toBe(true);
      await expect(c.page.locator('#content')).toHaveCount(0);
      await audit(c, 'submit-success', { links: false });

      await c.page.getByText('Submit another response').click();
      await expect(c.page.locator('#content')).toBeVisible();
      await expect(c.page.locator('#content')).toHaveValue('');
      await expect(c.page.locator('#name')).toHaveValue('');
      await expect(c.page.locator('#email')).toHaveValue('');
      await expect(c.page.locator('#consent')).not.toBeChecked();
      if (await c.page.locator('#company').count()) await expect(c.page.locator('#company')).toHaveValue('');
      if (await c.page.locator('#socialLinks').count()) await expect(c.page.locator('#socialLinks')).toHaveValue('');
      // rating reset?
      const filled = await c.page.locator('button[title="5 Star"] svg').first().evaluate((s) => getComputedStyle(s).color + '|' + s.getAttribute('class')).catch(() => '');
      note(`[${vp.name}] 5th star state after reset: ${filled}`);
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-submit-reset.png`), fullPage: true });
    });

    test('2b throwaway submit + /space/test-product', async () => {
      await c.page.goto('/space/test-product');
      await expect(c.page.locator('h1')).toBeVisible();
      await audit(c, 'space-alias');
      if (rateLimited) return;
      await c.page.locator('button[title="3 Star"]').click();
      await c.page.fill('#content', throwText);
      await c.page.fill('#name', `Throw ${vp.name}`);
      await c.page.fill('#email', `qa-throw-${vp.name}-${RUN}@example.com`);
      await c.page.locator('#consent').check();
      await c.page.locator('button[type=submit]').click();
      await expect(c.page.locator('[data-testid=confirmation-gif]')).toBeVisible();
      c.issues.splice(0);
    });

    // ---------- 3. Demo dashboard ----------
    test('3a login as demo + dashboard', async () => {
      await login(c, 'demo@example.com', 'password');
      await expect(c.page).toHaveURL(/dashboard/);
      await expect(c.page.locator('body')).toContainText('Total Testimonials');
      await expect(c.page.locator('body')).toContainText('Average Rating');
      await audit(c, 'dashboard');
      const before = await c.page.locator('svg path[d^="M 20"]').evaluateAll((ps) => ps.map((p) => p.getAttribute('d')).join('|'));
      await c.page.locator('[wire\\:click="setDensity(\'week\')"]').click();
      await c.page.waitForLoadState('networkidle');
      await c.page.waitForTimeout(500);
      const wk = await c.page.locator('svg path[d^="M 20"]').evaluateAll((ps) => ps.map((p) => p.getAttribute('d')).join('|'));
      note(`[${vp.name}] dashboard chart changed after weekly toggle: ${before !== wk}`);
      expect(wk, 'weekly toggle should change chart').not.toBe(before);
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-dashboard-weekly.png`), fullPage: true });
      await c.page.locator('[wire\\:click="setDensity(\'day\')"]').click();
      await c.page.waitForLoadState('networkidle');
      const dismiss = c.page.locator('[wire\\:click="dismissBanner"]');
      await expect(dismiss).toBeVisible();
      await dismiss.click();
      await expect(dismiss).toHaveCount(0);
      await c.page.reload();
      note(`[${vp.name}] banner after reload: ${await c.page.locator('[wire\\:click="dismissBanner"]').count() ? 'shown again' : 'stays dismissed'}`);
      c.issues.splice(0);
    });

    test('3b spaces list: toggle active, copy URL', async () => {
      await c.page.goto('/spaces');
      await audit(c, 'spaces');
      const card = c.page.locator('div').filter({ has: c.page.locator('[wire\\:click^="toggleActive"]') }).filter({ hasText: '/s/test-product' }).last();
      const tog = card.locator('[wire\\:click^="toggleActive"]');
      await expect(tog).toHaveText(/Deactivate/);
      await tog.click();
      await expect(tog).toHaveText(/Activate/);
      await expect(tog).not.toHaveText(/Deactivate/);
      // inactive space must not accept public view/submission
      const r = await c.page.request.get('/s/test-product');
      note(`[${vp.name}] inactive test-product public status ${r.status()}`);
      await tog.click();
      await expect(tog).toHaveText(/Deactivate/);
      await card.locator('button[title="Copy public URL"]').click();
      const clip = await c.page.evaluate(() => navigator.clipboard.readText());
      expect(clip).toBe(`${BASE}/s/test-product`);
      c.issues.splice(0);
    });

    test('3c create space at free-plan limit (demo has 3/3)', async () => {
      await c.page.goto('/spaces/create');
      await c.page.waitForLoadState('networkidle');
      await audit(c, 'create-at-limit');
      const url = c.page.url();
      note(`[${vp.name}] demo (3/3 spaces) opening /spaces/create -> ${url}`);
      await c.page.locator('#name').fill('Over Limit').catch(() => {});
      await expect(c.page.locator('body')).toContainText('Free Plan Limit Reached');
      const btn = c.page.locator('button[type=submit]:not([data-test=logout-button])').last();
      const disabled = await btn.isDisabled();
      note(`[${vp.name}] create-space submit button disabled at limit: ${disabled}`);
      expect(disabled, 'create button should be disabled at plan limit').toBe(true);
      c.issues.splice(0);
    });

    test('3d inbox: favorite, approve, edit, hide/show, filters, delete', async () => {
      await c.page.goto('/spaces/test-product/inbox');
      await audit(c, 'inbox');
      if (rateLimited) {
        const txt = await c.page.locator('body').textContent() ?? '';
        const q = txt.match(new RegExp('QA testimonial desktop [a-z0-9]+ great product(?! \\(edited\\))'));
        const t = txt.match(new RegExp('Throwaway desktop [a-z0-9]+ delete me'));
        if (!q) {
          // No pending leftover (rate limit blocks creating new ones): reduced run on the desktop-edited testimonial.
          const e = txt.match(new RegExp('QA testimonial desktop [a-z0-9]+ great product \\(edited\\)'));
          expect(e, 'desktop-edited QA testimonial to reuse').not.toBeNull();
          note(`[${vp.name}] DEGRADED inbox run (rate limited, no fresh testimonial): reusing "${e![0]}"; delete flow not re-run`);
          const dq = c.page.locator('[wire\\:key^="testimonial-"]').filter({ hasText: e![0] });
          await expect(dq).toBeVisible();
          await expect(dq.locator('a[href*="linkedin.com/in/qa-user"]')).toBeVisible();
          const fav = dq.locator('[wire\\:click^="toggleFavorite"]');
          const t0 = await fav.getAttribute('title');
          await fav.click();
          await expect(fav).not.toHaveAttribute('title', t0!);
          await fav.click();
          await expect(fav).toHaveAttribute('title', t0!);
          await dq.locator('button[title="More options"]').click();
          await dq.getByText('Edit Text').click();
          await expect(c.page.locator('textarea[wire\\:model="editContent"]')).toBeVisible();
          await c.page.locator('[wire\\:click="cancelEdit"]').click();
          await expect(c.page.locator('textarea[wire\\:model="editContent"]')).toHaveCount(0);
          for (const tab of ['Pending', 'Approved', 'Favorites', 'Hidden', 'All']) {
            await c.page.locator('button[role=tab]', { hasText: tab }).first().click();
            await expect(c.page.locator('button[role=tab][aria-selected=true]')).toContainText(tab);
          }
          await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-inbox-degraded.png`), fullPage: true });
          qaTextFinal.set(vp.name, e![0]);
          return;
        }
        qaText = q[0]; throwText = t ? t[0] : throwText;
        qaName = 'QA User desktop';
      }
      const card = (t: string) => c.page.locator('[wire\\:key^="testimonial-"]').filter({ hasText: t });
      const qa = card(qaText);
      await expect(qa).toBeVisible();
      await expect(qa).toContainText(qaName);
      await expect(qa).toContainText('QA Corp');
      await expect(qa.locator('a[href*="linkedin.com/in/qa-user"]')).toBeVisible(); // social link shown
      const href = await qa.locator('a[href*="linkedin.com/in/qa-user"]').first().getAttribute('href');
      expect(href).toMatch(/^https:\/\/(www\.)?linkedin\.com\/in\/qa-user/);

      await qa.locator('[wire\\:click^="toggleFavorite"]').click();
      await expect(qa.locator('[wire\\:click^="toggleFavorite"]')).toHaveAttribute('title', /Unpin/);
      await c.page.locator('button[role=tab]', { hasText: 'Favorites' }).click();
      await expect(card(qaText)).toBeVisible();
      await c.page.locator('button[role=tab]', { hasText: 'Pending' }).click();
      await expect(card(qaText)).toBeVisible(); // still pending
      await expect(card(throwText)).toBeVisible();
      await c.page.locator('button[role=tab]', { hasText: 'All' }).first().click();

      await card(qaText).locator('[wire\\:click^="toggleApproval"]').click();
      await expect(card(qaText).locator('[wire\\:click^="toggleApproval"]')).toHaveAttribute('title', /Remove from Wall/);
      await c.page.locator('button[role=tab]', { hasText: 'Approved' }).click();
      await expect(card(qaText)).toBeVisible();
      await expect(card(throwText)).toHaveCount(0);
      await c.page.locator('button[role=tab]', { hasText: 'All' }).first().click();

      // edit text
      const edited = qaText + ' (edited)';
      await card(qaText).locator('button[title="More options"]').click();
      await card(qaText).getByText('Edit Text').click();
      const ta = c.page.locator('textarea[wire\\:model="editContent"]');
      await expect(ta).toBeVisible();
      await ta.fill(edited);
      await c.page.locator('[wire\\:click="saveEdit"]').click();
      await expect(card(edited)).toBeVisible();
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-inbox-edited.png`), fullPage: true });

      // hide / show
      await card(edited).locator('button[title="More options"]').click();
      await card(edited).getByText('Hide from Public').click();
      await expect(card(edited)).toContainText('Hidden');
      await c.page.locator('button[role=tab]', { hasText: 'Hidden' }).click();
      await expect(card(edited)).toBeVisible();
      const wallHidden = await c.page.request.get('/wall/test-product');
      const hiddenOnWall = (await wallHidden.text()).includes(edited);
      note(`[${vp.name}] hidden+approved testimonial visible on public wall: ${hiddenOnWall}`);
      expect.soft(hiddenOnWall, 'hidden testimonial must NOT be on public wall').toBe(false);
      const embedHidden = (await (await c.page.request.get('/embed/wall-of-love/test-product')).text()).includes(edited);
      expect.soft(embedHidden, 'hidden testimonial must NOT be in embed').toBe(false);
      await card(edited).locator('button[title="More options"]').click();
      await card(edited).getByText('Show Publicly').click();
      await c.page.locator('button[role=tab]', { hasText: 'All' }).first().click();
      await expect(card(edited)).not.toContainText('Hidden');

      // delete throwaway (cancel confirm first, then accept)
      const delBtn = () => card(throwText).getByText('Delete', { exact: true });
      const openMenu = async () => { await card(throwText).locator('button[title="More options"]').click(); await expect(delBtn()).toBeVisible(); };
      c.page.once('dialog', (d) => d.dismiss());
      await openMenu();
      await delBtn().click();
      await c.page.waitForTimeout(800);
      await expect(card(throwText)).toBeVisible();
      c.page.once('dialog', (d) => d.accept());
      await c.page.waitForTimeout(300);
      if (!(await delBtn().isVisible())) await openMenu();
      await delBtn().click();
      await expect(card(throwText)).toHaveCount(0);
      c.issues.splice(0);
      qaTextFinal.set(vp.name, edited);
    });

    // ---------- 4. Embeds ----------
    test('4 embed generator', async () => {
      await c.page.goto('/embeds');
      await audit(c, 'embeds');
      const _unused = () => c.page.locator('[style*="background-color"]').filter({ has: c.page.locator('[wire\\:key^="preview-card-"]') }).first();
      await c.page.selectOption('#space-select', await c.page.locator('#space-select option', { hasText: 'saas-product-alpha' }).getAttribute('value') as string);
      await expect(c.page.locator('textarea').first()).toHaveValue(/saas-product-alpha/);
      await expect(c.page.locator('[wire\\:key^="preview-card-"]').first()).toBeVisible();
      const cardsAlpha = await c.page.locator('[wire\\:key^="preview-card-"]').count();
      expect(cardsAlpha).toBeGreaterThan(0);

      await c.page.locator('[wire\\:click="setLayout(\'carousel\')"]').click();
      await expect(c.page.locator('[x-ref=previewCarousel]')).toBeVisible();
      await expect(c.page.locator('textarea').first()).toHaveValue(/layout=carousel/);
      await c.page.locator('[wire\\:click="setLayout(\'masonry\')"]').click();
      await expect(c.page.locator('[x-ref=previewCarousel]')).toHaveCount(0);

      await c.page.locator('[wire\\:click="setTheme(\'dark\')"]').click();
      await expect(c.page.locator('textarea').first()).toHaveValue(/theme=dark/);
      await c.page.locator('[wire\\:click="setTheme(\'light\')"]').click();
      await expect(c.page.locator('textarea').first()).toHaveValue(/theme=light/);

      const bgOf = () => c.page.locator('div.min-h-\\[380px\\]').first().evaluate((e) => getComputedStyle(e).backgroundColor);
      await c.page.locator('[wire\\:click="setBackgroundColor(\'#1e1b4b\')"]').click();
      await expect.poll(bgOf).toBe('rgb(30, 27, 75)');
      await c.page.locator('[wire\\:click="setBackgroundColor(\'#f8fafc\')"]').click();
      await expect.poll(bgOf).toBe('rgb(248, 250, 252)');
      await c.page.locator('[wire\\:click="setBackgroundColor(\'#ffffff\')"]').click();

      const sw = c.page.locator('[wire\\:click="toggleAnimations"]');
      const a0 = await sw.getAttribute('aria-checked');
      await sw.click();
      await expect(sw).not.toHaveAttribute('aria-checked', a0!);
      await expect(c.page.locator('textarea').first()).toHaveValue(new RegExp(`animations=${a0 === 'true' ? 0 : 1}`));

      // copy buttons
      await c.page.getByText('Copy Code').first().click();
      const clip1 = await c.page.evaluate(() => navigator.clipboard.readText());
      expect(clip1).toContain('<iframe');
      expect(clip1).toContain('/embed/wall-of-love/saas-product-alpha');
      await expect(c.page.getByText('Copied!').first()).toBeVisible();
      await c.page.getByRole('button', { name: /script/i }).first().click();
      await c.page.locator('button', { hasText: 'Copy Code' }).filter({ visible: true }).first().click();
      const clip2 = await c.page.evaluate(() => navigator.clipboard.readText());
      expect(clip2).toContain('embed.js');
      expect(clip2).toContain('data-space="saas-product-alpha"');
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-embeds-final.png`), fullPage: true });

      // links
      const [wallPage] = await Promise.all([c.context.waitForEvent('page'), c.page.getByText('Public Wall Page').click()]);
      await wallPage.waitForLoadState('networkidle');
      expect(wallPage.url()).toContain('/wall/saas-product-alpha');
      await expect(wallPage.locator('body')).not.toContainText(/not found/i);
      await wallPage.close();
      const [standalone] = await Promise.all([c.context.waitForEvent('page'), c.page.getByText('Open Standalone').click()]);
      await standalone.waitForLoadState('networkidle');
      expect(standalone.url()).toContain('/embed/wall-of-love/saas-product-alpha');
      expect(standalone.url()).toContain('animations=');
      await standalone.close();
      c.issues.splice(0);
    });

    // ---------- 5. Wall + embed ----------
    test('5 wall page and embed endpoint', async () => {
      const edited = qaTextFinal.get(vp.name)!;
      await c.page.goto('/wall/test-product');
      await expect(c.page.locator('body')).toContainText(edited);
      await expect(c.page.locator('body')).not.toContainText(throwText);
      await audit(c, 'wall');
      await c.page.goto('/embed/wall-of-love/test-product?layout=carousel&theme=dark');
      await expect(c.page.locator('body')).toContainText(edited);
      await audit(c, 'embed-carousel-dark', { links: false });
      const r = await c.page.request.get(`/embed/wall-of-love/test-product?bg=${encodeURIComponent('red;}</style><script>alert(1)</script>')}`);
      expect(await r.text()).not.toContain('<script>alert(1)');
      c.issues.splice(0);
    });

    test('6 embed.js script auto-resizes iframe', async () => {
      const html = `<!doctype html><html><body><h1>host</h1><div id="wall-of-love" data-space="saas-product-alpha"></div><script src="${BASE}/embed.js" defer></script></body></html>`;
      fs.writeFileSync(path.join(SCRATCH, 'embed-host.html'), html);
      const host = await c.context.newPage();
      const errs: string[] = [];
      host.on('console', (m) => m.type() === 'error' && errs.push(m.text()));
      host.on('pageerror', (e) => errs.push(e.message));
      await host.goto('file:///' + path.join(SCRATCH, 'embed-host.html').replace(/\\/g, '/'));
      const iframe = host.locator('#wall-of-love iframe');
      await expect(iframe).toBeVisible();
      await expect(iframe).toHaveAttribute('src', /embed\/wall-of-love\/saas-product-alpha/);
      const h0 = (await iframe.boundingBox())!.height;
      note(`[${vp.name}] embed iframe initial height ~${h0}`);
      await expect.poll(async () => Math.round((await iframe.boundingBox())!.height), { timeout: 10000 }).not.toBe(650);
      const h1 = (await iframe.boundingBox())!.height;
      note(`[${vp.name}] embed iframe final height ${h1}`);
      const frame = host.frameLocator('#wall-of-love iframe');
      await expect(frame.locator('body')).toContainText(/.+/);
      await host.screenshot({ path: path.join(SHOTS, `${vp.name}-embed-script.png`), fullPage: true });
      expect.soft(errs.filter((e) => !/file:|CORS|ERR_/.test(e)), 'embed host console errors').toEqual([]);
      if (errs.length) note(`[${vp.name}] embed host console errors: ${errs.join(' || ')}`);
      await host.close();
    });

    // ---------- 7. Settings ----------
    test('7 settings pages, billing upgrade, dark mode', async () => {
      await c.page.goto('/settings/profile');
      await expect(c.page.locator('input[name=email], input[wire\\:model="email"]').first()).toBeVisible();
      await audit(c, 'settings-profile');
      await c.page.goto('/settings/billing');
      await audit(c, 'settings-billing');
      await c.page.getByRole('button', { name: /Upgrade to Pro/ }).first().click();
      // Without Stripe keys the upgrade opens the demo checkout (no payment made here).
      await expect(c.page).toHaveURL(/settings\/billing\/demo-checkout/);
      await expect(c.page.locator('body')).toContainText(/Demo checkout/i);
      await c.page.screenshot({ path: path.join(SHOTS, `${vp.name}-billing-demo-checkout.png`), fullPage: true });
      await c.page.goto('/settings/billing');
      c.issues.splice(0);
      await c.page.goto('/settings/appearance');
      await audit(c, 'settings-appearance');
      await c.page.getByText('Dark', { exact: true }).click();
      await expect(c.page.locator('html')).toHaveClass(/dark/);
      await c.page.goto('/dashboard');
      await expect(c.page.locator('html')).toHaveClass(/dark/);
      await audit(c, 'dashboard-dark');
      await c.page.goto('/spaces/test-product/inbox');
      await audit(c, 'inbox-dark', { links: false });
      await c.page.goto('/embeds');
      await audit(c, 'embeds-dark', { links: false });
      await c.page.goto('/settings/appearance');
      await c.page.getByText('Light', { exact: true }).click();
      await c.page.goto('/settings/security');
      await c.page.waitForLoadState('networkidle');
      if (/confirm-password/.test(c.page.url())) {
        note(`[${vp.name}] /settings/security requires password confirmation first (expected Fortify behaviour)`);
        await audit(c, 'confirm-password', { links: false });
        await c.page.fill('input[type=password]', 'password');
        await c.page.getByRole('button', { name: 'Confirm', exact: true }).click();
        await c.page.waitForLoadState('networkidle');
      }
      await expect(c.page).toHaveURL(/settings[/]security/);
      await audit(c, 'settings-security');
      await logout(c);
      await expect(c.page).toHaveURL(/\/$|login/);
      await c.page.goto('/dashboard');
      await expect(c.page).toHaveURL(/login/);
      c.issues.splice(0);
    });

    // ---------- 8. New user: create space, edit, 403 ----------
    test('8a new user: create space, success page, edit, slug change', async () => {
      test.skip(!verified, 'new user not verified');
      await login(c, newEmail, newPass);
      await expect(c.page).toHaveURL(/dashboard/);
      await audit(c, 'newuser-dashboard');
      await c.page.goto('/spaces');
      await audit(c, 'newuser-spaces');
      await expect(c.page.locator('body')).not.toContainText('Test Product');
      await expect(c.page.locator('body')).not.toContainText('saas-product-alpha');
      await c.page.goto('/spaces/create');
      await audit(c, 'create-form');
      await c.page.fill('#name', `QA Space ${vp.name} ${RUN}`);
      await expect(c.page.locator('#slug')).toHaveValue(/qa-space/);
      await c.page.waitForTimeout(800);
      await c.page.fill('#slug', mySlug);
      await c.page.fill('#title', 'QA Title');
      await c.page.locator('button[type=submit]:not([data-test=logout-button])').last().click();
      await expect(c.page).toHaveURL(new RegExp(`/spaces/${mySlug}/success`));
      await expect(c.page.locator('#shareableUrl')).toHaveValue(`${BASE}/s/${mySlug}`);
      await audit(c, 'space-success', { links: false });
      const [pub] = await Promise.all([c.context.waitForEvent('page'), c.page.getByText('Visit Page').click()]);
      await pub.waitForLoadState('networkidle');
      expect(pub.url()).toContain(`/s/${mySlug}`);
      await expect(pub.locator('h1')).toContainText('QA Title');
      await pub.close();

      await c.page.goto(`/spaces/${mySlug}/edit`);
      await audit(c, 'edit');
      await c.page.fill('#title', 'QA Title Changed');
      await c.page.locator('button[type=submit]:not([data-test=logout-button])').last().click();
      await expect(c.page.locator('body')).toContainText(/updated successfully/i);
      await c.page.goto(`/s/${mySlug}`);
      await expect(c.page.locator('h1')).toContainText('QA Title Changed');

      await c.page.goto(`/spaces/${mySlug}/edit`);
      await c.page.fill('[wire\\:model="slug"]', myNewSlug);
      await c.page.locator('button[type=submit]:not([data-test=logout-button])').last().click();
      await expect(c.page).toHaveURL(new RegExp(`/spaces/${myNewSlug}/edit`));
      await expect(c.page.locator('[wire\\:model="slug"]')).toHaveValue(myNewSlug);
      expect((await c.page.request.get(`/s/${mySlug}`)).status()).toBe(404);
      expect((await c.page.request.get(`/s/${myNewSlug}`)).status()).toBe(200);
      // saving again after slug change must still work
      await c.page.fill('#title', 'After rename');
      await c.page.locator('button[type=submit]:not([data-test=logout-button])').last().click();
      await expect(c.page.locator('body')).toContainText(/updated successfully/i);
      c.issues.splice(0);
    });

    test('8b new user cannot access demo inbox/edit (403)', async () => {
      test.skip(!verified, 'new user not verified');
      for (const p of ['/spaces/test-product/inbox', '/spaces/test-product/edit', '/spaces/test-product/success']) {
        const r = await c.page.goto(p);
        expect(r?.status(), p).toBe(403);
      }
      c.issues.splice(0);
      await audit(c, '403', { links: false });
      await c.page.goto('/spaces');
      await expect(c.page.locator('body')).not.toContainText('test-product');
      await logout(c);
      c.issues.splice(0);
    });

    test('9 embed generator hex text field updates live preview and snippet', async () => {
      await login(c, 'demo@example.com', 'password');
      await c.page.goto('/embeds');
      await c.page.waitForLoadState('networkidle');
      const bgOf = () => c.page.locator('.overflow-y-auto[style*="background-color"]').first().evaluate((e) => getComputedStyle(e).backgroundColor);
      const hex = c.page.locator('[aria-label="Background color hex value"]');
      await hex.fill('#ff0000');
      await hex.blur();
      await expect.configure({ timeout: 4000 }).poll(bgOf, 'typing #ff0000 in hex field should update live preview').toBe('rgb(255, 0, 0)');
      await expect(c.page.locator('textarea').first(), 'hex field value should reach the embed snippet').toHaveValue(/bg=%23ff0000/i, { timeout: 3000 });
      await hex.fill('not-a-color');
      await hex.blur();
      await c.page.waitForLoadState('networkidle');
      const afterBad = await bgOf();
      note(`[${vp.name}] hex 'not-a-color' -> preview bg ${afterBad}; error shown: ${/invalid|valid/i.test(await c.page.locator('body').innerText())}`);
      c.issues.splice(0);
    });
  });
}

const qaTextFinal = new Map<string, string>();
