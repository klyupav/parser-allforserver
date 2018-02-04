@extends('layouts.app')

@section('title')/Доноры/Редактирование@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Редактировать донора</h1>
            </div>
        </div>
        <div class="row">
            <form class="" action="{{route('donor.update',$donor->id)}}" method="post">
                <input name="_method" type="hidden" value="PATCH">
                {{csrf_field()}}
                <div class="form-group{{ ($errors->has('name')) ? $errors->first('name') : '' }}">
                    <input type="text" name="name" class="form-control" placeholder="Наименование проекта" value="{{$donor->name}}">
                    {!! $errors->first('name','<p class="help-block">:message</p>') !!}
                </div>
                <div class="form-group{{ ($errors->has('link')) ? $errors->first('link') : '' }}">
                    <input type="text" name="link" class="form-control" placeholder="Ссылка на проект" value="{{$donor->link}}">
                    {!! $errors->first('link','<p class="help-block">:message</p>') !!}
                </div>
                <div class="form-group{{ ($errors->has('class')) ? $errors->first('class') : '' }}">
                    <input type="text" name="class" class="form-control" placeholder="Класс парсера" value="{{$donor->class}}">
                    {!! $errors->first('class','<p class="help-block">:message</p>') !!}
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="used" {{$donor->used ? 'checked' : ''}}> Обрабатывается
                    </label>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Изменить">
                </div>
            </form>
        </div>
    </div>
@endsection
