@extends('admin.master')
@section('title')
{{ trans('admin.addtips') }}
@stop
@section('content')
<h5 class="widget-name"><i class="icon-align-justify"></i>@yield('title')</h5>
<fieldset>
    <div class="widget row-fluid">
        <div class="navbar">
            <div class="navbar-inner">
                <h6>&nbsp;</h6>
            </div>
        </div>
        <div class="well">
            {!! Form::open(['route'=>'admin.tips.store', 'class'=>'form-horizontal','files'=>true]) !!}
            @include('admin.tips.form', ['type' => 'create'])
            {!! Form::close() !!}
        </div>
    </div>
</fieldset>
@stop