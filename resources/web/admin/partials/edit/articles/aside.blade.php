<div class="admin-aside-stack">
    <section class="admin-surface">
        <div class="admin-surface__body">
            <x-ui.form.typeahead name="author_id" id="article-author" label="Автор" :value="$article->author_id" :options="[]" endpoint="{{ route('admin.users.options') }}" />
            <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_visible" value="1" id="article-visible" @checked($article->is_visible)><label class="form-check-label" for="article-visible">Видна на сайте</label></div>
            <x-ui.form.input name="publish_at" label="Отложенная публикация" type="datetime-local" :value="$article->publish_at?->format('Y-m-d\TH:i')" hint="Пусто — публиковать сразу после включения видимости." />
            <x-ui.form.media-dropzone name="cover" ratio="16x9" :existing-url="$article->cover_url" />
        </div>
    </section>
</div>
