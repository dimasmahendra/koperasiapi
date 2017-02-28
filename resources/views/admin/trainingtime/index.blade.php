@extends('admin.master')
@section('title')
{{ trans('admin.trainingtime') }}
@stop
@section('content')
@if (session('status'))
<script>
    $.jGrowl('{{ session('status') }}', { sticky: true, theme: 'growl-success', header: '{{ trans("admin.success") }}!' });
</script>
@endif
<h5 class="widget-name"><i class="icon-align-justify"></i>@yield('title')</h5>
<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6></h6>
            <div class="form-actions align-right">
                <a href="{{ url('admin/trainingtime/create') }}" data-placement="right" class="btn tip" data-original-title=""><i class="icon-plus"></i> &nbsp; {{ trans('admin.addtrainingtime') }}</a>
            </div>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered" id="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('admin.trainingtitle') }}</th>
                    <th>{{ trans('admin.trainingdate') }}</th>
                    <th>{{ trans('admin.trainingtime') }}</th>

                    <th>{{ trans('admin.option') }}</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; ?>
                @foreach ($trainingtime as  $r)

                <tr>
                    <td>
                        <?php echo $no; $no++; ?>
                    </td>
                    <td>{{ $r->training->title }}</td>
                    <td>{{ $r->trainingdate->date }}</td>
                    <td>{{ $r->time }}</td>

                    <td>
                        <p class="item-buttons">
                            <a href="{{ url('/admin/trainingtime/'.$r->id.'/edit') }}" class="btn btn-info tip" title="{{ trans('admin.editthistrainingtime') }}" data-original-title="{{ trans('admin.editthistrainingtime') }}"><i class="icon-pencil"></i></a>
                            <a href="{{ url('/admin/trainingtime/'.$r->id.'/destroy') }}" class="btn btn-danger tip" title="{{ trans('admin.deletethistrainingtime') }}" data-original-title="{{ trans('admin.deletethistrainingtime') }}"><i class="icon-trash"></i></a>
                        </p>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop