<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Enums\UserPlan;
use App\Livewire\Spaces\Inbox;
use App\Models\Space;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    // ─── Authorization ─────────────────────────────────────────────────────────

    public function test_guest_cannot_access_inbox(): void
    {
        $space = Space::factory()->create();

        $this->get(route('spaces.inbox', $space->slug))
            ->assertRedirect(route('login'));
    }

    public function test_owner_can_access_inbox(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('spaces.inbox', $space->slug))
            ->assertOk();
    }

    public function test_other_user_cannot_access_inbox(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->get(route('spaces.inbox', $space->slug))
            ->assertForbidden();
    }

    // ─── Listing ───────────────────────────────────────────────────────────────

    public function test_inbox_lists_testimonials_for_the_space(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t1    = Testimonial::factory()->create(['space_id' => $space->id, 'submitter_name' => 'Alice']);
        $t2    = Testimonial::factory()->create(['space_id' => $space->id, 'submitter_name' => 'Bob']);

        $other = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->create(['space_id' => $other->id, 'submitter_name' => 'Charlie Stealth']);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSee('Alice')
            ->assertSee('Bob')
            ->assertDontSee('Charlie Stealth');
    }

    public function test_inbox_shows_star_rating_when_space_has_ratings_enabled(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id, 'enable_ratings' => true]);
        Testimonial::factory()->create(['space_id' => $space->id, 'rating' => 4]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSee('4/5');
    }

    public function test_inbox_does_not_show_rating_when_space_has_ratings_disabled(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id, 'enable_ratings' => false]);
        Testimonial::factory()->create(['space_id' => $space->id, 'rating' => 3]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertDontSee('3/5');
    }

    // ─── Favorite toggle ───────────────────────────────────────────────────────

    public function test_owner_can_favorite_a_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id, 'is_featured' => false]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleFavorite', $t->id);

        $this->assertTrue($t->fresh()->is_featured);
    }

    public function test_owner_can_unfavorite_a_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id, 'is_featured' => true]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleFavorite', $t->id);

        $this->assertFalse($t->fresh()->is_featured);
    }

    public function test_favorite_toggle_on_other_spaces_testimonial_returns_404(): void
    {
        $user    = User::factory()->create();
        $mySpace = Space::factory()->create(['user_id' => $user->id]);
        $other   = Space::factory()->create();
        $t       = Testimonial::factory()->create(['space_id' => $other->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $mySpace])
            ->call('toggleFavorite', $t->id)
            ->assertStatus(404);
    }

    // ─── Wall of Love (Approval) toggle ────────────────────────────────────────

    public function test_owner_can_approve_a_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create([
            'space_id' => $space->id,
            'status'   => TestimonialStatus::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleApproval', $t->id);

        $this->assertSame(TestimonialStatus::Approved, $t->fresh()->status);
    }

    public function test_owner_can_unapprove_a_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create([
            'space_id' => $space->id,
            'status'   => TestimonialStatus::Approved,
        ]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleApproval', $t->id);

        $this->assertSame(TestimonialStatus::Pending, $t->fresh()->status);
    }

    // ─── Edit text ─────────────────────────────────────────────────────────────

    public function test_owner_can_edit_testimonial_content(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create([
            'space_id' => $space->id,
            'content'  => 'Original content here.',
        ]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('startEdit', $t->id)
            ->assertSet('editingId', $t->id)
            ->set('editContent', 'Updated testimonial content for testing.')
            ->call('saveEdit')
            ->assertSet('editingId', null)
            ->assertHasNoErrors();

        $this->assertSame('Updated testimonial content for testing.', $t->fresh()->content);
    }

    public function test_edit_requires_minimum_content_length(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('startEdit', $t->id)
            ->set('editContent', 'Hi')
            ->call('saveEdit')
            ->assertHasErrors(['editContent']);
    }

    public function test_owner_can_cancel_edit(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('startEdit', $t->id)
            ->call('cancelEdit')
            ->assertSet('editingId', null);
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    public function test_owner_can_delete_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('deleteTestimonial', $t->id);

        $this->assertDatabaseMissing('testimonials', ['id' => $t->id]);
    }

    public function test_delete_on_other_spaces_testimonial_returns_404(): void
    {
        $user    = User::factory()->create();
        $mySpace = Space::factory()->create(['user_id' => $user->id]);
        $other   = Space::factory()->create();
        $t       = Testimonial::factory()->create(['space_id' => $other->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $mySpace])
            ->call('deleteTestimonial', $t->id)
            ->assertStatus(404);

        $this->assertDatabaseHas('testimonials', ['id' => $t->id]);
    }

    // ─── Hide from public ──────────────────────────────────────────────────────

    public function test_owner_can_hide_testimonial_from_public(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id, 'is_hidden' => false]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleHidden', $t->id);

        $this->assertTrue($t->fresh()->is_hidden);
    }

    public function test_owner_can_unhide_testimonial(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $t     = Testimonial::factory()->create(['space_id' => $space->id, 'is_hidden' => true]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('toggleHidden', $t->id);

        $this->assertFalse($t->fresh()->is_hidden);
    }

    // ─── Filters ───────────────────────────────────────────────────────────────

    public function test_inbox_filter_pending_shows_only_pending(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        $p     = Testimonial::factory()->create(['space_id' => $space->id, 'status' => TestimonialStatus::Pending, 'submitter_name' => 'PendingPerson']);
        $a     = Testimonial::factory()->create(['space_id' => $space->id, 'status' => TestimonialStatus::Approved, 'submitter_name' => 'ApprovedPerson']);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('setFilter', 'pending')
            ->assertSee('PendingPerson')
            ->assertDontSee('ApprovedPerson');
    }

    public function test_inbox_filter_approved_shows_only_approved(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->create(['space_id' => $space->id, 'status' => TestimonialStatus::Pending, 'submitter_name' => 'PendingPerson']);
        Testimonial::factory()->create(['space_id' => $space->id, 'status' => TestimonialStatus::Approved, 'submitter_name' => 'ApprovedPerson']);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('setFilter', 'approved')
            ->assertSee('ApprovedPerson')
            ->assertDontSee('PendingPerson');
    }

    public function test_inbox_filter_featured_shows_only_favorites(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->create(['space_id' => $space->id, 'is_featured' => true, 'submitter_name' => 'FavoritePerson']);
        Testimonial::factory()->create(['space_id' => $space->id, 'is_featured' => false, 'submitter_name' => 'RegularPerson']);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('setFilter', 'featured')
            ->assertSee('FavoritePerson')
            ->assertDontSee('RegularPerson');
    }

    public function test_inbox_filter_hidden_shows_only_hidden(): void
    {
        $user  = User::factory()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->create(['space_id' => $space->id, 'is_hidden' => true, 'submitter_name' => 'HiddenPerson']);
        Testimonial::factory()->create(['space_id' => $space->id, 'is_hidden' => false, 'submitter_name' => 'VisiblePerson']);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->call('setFilter', 'hidden')
            ->assertSee('HiddenPerson')
            ->assertDontSee('VisiblePerson');
    }

    // ─── Collection cap enforcement ────────────────────────────────────────────

    public function test_inbox_shows_cap_indicator_for_free_plan(): void
    {
        $user  = User::factory()->create(['plan' => UserPlan::Free]);
        $space = Space::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSee('/ 100 collected');
    }

    public function test_inbox_shows_cap_indicator_for_pro_plan(): void
    {
        $user  = User::factory()->pro()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSee('/ 1000 collected');
    }

    public function test_inbox_shows_cap_reached_warning_when_at_limit_free(): void
    {
        $user  = User::factory()->create(['plan' => UserPlan::Free]);
        $space = Space::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        Testimonial::factory()->count(100)->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSet('isAtCap', true)
            ->assertSee('Cap reached');
    }

    public function test_inbox_cap_is_not_reached_before_limit(): void
    {
        $user  = User::factory()->create(['plan' => UserPlan::Free]);
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->count(50)->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSet('isAtCap', false);
    }

    public function test_pro_plan_cap_is_1000_testimonials_per_space(): void
    {
        $user  = User::factory()->pro()->create();
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->count(1000)->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSet('isAtCap', true)
            ->assertSet('collectionCap', 1000);
    }

    public function test_free_plan_cap_is_100_testimonials_per_space(): void
    {
        $user  = User::factory()->create(['plan' => UserPlan::Free]);
        $space = Space::factory()->create(['user_id' => $user->id]);
        Testimonial::factory()->count(100)->create(['space_id' => $space->id]);

        $this->actingAs($user);

        Livewire::test(Inbox::class, ['space' => $space])
            ->assertSet('isAtCap', true)
            ->assertSet('collectionCap', 100);
    }
}
