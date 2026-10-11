<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('preserves access for an enrolled learner when a course is soft-deleted', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $course = Course::query()->create([
        'title' => 'Previously purchased course',
        'price' => '49.00',
        'publish_status' => 'published',
    ]);

    CourseEnrollment::query()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
        'amount_paid' => '49.00',
    ]);

    $course->delete();

    $this->actingAs($user)
        ->get(route('courses.learn', $course->public_id))
        ->assertOk()
        ->assertSee('Previously purchased course');
});

it('preserves access for an enrolled learner when a course is unpublished', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $course = Course::query()->create([
        'title' => 'Unpublished but purchased course',
        'price' => '29.00',
        'publish_status' => 'draft',
    ]);

    CourseEnrollment::query()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
        'amount_paid' => '29.00',
    ]);

    $this->actingAs($user)
        ->get(route('courses.learn', $course->public_id))
        ->assertOk()
        ->assertSee('Unpublished but purchased course');
});

it('does not let a user without enrollment access a soft-deleted course', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $course = Course::query()->create([
        'title' => 'Not purchased course',
        'price' => '39.00',
        'publish_status' => 'published',
    ]);
    $course->delete();

    $this->actingAs($user)
        ->get(route('courses.learn', $course->public_id))
        ->assertForbidden();
});
