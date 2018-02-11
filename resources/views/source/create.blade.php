@extends('layouts.app')

@section('title')/Новый источник для донора {{ $donor_name }}@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Новый источник</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="" action="{{url("/donor/{$donor_name}/sources/store")}}" method="post">
                    {{csrf_field()}}
                    <input type="hidden" name="donor_id" value="{{ $donor_id }}">
                    <div class="form-group{{ ($errors->has('url')) ? $errors->first('url') : '' }}">
                        <input type="text" name="url" class="form-control" placeholder="Ссылка">
                        {!! $errors->first('url','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="form-group{{ ($errors->has('category_id')) ? $errors->first('category_id') : '' }}">
                        <input type="text" name="category_id" class="form-control" placeholder="ID категории для экспорта">
                        {!! $errors->first('category_id','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="form-group{{ ($errors->has('procent_nakrutki')) ? $errors->first('procent_nakrutki') : '' }}">
                        <input type="text" name="procent_nakrutki" class="form-control" placeholder="Процент накрутки">
                        {!! $errors->first('procent_nakrutki','<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="review"> Обработан
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Добавить">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
