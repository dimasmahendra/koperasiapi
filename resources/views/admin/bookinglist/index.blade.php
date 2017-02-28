@extends('admin.master')
@section('title')
{{ trans('admin.bookinglist') }}
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
                <a href="{{ url('admin/bookinglist/create') }}" data-placement="right" class="btn tip" data-original-title=""><i class="icon-plus"></i> &nbsp; {{ trans('admin.addbookinglist') }}</a>
            </div>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered" id="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('admin.countrylang') }}</th>
                    <th>{{ trans('admin.title') }}</th>

                    <th>{{ trans('admin.totalperticipant') }}</th>

                    <th>{{ trans('admin.option') }}</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; ?>
                @foreach ($bookinglist as  $r)

                <tr>
                    <td>
                        <?php echo $no; $no++; ?>
                    </td>
                    <td>{{ $r->lang }}</td>
                    <td>{{ $r->trainingtitle }}</td>

                    <td>@if(is_null($r->kuota)) 0 @else {{$r->kuota}} @endif</td>

                    <td>
                        <p class="item-buttons">
                            <a href="{{ url('/admin/bookinglist/'.$r->trainingid.'/edit') }}" class="btn btn-info tip" title="{{ trans('admin.editthisbookinglist') }}" data-original-title="{{ trans('admin.editthisbookinglist') }}"><i class="icon-pencil"></i></a>
                            <a href="{{ url('/admin/bookinglist/'.$r->trainingid.'/destroy') }}" class="btn btn-danger tip" title="{{ trans('admin.deletethisbookinglist') }}" data-original-title="{{ trans('admin.deletethisbookinglist') }}"><i class="icon-trash"></i></a>
                        </p>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop