<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__body">
            <div class="row g-3">
                <div class="col-lg-8"><x-ui.form.input name="title" label="Название" :value="$recipe->title" required/></div>
                <div class="col-lg-4"><x-ui.form.typeahead name="author_id" id="recipe-author" label="Автор" :value="$recipe->author_id" :options="$authors" endpoint="{{ route('admin.users.options') }}"/></div>
            </div>
            <x-ui.form.textarea name="description" id="recipe-description" label="Краткое описание" :value="$recipe->description"/>
            <x-ui.form.rich-textarea name="body" id="recipe-body" input-id="recipe-body-input" labelledby="recipe-body-label" :value="$recipe->body"/>
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__head"><div><h3 class="h6 mb-1">Ингредиенты</h3><div class="admin-section-note">Группа → ингредиент → количество → единица. Структура сериализуется hidden/input и проверяется сервером.</div></div><div class="d-flex gap-2"><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recipeGroupCreateModal">Добавить группу</button><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#recipeIngredientCreateModal">Добавить ингредиент</button></div></div>
        <div class="admin-surface__body">
            <div class="admin-structure" data-recipe-structure>
                @php($initial=['groups'=>$recipe->ingredientGroups->map(fn($g)=>['id'=>$g->id,'label'=>$g->title,'items'=>$g->ingredients->map(fn($i)=>['id'=>$i->id,'value'=>$i->reference_item_id,'label'=>$i->referenceItem?->title,'data'=>['quantity'=>$i->quantity,'unit'=>$i->unit]])->values()->all()])->values()->all(),'items'=>[]])
                <x-ui.form.sortable name="structure" :value="$initial" :button="false" :grouped="true" :endpoint="null">
                    @foreach($recipe->ingredientGroups as $group)
                        <x-ui.form.sortable.group :group-id="$group->id" :label="$group->title">
                            @foreach($group->ingredients as $ingredient)
                                <x-ui.form.sortable.item :value="$ingredient->reference_item_id" :data-sortable-item-label-value="$ingredient->referenceItem?->title" :data-item-data-quantity="$ingredient->quantity" :data-item-data-unit="$ingredient->unit">
                                    <span>{{ $ingredient->referenceItem?->title }}</span>
                                    <div class="row g-2 mt-1"><div class="col-5"><input class="form-control form-control-sm" type="number" min="0" step="0.01" value="{{ $ingredient->quantity }}" data-recipe-ingredient-field="quantity" aria-label="Количество"></div><div class="col-7"><input class="form-control form-control-sm" type="text" value="{{ $ingredient->unit }}" data-recipe-ingredient-field="unit" aria-label="Единица"></div></div>
                                </x-ui.form.sortable.item>
                            @endforeach
                        </x-ui.form.sortable.group>
                    @endforeach
                </x-ui.form.sortable>
            </div>
        </div>
    </section>
</div>
