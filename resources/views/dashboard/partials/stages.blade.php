<ol class="shipment-stages">
    @php($stageKeys = array_keys(\App\Support\ShipmentStages::labels()))
    @php($stageIndex = array_search($status, $stageKeys, true))
    @foreach(\App\Support\ShipmentStages::labels() as $key => $label)
        <li class="{{ $stageIndex !== false && $loop->index <= $stageIndex ? 'is-complete' : '' }}" @if($key === $status) aria-current="step" @endif><span>{{ $loop->iteration }}</span> {{ $label }}</li>
    @endforeach
</ol>
