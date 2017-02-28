@extends('admin.master')
@section('title')
{{ trans('admin.editmyaccount') }}
@stop
@section('content')

    @if (session('status'))
        <script>
            $.jGrowl('{{ session('status') }}', { sticky: true, theme: 'growl-success', header: '{{ trans('admin.success') }}!' });
        </script>
    @endif
<h5 class="widget-name"><i class="icon-align-justify"></i>@yield('title')</h5>
<fieldset>
    <div class="widget row-fluid">
        <div class="navbar">
            <div class="navbar-inner">
                <h6>&nbsp;</h6>
            </div>
        </div>
        <div class="well">
            {!! Form::model($myaccount, ['url'=>'admin/myaccount/'.$myaccount->id, 'method'=>'PATCH'  , 'class'=>'form-horizontal' ,  'files'=>true]) !!}
            <div class="control-group {{$errors->has('fullname') ? 'has-error':'' }}">
                {!! Form::label('fullname', 'Full Name',['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::text('fullname',null,['class' => 'span6']) !!}
                    {!!   $errors->first('fullname','<span class="help-block">:message</span>') !!}
                </div>
            </div>
            <div class="control-group {{$errors->has('email') ? 'has-error':'' }}">
                {!! Form::label('email', trans('admin.email'),['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::email('email',null,['class' => 'span6']) !!}
                    {!!   $errors->first('email','<span class="help-block">:message</span>') !!}
                </div>
            </div>
            <div class="control-group {{$errors->has('username') ? 'has-error':'' }}">
                {!! Form::label('username', trans('admin.username'),['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::text('username',null,['class' => 'span6']) !!}
                    {!!   $errors->first('username','<span class="help-block">:message</span>') !!}
                </div>
            </div>
            <div class="control-group {{$errors->has('password') ? 'has-error':'' }}">
                {!! Form::label('password', trans('admin.password'),['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::password('password',null,['class' => 'span6']) !!}
                    {!!   $errors->first('password','<span class="help-block">:message</span>') !!}
                </div>
            </div>
            <div class="control-group {{$errors->has('password_confirmation') ? 'has-error':'' }}">
                {!! Form::label('password', trans('admin.passwordconfirmation'),['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::password('password_confirmation',null,['class' => 'span6']) !!}
                    {!!   $errors->first('password_confirmation','<span class="help-block">:message</span>') !!}
                </div>
            </div>

            <div class="control-group {{$errors->has('img') ? 'has-error':'' }}">
                {!! Form::label('img', 'Image',['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::file('img',null,['class' => 'span6']) !!}
                    {!!   $errors->first('img','<span class="help-block">:message</span>') !!}
                    <br><br>
                    @if ($myaccount->img==''||$myaccount->img=='no_image.png')
                        <img src="{{ url('public/images/admin/no_image.png')  }}" width="100" height="100">
                        @else
                    <img src="{{ url('public/images/admin/'.$myaccount->img)  }}" width="100" height="100">
                        @endif
                </div>
            </div>
            <div class="form-actions align-right">
                <div class="form-group ">
                    {!! Form::submit(trans('admin.submit'),['class' => 'btn btn-primary']) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</fieldset>

@stop