<div class="admin-aside-stack">
    <section class="admin-surface"><div class="admin-surface__body"><x-ui.form.input name="price" label="Цена, ₽" type="number" step="0.01" min="0" :value="$product->price"/><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_visible" value="1" id="product-visible" @checked($product->is_visible)><label class="form-check-label" for="product-visible">Разрешить продажу</label></div><div class="admin-section-note mt-2">Цена — элемент ценностного предложения; не прячьте её за лишним интерфейсом.</div></div></section>
    <section class="sales-callout"><strong>Продажная цепочка</strong><div class="admin-section-note mt-1">Товар → презентация → доверие → возражения → CTA → покупка → оплата → доступ.</div></section>
</div>
