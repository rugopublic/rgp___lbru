<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\CourseEnrollment;
use Illuminate\Database\Eloquent\Model;

interface CourseEnrollmentRepositoryInterface
{

    /**
     * Queries or persists upsert for payment for the course enrollment repository interface repository.
     *
     * @param int $userId
     * @param int $courseId
     * @param int $transactionId
     * @param string $amount
     * @return Model
     */
    public function upsertForPayment(int $userId, int $courseId, int $transactionId, string $amount): Model;

    /**
     * Queries active for user and returns the matching persistence model.
     *
     * @param int $userId
     * @param int $courseId
     * @return ?CourseEnrollment
     */
    public function findActiveForUser(int $userId, int $courseId): ?CourseEnrollment;
}
