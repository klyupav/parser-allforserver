@extends('layouts.app')

@section('title')/Источники донора {{ $donor_name }}@endsection

@section('content')
    <div class="container">
        @if (session('alert-success'))
            <div class="alert alert-success">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('alert-success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <h1>Список источников</h1>
            </div>
        </div>
        <div class="row">
            <table class="table table-striped">
                <tr>
                    <th>No.</th>
                    <th>Ссылка</th>
                    <th>ID категории для экспорта</th>
                    <th>Процент накрутки</th>
                    <th>Обработан</th>
                    <th></th>
                </tr>
                <a href="{{url("/donor/{$donor_name}/sources/create")}}" class="btn btn-info pull-right">Добавить источник</a><br><br>
                <?php $no=1; ?>
                @foreach($sources as $source)
                    <tr>
                        <td>{{$no++}}</td>
                        <td><a href="{{$source->url}}">{{ \Illuminate\Support\Str::limit($source->url, 30) }}</a></td>
                        <td>{{$source->category_id}}</td>
                        <td>{{$source->procent_nakrutki}}</td>
                        <td>
                            @if( $source->review )
                                <div class="text-center">Да</div>
                            @else
                                <div class="text-center">Нет</div>
                            @endif
                        </td>
                        <td>
                            <form class="" action="{{url("donor/{$donor_name}/sources/{$source->id}/destroy")}}" method="post">
                                {{--<input type="hidden" name="_method" value="delete">--}}
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <a href="{{url("donor/{$donor_name}/sources/{$source->id}/edit")}}" class="btn btn-primary">Изменить</a>
                                <input type="submit" class="btn btn-danger" onclick="return confirm('Вы действительно хотите удалить источник?');" name="name" value="Удалить">
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
