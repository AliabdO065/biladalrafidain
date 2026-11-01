<div class="catalog-grid">
@forelse($items as $item)
    <article class="catalog-card">
        <a class="catalog-cover" href="{{ route('fronted.catalog.item',$item->id) }}">@if($photo=$item->photos->first())<img src="{{ route('fronted.catalog.photo',$photo) }}" alt="{{ $item->title }}" loading="lazy" width="600" height="450">@else<span class="fa-solid fa-box-open" aria-hidden="true"></span>@endif<span class="catalog-kind">{{ __('catalog.'.$item->kind) }}</span></a>
        <div class="catalog-card-body"><span class="catalog-category">{{ $item->category->label }}</span><h2><a href="{{ route('fronted.catalog.item',$item->id) }}">{{ $item->title }}</a></h2><a class="catalog-maker" href="{{ route('fronted.catalog.factory',$item->factory_id) }}"><i class="fa-solid fa-industry" aria-hidden="true"></i> {{ $item->factory->name }}</a>
        <p>{{ \Illuminate\Support\Str::limit($item->description,110) }}</p><div class="catalog-card-bottom"><strong>{{ $item->price !== null ? $item->price.' '.$item->currency : __('catalog.ask_price') }}</strong><span>{{ $item->factory->city }}</span></div></div>
    </article>
@empty
    <div class="catalog-empty"><span class="fa-solid fa-box-open" aria-hidden="true"></span><h2>{{ __('catalog.empty') }}</h2><p>{{ __('catalog.empty_hint') }}</p></div>
@endforelse
</div>
@if($items->hasPages())<nav class="catalog-pages" aria-label="{{ __('Pagination Navigation') }}">@if($items->previousPageUrl())<a href="{{ $items->previousPageUrl() }}">{{ __('catalog.previous') }}</a>@endif<span>{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>@if($items->nextPageUrl())<a href="{{ $items->nextPageUrl() }}">{{ __('catalog.next') }}</a>@endif</nav>@endif
