@once
@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/datatables/dataTables.dataTables.min.css') }}?v={{ filemtime(public_path('dashboard/assets/vendor/datatables/dataTables.dataTables.min.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('dashboard/assets/vendor/datatables/dataTables.min.js') }}?v={{ filemtime(public_path('dashboard/assets/vendor/datatables/dataTables.min.js')) }}"></script>
<script src="{{ asset('dashboard/assets/javascript/operations-tables.js') }}?v={{ filemtime(public_path('dashboard/assets/javascript/operations-tables.js')) }}"></script>
@endpush
@endonce
