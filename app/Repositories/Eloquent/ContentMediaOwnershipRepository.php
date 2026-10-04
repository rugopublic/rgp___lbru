<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Models\Course;
use App\Models\Product;
use App\Models\ReferenceItem;
use App\Models\Recipe;
use App\Models\Course\Teacher;
use App\Repositories\Contracts\ContentMediaOwnershipRepositoryInterface;

final class ContentMediaOwnershipRepository implements ContentMediaOwnershipRepositoryInterface
{

    /**
     * Queries or persists expected folders for the content media ownership repository repository.
     * @return iterable
     */
    public function expectedFolders(): iterable
    {
        foreach ([
            [Article::class, 'articles'],
            [Product::class, 'products'],
            [ReferenceItem::class, 'reference'],
            [Recipe::class, 'recipes'],
            [Course::class, 'courses'],
            [Teacher::class, 'teachers'],
        ] as [$model, $prefix]) {
            foreach ($model::withTrashed()->select('public_id')->orderBy('id')->cursor() as $entity) {
                if ($entity->public_id !== null && $entity->public_id !== '') {
                    yield $prefix.'/'.$entity->public_id;
                }
            }
        }
    }
}
