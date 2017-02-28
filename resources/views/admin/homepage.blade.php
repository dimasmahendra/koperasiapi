@extends('admin.master')
@section('title')
{{ trans('admin.dashboardadmin') }}
@stop
@section('content')
<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>{{ trans('admin.dashboard') }}</h5>
        <span>{{ trans('admin.hello') }}, {{  Auth::user()->fullname  }} !</span>
        @if (session('status'))
        <div class="alert alert-success" style="margin-top: 16px;">
            <button type="button" class="close" data-dismiss="alert">X</button>
            <strong>{{ trans('admin.loginsuccess') }}!</strong> {{ trans('admin.loginsuccessdesc') }}, <i>{{  Auth::user()->fullname  }}</i>!
        </div>
        @endif
    </div>


    <ul class="page-stats">
        <li>
            <div class="showcase">
                <span>{{ trans('admin.member') }}</span>
                <h2>{{ $data['countmember'] }} </h2>
            </div>
            <!--  <div id="total-visits" class="chart">10,14,8,45,23,41,22,31,19,12, 28, 21, 24, 20</div> -->
        </li>
        <li>
            <div class="showcase">
                <span>{{ trans('admin.postingpublication') }}</span>
                <h2>{{ $data['countpost'] }}</h2>
            </div>
            <!-- <div id="balance" class="chart">10,14,8,45,23,41,22,31,19,12, 28, 21, 24, 20</div> -->
        </li>
    </ul>
</div>
<!-- /page header -->
<!-- Action tabs -->
<div >

    <h5 class="widget-name" style="text-align: center;"><img src="{{ url('asset_admin/img/logo_big.png')}}" class="img-responsive"  alt="" style="max-height:300px;" /></h5>
</div>

<!-- /action tabs -->
@stop