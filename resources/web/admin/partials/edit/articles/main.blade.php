<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__body">
            <div class="row g-3">
                <div class="col-lg-8"><x-ui.form.input name="title" label="Название" :value="$article->title" required /></div>
                <div class="col-lg-4"><x-ui.form.typeahead name="category_id" id="article-category" label="Категория" :value="$article->category_id" :options="$categories" endpoint="{{ route('admin.article.categories.options') }}" /></div>
            </div>
            <x-ui.form.textarea name="description" id="article-description" label="Краткое описание" :value="$article->description" maxlength="2000" />
            <x-ui.form.rich-textarea name="body" id="article-body" input-id="article-body-input" labelledby="article-body-label" :value="$article->body" />
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__head">
            <div><h3 class="h6 mb-1">Продажа через контент</h3><div class="admin-section-note">Перетаскивайте связанные товары — порядок сохраняется в hidden/input state.</div></div>
        </div>
        <div class="admin-surface__body">
            <x-ui.form.typeahead name="product_picker" id="article-product-picker" label="Добавить товар" endpoint="{{ route('admin.products.options') }}" data-relation-picker="article-products" />
            <x-ui.form.sortable name="product_ids" :value="$selected->all()" :button="false" data-relation-sortable="article-products">
                @foreach($selected as $item)
                    <x-ui.form.sortable.item :value="$item['value']" :data-sortable-item-label-value="$item['label']">{{ $item['label'] }}</x-ui.form.sortable.item>
                @endforeach
            </x-ui.form.sortable>
        </div>
    </section>
</div>
