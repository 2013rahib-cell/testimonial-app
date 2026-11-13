/**
 * Standalone Playwright test runner script for End-to-End Space Submission.
 * Can be executed via: node e2e/run-test.cjs
 */
const { chromium } = require('@playwright/test');

(async () => {
  const baseURL = process.env.BASE_URL || 'http://127.0.0.1:8000';
  const targetUrl = `${baseURL}/space/test-product`;
  console.log(`Starting E2E Playwright test against: ${targetUrl}`);

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Step 1: Navigate to the public space URL
    console.log(`Navigating to ${targetUrl}...`);
    const response = await page.goto(targetUrl, { waitUntil: 'networkidle', timeout: 30000 });
    if (!response || response.status() !== 200) {
      throw new Error(`Failed to load page. HTTP status: ${response ? response.status() : 'null'}`);
    }
    console.log('Page loaded successfully (HTTP 200).');

    // Step 2: Validate displayed elements
    console.log('Verifying Title, Subtitle, Ask, and Form fields...');
    const heading = await page.locator('h1').innerText();
    console.log(`Found Title: "${heading}"`);
    if (!heading.includes('Testimonial Showcase')) {
      throw new Error(`Expected title to contain 'Testimonial Showcase', but got: "${heading}"`);
    }

    const pageContent = await page.content();
    if (!pageContent.includes('We love hearing from you')) {
      throw new Error('Subtitle "We love hearing from you" not found on page.');
    }
    if (!pageContent.includes('What did you like best?')) {
      throw new Error('Ask "What did you like best?" not found on page.');
    }
    console.log('Title, Subtitle, and Ask verified.');

    // Step 3: Fill out the form
    console.log('Selecting 5-star rating...');
    await page.locator('button[title="5 Star"]').click();

    console.log('Filling in testimonial content...');
    await page.locator('textarea#content').fill('This platform is exceptionally fast, intuitive, and made sharing feedback completely effortless!');

    console.log('Filling in submitter details...');
    await page.locator('input#name').fill('Jane Doe');
    await page.locator('input#email').fill('jane.doe@example.com');
    await page.locator('input#company').fill('Acme Corp');
    await page.locator('input#role').fill('Director of Product');

    // Ensure consent is checked
    const consent = page.locator('input#consent');
    if (!(await consent.isChecked())) {
      await consent.check();
    }
    console.log('Form filled successfully.');

    // Step 4: Submit form
    console.log('Submitting form...');
    await page.locator('button[type="submit"]').click();

    // Step 5: Verify confirmation screen & GIF
    console.log('Waiting for confirmation / thank you screen...');
    await page.waitForSelector('text=Thank You!', { timeout: 15000 });
    console.log('Confirmation screen displayed.');

    const gif = page.locator('img[data-testid="confirmation-gif"]');
    await gif.waitFor({ state: 'visible', timeout: 5000 });
    const gifSrc = await gif.getAttribute('src');
    console.log(`Confirmation GIF verified! Source: ${gifSrc}`);

    console.log('All E2E checks passed successfully!');
    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error('E2E Test Failed:', err.message);
    await browser.close();
    process.exit(1);
  }
})();
