<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class CourseLearnController extends Controller
{

    /**
     * Initializes the course learn controller instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $courses
     * @param CourseEnrollmentRepositoryInterface $enrollments
     * @return void
     */
    public function __construct(private readonly CourseRepositoryInterface $courses, private readonly CourseEnrollmentRepositoryInterface $enrollments) {}

    /**
     * Builds the detail response for the requested resource.
     *
     * @param string $course
     * @return View
     */
    public function show(string $course): View
    {
        $item = $this->courses->findForAccess($course);
        abort_unless($item, 404);

        $enrollment = $this->enrollments->findActiveForUser((int) Auth::id(), (int) $item->id);
        abort_unless($enrollment, 403);

        return view('frontend.course.learn', [
            'course' => $item,
            'enrollment' => $enrollment,
        ]);
    }
}
