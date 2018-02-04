@extends('layouts.app')

@section('content')
<div class="tabs">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#data-set">Data Set</a></li>
    </ul>
    <div class="tab-content">
         <div class="tab-pane active" id="data-set">
            @yield('data-set-table')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
@yield('js')
</script>
@endpush
