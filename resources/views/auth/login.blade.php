@extends('masterbeforelogin')
@section('title')
{{ trans('admin.login') }}
@stop
@section('content')
<!-- Login block -->
<div class="login">
@if (count($errors) > 0)
    <div class="alert alert-danger">
        {{ trans('admin.loginalert') }}<br><br>
        <!--	<ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
                </ul> -->
    </div>
@endif

    @if (session('message'))
    <div class="alert alert-warning" style="margin-top: 16px;">
        <button type="button" class="close" data-dismiss="alert">X</button>
        <i>{{ trans('admin.youhavebeenloggedout') }}...</i>
    </div>
    @endif
    @if (session('statuse'))
    <div class="alert alert-danger" style="margin-top: 16px;">
        <button type="button" class="close" data-dismiss="alert">X</button>
        <i>{{ trans('admin.loginalert') }}</i>
    </div>
    @endif


    <div class="navbar">
        <div class="navbar-inner">
            <h6><i class="icon-user"></i>{{ trans('admin.loginpage') }}</h6>

        </div>
    </div>
    <div class="well">
        {!! Form::open(array('url' => 'login','class'=>'row-fluid')) !!}
        <div class="control-group {{$errors->has('username') ? 'has-error':'' }}">
            {!! Form::label('username', trans('admin.username'),['class' => 'control-label']) !!}
            <div class="controls">
                {!! Form::text('username',null,['class' => 'span12','required']) !!}
                {!!   $errors->first('username','<span class="help-block">:message</span>') !!}
            </div>
        </div>
        <div class="control-group {{$errors->has('password') ? 'has-error':'' }}">
            <label class="control-label">{{ trans('admin.password') }}:</label>
            <div class="controls">
                <input class="span12" type="password" name="password" required />
                {!!   $errors->first('password','<span class="help-block">:message</span>') !!}
            </div>
        </div>
        <div class="login-btn">
            {!! Form::submit(trans('admin.login'),['class' => 'btn btn-primary btn-block']) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>



<!-- /login block -->
@stop