@extends('layouts.app')

@section('content')
    <div class="container">
        {!! $dataTable->table([], true) !!}
    </div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('js/buttons/css/buttons.dataTables.css') }}">
<script src="{{ asset('js/buttons/js/dataTables.buttons.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
{!! $dataTable->scripts() !!}
@endpush


