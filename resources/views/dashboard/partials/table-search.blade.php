<div class="ops-grid-search">
    <i class="oi oi-magnifying-glass" aria-hidden="true"></i>
    <label class="sr-only" for="{{ $tableId }}-search">{{ __('Search') }}</label>
    <input id="{{ $tableId }}-search" type="search" data-table-search="{{ $tableId }}" placeholder="{{ $placeholder }}" value="{{ $searchValue ?? '' }}" maxlength="191" autocomplete="off" @if(isset($searchForm)) name="search" form="{{ $searchForm }}" @endif>
</div>
