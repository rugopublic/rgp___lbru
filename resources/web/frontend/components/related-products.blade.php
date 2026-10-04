@if(isset($products) && $products->isNotEmpty())
<section class="sale-block mt-5" aria-labelledby="related-products-title">
    <div class="section-head mb-4">
        <div>
            <span class="eyebrow">Можно добавить к делу</span>
            <h2 id="related-products-title" class="h5 mt-2">Полезные материалы</h2>
            <p class="text-muted mb-0">То, что логично использовать вместе с этим материалом.</p>
        </div>
    </div>
    <div class="row g-3">
        @foreach($products as $product)
            <div class="col-md-6">
                <a class="product-card" href="{{ route('products.show', $product->public_id) }}">
                    <div class="product-card__body">
                        <span class="eyebrow">Материал</span>
                        <strong>{{ $product->title }}</strong>
                        <div class="price mt-1">{{ number_format((float) $product->price, 0, ',', ' ') }} ₽</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif
