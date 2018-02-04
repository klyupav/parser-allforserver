@extends('layouts.app')

@section('title')/Доноры/Новый@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Новый донор</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="" action="{{route('donor.store')}}" method="post">
                    {{csrf_field()}}
                    <div class="form-group{{ ($errors->has('name')) ? $errors->first('name') : '' }}">
                        <input type="text" name="name" class="form-control" placeholder="Наименование проекта">
                        {!! $errors->first('name','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="form-group{{ ($errors->has('link')) ? $errors->first('link') : '' }}">
                        <input type="text" name="link" class="form-control" placeholder="Ссылка на проект">
                        {!! $errors->first('link','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="form-group{{ ($errors->has('class')) ? $errors->first('class') : '' }}">
                        <input type="text" name="class" class="form-control" placeholder="Класс парсера">
                        {!! $errors->first('class','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="used" checked> Обрабатывается
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Создать">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
