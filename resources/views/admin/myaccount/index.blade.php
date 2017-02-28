@extends('admin.master')
@section('title')
{{ trans('admin.myaccount') }}
@stop
@section('content')
@if (session('status'))
<script>
    $.jGrowl('{{ session('status') }}', { sticky: true, theme: 'growl-success', header: '{{ trans('admin.success') }}!' });
</script>
@endif
<h5 class="widget-name"><i class="icon-align-justify"></i>@yield('title')</h5>
<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6></h6>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered" id="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('admin.personincharge') }}</th>
                    <th>{{ trans('admin.restoname') }}</th>
                    <th>{{ trans('admin.img') }}</th>
                    <th>{{ trans('admin.option') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        1
                    </td>
                    <td>{{ $myaccount->personincharge }}</td>
                    <td>{{$myaccount->restaurantname }}</td>
                    <td>
                        @if ($myaccount->img=='')
                        <img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
                        @else
                        <img src="{{ url('/public/images/myaccount/'.$myaccount->img)  }}" width="90" height="90">
                        @endif
                    </td>
                    <td>
                        <p class="item-buttons">
                            <a href="{{ url('/admin/myaccount/'.$myaccount->id.'/edit') }}" class="btn btn-info tip" title="{{ trans('admin.editmyaccount') }}" data-original-title="{{ trans('admin.editmyaccount') }}"><i class="icon-pencil"></i></a>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@stop