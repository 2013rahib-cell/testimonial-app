import { test, expect } from '@playwright/test';

test.describe('Customer-Facing Public Space Submission', () => {
  const spaceSlug = 'test-product';

  test('loads public space and displays title, subtitle, ask, and form fields without authentication', async ({ page }) => {
    // Navigate directly to public space URL without authentication
    await page.goto(`/space/${spaceSlug}`);

    // Verify Space Title
    const title = page.locator('h1');
    await expect(title).toBeVisible();
    await expect(title).toContainText('Testimonial Showcase');

    // Verify Subtitle
    await expect(page.getByText('We love hearing from you')).toBeVisible();

    // Verify Ask / Questions
    await expect(page.getByText('What did you like best?')).toBeVisible();

    // Verify configured form fields are rendered
    await expect(page.getByText('Your Rating')).toBeVisible();
    await expect(page.locator('button[title="5 Star"]')).toBeVisible();
    await expect(page.locator('textarea#content')).toBeVisible();
    await expect(page.locator('input#name')).toBeVisible();
    await expect(page.locator('input#email')).toBeVisible();
    await expect(page.locator('input#company')).toBeVisible();
    await expect(page.locator('input#role')).toBeVisible();

    // Verify social sharing consent checkbox
    const consentCheckbox = page.locator('input#consent');
    await expect(consentCheckbox).toBeVisible();
    await expect(page.getByText('I give permission to use this testimonial')).toBeVisible();

    // Verify submit button
    const submitButton = page.locator('button[type="submit"]');
    await expect(submitButton).toBeVisible();
  });

  test('successfully submits dummy testimonial and displays confirmation screen with GIF', async ({ page }) => {
    await page.goto(`/space/${spaceSlug}`);

    // Select 5-star rating
    const fiveStarButton = page.locator('button[title="5 Star"]');
    await fiveStarButton.click();

    // Fill in testimonial content
    await page.locator('textarea#content').fill('This platform is exceptionally fast, intuitive, and made sharing feedback completely effortless!');

    // Fill in submitter info
    await page.locator('input#name').fill('Jane Doe');
    await page.locator('input#email').fill('jane.doe@example.com');
    await page.locator('input#company').fill('Acme Corp');
    await page.locator('input#role').fill('Director of Product');

    // Ensure consent checkbox is checked
    const consentCheckbox = page.locator('input#consent');
    if (!(await consentCheckbox.isChecked())) {
      await consentCheckbox.check();
    }

    // Submit the testimonial
    const submitBtn = page.locator('button[type="submit"]');
    await submitBtn.click();

    // Verify Success Screen loads
    await expect(page.getByRole('heading', { name: 'Thank You!' })).toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Your testimonial for')).toBeVisible();

    // Confirm that the confirmation GIF/meme is visible
    const gifByTestId = page.locator('img[data-testid="confirmation-gif"]');
    await expect(gifByTestId).toBeVisible();

    const gifByAlt = page.locator('img[alt="Celebration GIF"]');
    await expect(gifByAlt).toBeVisible();

    // Verify that the GIF source is valid
    const src = await gifByTestId.getAttribute('src');
    expect(src).toBeTruthy();
    expect(src).toContain('thank-you.gif');

    // Verify "Submit another response" button is available
    await expect(page.getByRole('button', { name: 'Submit another response' })).toBeVisible();
  });
});
