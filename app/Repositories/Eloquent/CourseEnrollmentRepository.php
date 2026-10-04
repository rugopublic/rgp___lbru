<?php
declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\CourseEnrollment;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
final class CourseEnrollmentRepository extends Repository implements CourseEnrollmentRepositoryInterface
{

    /**
     * Queries active for user and returns the matching persistence model.
     *
     * @param int $userId
     * @param int $courseId
     * @return ?CourseEnrollment
     */
    public function findActiveForUser(int $userId, int $courseId): ?CourseEnrollment
    {
        return CourseEnrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('access_expires_at')->orWhere('access_expires_at', '>', now()))
            ->first();
    }

    /**
     * Queries or persists upsert for payment for the course enrollment repository repository.
     *
     * @param int $userId
     * @param int $courseId
     * @param int $transactionId
     * @param string $amount
     * @return Model
     */
    public function upsertForPayment(int $userId, int $courseId, int $transactionId, string $amount): Model
    {
        return CourseEnrollment::query()->updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['transaction_id' => $transactionId, 'amount_paid' => $amount, 'status' => 'active', 'access_expires_at' => null],
        );
    }
}
