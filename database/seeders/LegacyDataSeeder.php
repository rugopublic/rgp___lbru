<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\IngredientCategory;
use App\Models\Ingredient;
use App\Models\IngredientMeasure;
use App\Models\IngredientVariety;
use App\Models\IngredientFile;
use App\Models\RecipeCategory;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\RecipeFile;
use App\Models\RecipeIngredient;
use App\Models\ArticleCategory;
use App\Models\Article;

// Вспомогательные функции из старого проекта
if (!function_exists('place_range')) {

    /**
     * Handles place range for the legacy data seeder.
     *
     * @param int $number
     * @param int $place
     * @param bool $leading
     * @return string
     */
    function place_range(int $number, int $place, bool $leading = false): string
    {
        $power_of_ten = 10 ** ($place - 1);
        $place_number = $leading ? floor($number / $power_of_ten) : ($number / $power_of_ten) % 10;
        $lower_bound  = $place_number * $power_of_ten;
        $upper_bound  = $lower_bound + $power_of_ten - 1;
        return $lower_bound == $upper_bound ? "$lower_bound" : "$lower_bound-$upper_bound";
    }
}

if (!function_exists('id_to_path')) {

    /**
     * Handles id to path for the legacy data seeder.
     *
     * @param int|null $id
     * @return string|null
     */
    function id_to_path(int|null $id): string|null
    {
        if (is_null($id)) return null;
        $ranges    = [];
        $max_depth = 5;
        $depth     = $max_depth + 1;
        do {
            $ranges[] = place_range($id, $depth, $depth === $max_depth + 1);
        } while (--$depth > 0);
        return implode("/", $ranges);
    }
}

class LegacyDataSeeder extends Seeder
{
    protected string $legacyFilesRoot = '';

