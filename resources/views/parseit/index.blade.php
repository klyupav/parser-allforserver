@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('alert-success'))
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('alert-success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-heading">Сбор данных</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="label label-default">Страниц</span>
                            <span class="label label-info">всего: <span class="badge" id="source_all">{{ $parsing_info['всего'] }}</span></span>
                            <span class="label label-primary">просмотренно: <span class="badge" id="source_review">{{ $parsing_info['просмотренно'] }}</span></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress">
                                <div id="progress_bar" class="progress-bar" role="progressbar" style="width: {{ $parsing_info['процент'] }}%;" data-valuenow="{{ $parsing_info['просмотренно'] }}" data-valuemin="0" data-valuemax="{{ $parsing_info['всего'] }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <a id="btn-play" class="btn btn-success glyphicon glyphicon-play" href="{{ url('/parseit/go') }}" style="display: {{ $parsing_info['работает'] ? 'none' : '' }}"></a>
                            <a id="btn-stop" class="btn btn-danger glyphicon glyphicon-stop" href="{{ url('/parseit/stop') }}" style="display: {{ $parsing_info['работает'] ? '' : 'none' }}"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function update_parsing_info ()
    {
        var data = {
            '_token': $('meta[name="csrf-token"]').attr('content')
        }
        $.ajax({ //  сам запрос
            type: 'POST',
            url: '/parseit/get-parsing-info',
            data: data,
            dataType: "json"
        }).done(function(res) {
            if ( res.success )
            {
                console.log(res.parsing_info);
                $('#source_all').html(res.parsing_info['всего']);
                $('#source_review').html(res.parsing_info['просмотренно']);
                $('#progress_bar').data('valuemax', res.parsing_info['всего']);
                $('#progress_bar').data('valuenow', res.parsing_info['просмотренно']);
                $('#progress_bar').width(res.parsing_info['процент']+"%");
                if ( res.parsing_info['работает'] )
                {
                    $('#btn-play').hide();
                    $('#btn-stop').show();
                }
                else
                {
                    $('#btn-play').show();
                    $('#btn-stop').hide();
                }
            }
            else
            {
                console.log('Get parsing info error');
            }
        }).fail(function() {
            console.log('Get parsing info error, no response');
        });
    }
    $(function () {

    });
    setInterval(function (){
        update_parsing_info();
    }, 5000);
</script>
@endpush