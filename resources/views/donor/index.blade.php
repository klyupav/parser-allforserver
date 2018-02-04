@extends('layouts.app')

@section('title')/Доноры@endsection

@section('content')
    <div class="container">
        @if (session('alert-success'))
            <div class="alert alert-success">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('alert-success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <h1>Список доноров</h1>
            </div>
        </div>
        <div class="row">
            <table class="table table-striped">
                <tr>
                    <th>No.</th>
                    <th>Наименование донора</th>
                    <th>Ссылка на донора</th>
                    <th>Класс парсера</th>
                    <th>Обрабатывается?</th>
                    <th></th>
                </tr>
                <a href="{{route('donor.create')}}" class="btn btn-info pull-right">Добавить донора</a><br><br>
                <?php $no=1; ?>
                @foreach($donors as $donor)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$donor->name}}</td>
                        <td>{{$donor->link}}</td>
                        <td>{{$donor->class}}</td>
                        <td>
                            @if( $donor->used )
                                <div class="text-center">Да</div>
                            @else
                                <div class="text-center">Нет</div>
                            @endif
                        </td>
                        <td>
                            <form class="" action="{{route('donor.destroy',$donor->id)}}" method="post">
                                <input type="hidden" name="_method" value="delete">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <a href="{{route('donor.edit',$donor->id)}}" class="btn btn-primary">Изменить</a>
                                <input type="submit" class="btn btn-danger" onclick="return confirm('Вы действительно хотите удалить донора?');" name="name" value="Удалить">
                                <a href="{{url("donor/{$donor->name}/sources")}}" class="btn btn-default">Список источников</a>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