    /**
     * Seeds the configured database records for this environment.
     * @return void
     */
    public function run(): void
    {
        // Определяем корень для поиска старых файлов
        $this->legacyFilesRoot = storage_path('app/public/files');
        if (!is_dir($this->legacyFilesRoot)) {
            $this->legacyFilesRoot = public_path('files');
        }
        if (!is_dir($this->legacyFilesRoot)) {
            $this->command->warn('Папка со старыми файлами не найдена. Изображения будут пропущены.');
        }

        // Очистка целевых таблиц
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Pivot таблицы
        DB::table('article_article_category')->truncate();
        // остальные pivot можно не трогать, но для чистоты
        // DB::table('recipe_ingredient')? нет такой отдельной

        RecipeIngredient::truncate();
        RecipeStep::truncate();
        RecipeFile::truncate();
        Recipe::truncate();
        RecipeCategory::truncate();
        IngredientMeasure::truncate();
        IngredientVariety::truncate();
        IngredientFile::truncate();
        Ingredient::truncate();
        IngredientCategory::truncate();

        Article::truncate();
        ArticleCategory::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $old = DB::connection('mysql_old');

        // 1. Категории (для ингредиентов, рецептов и статей)
        $categories = $old->table('categories')->get();
        foreach ($categories as $cat) {
            $data = [
                'id'          => $cat->id,
                'name'        => $cat->title,
                'slug'        => $cat->slug,
                'description' => $cat->summary,
                'parent_id'   => $cat->parent_id,
                'image'       => null,
                'is_active'   => true,
            ];
            IngredientCategory::create($data);
            RecipeCategory::create($data);
            ArticleCategory::create($data);
        }

        // 2. Продукты → ингредиенты + маппинг
        $products = $old->table('products')->get();
        $productMap = [];

        foreach ($products as $prod) {
            $status = $prod->hidden ? 'draft' : 'published';
            $mainImage = $this->importLegacyCover('products', $prod->id, $prod->cover, 'ingredients/images');

            $ingredient = Ingredient::create([
                'name'         => $prod->title,
                'slug'         => $prod->slug,
                'category_id'  => $prod->category_id,
                'description'  => $prod->summary,
                'content'      => $prod->content,
                'main_image'   => $mainImage,
                'calories'     => 0,
                'protein'      => $prod->proteins ?? 0,
                'fat'          => $prod->fats ?? 0,
                'carbs'        => $prod->carbohydrates ?? 0,
                'storage_tips' => null,
                'video_url'    => null,
                'status'       => $status,
                'publish_date' => $prod->created_at ?? null,
                'public_id'    => Str::random(28),
            ]);

            $productMap[$prod->id] = $ingredient->id;

            // Меры
            $measures = [
                'tbsp'    => $prod->tbsp,
                'tsp'     => $prod->tsp,
                'cup200'  => $prod->cup200,
                'cup250'  => $prod->cup250,
                'pieces'  => $prod->pieces,
                'packets' => $prod->packets,
                'drips'   => $prod->drips,
            ];
            foreach ($measures as $unit => $value) {
                if ($value !== null && $value != 0) {
                    IngredientMeasure::create([
                        'ingredient_id' => $ingredient->id,
                        'unit'          => $unit,
                        'grams'         => $value,
                    ]);
                }
            }
        }

        // 3. Рецепты
        $recipes = $old->table('recipes')->get();
        foreach ($recipes as $rec) {
            $status = $rec->hidden ? 'draft' : 'published';
            $coverImage = $this->importLegacyCover('recipes', $rec->id, $rec->cover, 'recipes/covers');

            $newRecipe = Recipe::create([
                'title'        => $rec->title,
                'slug'         => $rec->slug,
                'description'  => $rec->summary,
                'category_id'  => $rec->category_id,
                'author_id'    => 1,
                'cover_image'  => $coverImage,
                'video_url'    => $rec->video ?? null,
                'tips'         => $rec->gadget_list,
                'access'       => 'public',
                'status'       => $status,
                'publish_date' => $rec->created_at ?? null,
                'public_id'    => Str::random(28),
                'duration'     => $rec->duration ?? 0,
                'bookmarks'    => $rec->bookmarks ?? 0,
                'likes'        => $rec->likes ?? 0,
            ]);

            // Шаги
            $steps = $old->table('recipe_steps')->where('recipe_id', $rec->id)->orderBy('id')->get();
            foreach ($steps as $index => $step) {
                RecipeStep::create([
                    'recipe_id'    => $newRecipe->id,
                    'step_number'  => $index + 1,
                    'description'  => $step->content,
                    'image'        => $step->image ? $this->importLegacyStepImage($step->image) : null,
                ]);
            }

            // Наборы продуктов
            $sets = $old->table('recipe_product_sets')->where('recipe_id', $rec->id)->orderBy('id')->get();
            foreach ($sets as $setIndex => $set) {
                $items = $old->table('product_productset')
                            ->where('recipe_product_set_id', $set->id)
                            ->orderBy('id')
                            ->get();

                foreach ($items as $itemIndex => $item) {
                    $ingredientId = $productMap[$item->product_id] ?? null;
                    $productName = '';
                    if ($item->product_id) {
                        $oldProduct = $old->table('products')->where('id', $item->product_id)->first();
                        $productName = $oldProduct->title ?? '';
                    }

                    RecipeIngredient::create([
                        'recipe_id'     => $newRecipe->id,
                        'ingredient_id' => $ingredientId,
                        'name'          => $productName ?: 'Продукт',
                        'quantity'      => $item->amount ?? 0,
                        'unit'          => $item->measure ?? '',
                        'section'       => $set->title,
                        'sort_order'    => $setIndex * 100 + $itemIndex,
                    ]);
                }
            }

            // Прямые ингредиенты (без набора)
            $directItems = $old->table('product_productset')
                              ->where('recipe_id', $rec->id)
                              ->whereNull('recipe_product_set_id')
                              ->orderBy('id')
                              ->get();

            foreach ($directItems as $directItem) {
                $ingredientId = $productMap[$directItem->product_id] ?? null;
                $productName = '';
                if ($directItem->product_id) {
                    $oldProduct = $old->table('products')->where('id', $directItem->product_id)->first();
                    $productName = $oldProduct->title ?? '';
                }

                RecipeIngredient::create([
                    'recipe_id'     => $newRecipe->id,
                    'ingredient_id' => $ingredientId,
                    'name'          => $productName ?: 'Продукт',
                    'quantity'      => $directItem->amount ?? 0,
                    'unit'          => $directItem->measure ?? '',
                    'section'       => null,
                    'sort_order'    => $directItem->id,
                ]);
            }
        }

        // 4. Статьи
        $articles = $old->table('articles')->get();
        foreach ($articles as $art) {
            $status = $art->hidden ? 'draft' : 'published';
            $coverImage = $this->importLegacyCover('articles', $art->id, $art->cover, 'articles/covers');

            $article = Article::create([
                'title'        => $art->title,
                'slug'         => $art->slug,
                'excerpt'      => $art->summary,
                'content'      => $art->content,
                'cover_image'  => $coverImage,
                'author_id'    => 1,
                'status'       => $status,
                'publish_date' => $art->created_at ?? null,
                'public_id'    => Str::random(28),
                'meta_title'       => null,
                'meta_description' => null,
                'meta_keywords'    => null,
            ]);

            // Привязка к категории через many-to-many
            if ($art->category_id) {
                $article->categories()->attach($art->category_id);
            }
        }

        $this->command->info('Legacy data imported successfully (including articles).');
    }

