<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__body">
            <div class="row g-3">
                <div class="col-lg-8"><x-ui.form.input name="title" label="Название" :value="$product->title" required /></div>
                <div class="col-lg-4"><x-ui.form.typeahead name="category_id" id="product-category" label="Категория" :value="$product->category_id" :options="$categories" endpoint="{{ route('admin.product.categories.options') }}" /></div>
            </div>
            <x-ui.form.textarea name="description" id="product-description" label="Описание" :value="$product->description" maxlength="5000" />
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__body">
            <h3 class="h6">Файл товара</h3>
            <p class="admin-section-note">Это бизнес-файл товара, а не только картинка. При замене старый файл удалится после commit.</p>
            <div class="admin-file-field">
                @if($product->file)
                    <div class="d-flex align-items-center justify-content-between gap-3 rounded-3 border bg-light-subtle p-3 mb-3">
                        <div class="min-w-0"><strong class="d-block text-truncate">{{ $product->fileName() }}</strong><span class="small text-muted">Файл хранится приватно и выдаётся только после покупки.</span></div>
                        <a class="btn btn-sm btn-light border flex-shrink-0" href="{{ route('admin.products.file', $product) }}">Скачать</a>
                    </div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="file_remove" value="1" id="product-file-remove"><label class="form-check-label" for="product-file-remove">Удалить текущий файл</label></div>
                @endif
                <label for="product-file" class="form-label">Новый файл</label>
                <input id="product-file" class="form-control" type="file" name="file" accept="{{ implode(',', config('media.allowed_document_types', [])) }}">
                <div class="form-text">PDF, Office, архивы и другие форматы из server-side whitelist. Максимум {{ number_format(config('media.document_max_size') / 1024 / 1024, 0, ',', ' ') }} МБ.</div>
            </div>
            <div class="mt-3"><x-ui.form.media-dropzone name="cover" ratio="4x3" :existing-url="$product->cover_url" /></div>
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__head"><div><h3 class="h6 mb-1">Связи с контентом</h3><div class="admin-section-note">Товар становится частью продающей цепочки: статьи, рецепты и справочник могут вести к нему.</div></div></div>
        <div class="admin-surface__body"><div class="admin-relation-stack">
            <div><x-ui.form.typeahead name="article_picker" id="product-article-picker" label="Добавить статью" endpoint="{{ route('admin.articles.options') }}" data-relation-picker="product-articles"/><x-ui.form.sortable name="article_ids" :value="$product->articles->map(fn($x)=>['value'=>(string)$x->id,'label'=>$x->title])->all()" :button="false" data-relation-sortable="product-articles">@foreach($product->articles as $x)<x-ui.form.sortable.item :value="$x->id" :data-sortable-item-label-value="$x->title">{{ $x->title }}</x-ui.form.sortable.item>@endforeach</x-ui.form.sortable></div>
            <div><x-ui.form.typeahead name="recipe_picker" id="product-recipe-picker" label="Добавить рецепт" endpoint="{{ route('admin.recipes.options') }}" data-relation-picker="product-recipes"/><x-ui.form.sortable name="recipe_ids" :value="$product->recipes->map(fn($x)=>['value'=>(string)$x->id,'label'=>$x->title])->all()" :button="false" data-relation-sortable="product-recipes">@foreach($product->recipes as $x)<x-ui.form.sortable.item :value="$x->id" :data-sortable-item-label-value="$x->title">{{ $x->title }}</x-ui.form.sortable.item>@endforeach</x-ui.form.sortable></div>
            <div><x-ui.form.typeahead name="reference_picker" id="product-reference-picker" label="Добавить ингредиент" endpoint="{{ route('admin.reference.options') }}" data-relation-picker="product-references"/><x-ui.form.sortable name="reference_item_ids" :value="$product->referenceItems->map(fn($x)=>['value'=>(string)$x->id,'label'=>$x->title])->all()" :button="false" data-relation-sortable="product-references">@foreach($product->referenceItems as $x)<x-ui.form.sortable.item :value="$x->id" :data-sortable-item-label-value="$x->title">{{ $x->title }}</x-ui.form.sortable.item>@endforeach</x-ui.form.sortable></div>
        </div></div>
    </section>
</div>
