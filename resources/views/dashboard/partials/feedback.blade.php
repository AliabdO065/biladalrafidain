@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@php($feedbackErrors = collect($errors->getBags())->flatMap(fn ($bag) => $bag->all())->unique())
@if($feedbackErrors->isNotEmpty())
<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($feedbackErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