    /**
     * Handles import legacy cover for the legacy data seeder.
     *
     * @param mixed $type
     * @param mixed $id
     * @param mixed $coverCode
     * @param mixed $targetDir
     * @return mixed
     */
    private function importLegacyCover($type, $id, $coverCode, $targetDir)
    {
        if (!$coverCode) return null;

        $oldRelative = 'files/' . $type . '/' . id_to_path($id) . '/' . $coverCode . '_1to1.jpg';
        $source = $this->findLegacyFile($oldRelative);

        if (!$source) {
            $this->command->warn("Обложка не найдена: {$oldRelative}");
            return null;
        }

        $newFilename = (string) Str::uuid() . '.jpg';
        $newRelativePath = $targetDir . '/' . $newFilename;
        Storage::disk('public')->put($newRelativePath, file_get_contents($source));

        return $newRelativePath;
    }

    /**
     * Handles import legacy step image for the legacy data seeder.
     *
     * @param mixed $imagePath
     * @return mixed
     */
    private function importLegacyStepImage($imagePath)
    {
        if (!$imagePath) return null;

        $source = $this->findLegacyFile($imagePath);
        if (!$source) {
            $this->command->warn("Изображение шага не найдено: {$imagePath}");
            return null;
        }

        $ext = pathinfo($imagePath, PATHINFO_EXTENSION) ?: 'jpg';
        $newFilename = (string) Str::uuid() . '.' . $ext;
        $newRelativePath = 'recipes/steps/' . $newFilename;
        Storage::disk('public')->put($newRelativePath, file_get_contents($source));

        return $newRelativePath;
    }

    /**
     * Finds legacy file in the current context.
     *
     * @param string $relativePath
     * @return mixed
     */
    private function findLegacyFile(string $relativePath)
    {
        $candidates = [];

        if ($this->legacyFilesRoot) {
            $candidates[] = rtrim($this->legacyFilesRoot, '/\\') . '/' . ltrim($relativePath, '/');
        }

        $candidates[] = public_path('storage/files/' . ltrim($relativePath, '/'));
        $candidates[] = storage_path('app/public/files/' . ltrim($relativePath, '/'));

        $candidates[] = public_path($relativePath);
        $candidates[] = storage_path('app/public/' . ltrim($relativePath, '/'));

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
