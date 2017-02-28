@extends('admin.master')
@section('title')
{{ trans('admin.user') }}
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
                    <th>{{ trans('admin.firstname') }}</th>
                    <th>{{ trans('admin.lastname') }}</th>
                    <th>{{ trans('admin.email') }}</th>
                    <th>{{ trans('admin.akses') }}</th>
                    <th>{{ trans('admin.status') }}</th>
                    <th>{{ trans('admin.img') }}</th>
                    <th>{{ trans('admin.option') }}</th>
                </tr>
            </thead>
            <tbody>
            <?php $no=1; ?>
            @foreach ($user as  $r)

                <tr>
                    <td>
                        <?php echo $no; $no++; ?>
                    </td>
                    <td>{{ $r->first_name }}</td>
                    <td>{{ $r->last_name }}</td>
                    <td>{{ $r->email }}</td>
                    <td>{{ $r->akses }}</td>
                    <td>{{$r->status }}</td>
                    <td>
                        @if ($r->img==''||$r->img=='no_image.png')
                        <img src="{{ url('/public/images/no_image.png')  }}" width="50" height="50">
                         @elseif (filter_var($r->img, FILTER_VALIDATE_URL))
                         <img src="{{ url($r->img)  }}" width="50" height="50">
                        @else
                        <img src="{{ url('/public/images/user/thumb_'.$r->img)  }}" width="50" height="50">
                        @endif
                    </td>
                    <td>
                        <p class="item-buttons">
                            <a href="{{ url('/admin/user/'.$r->id.'/edit') }}" class="btn btn-info tip" title="{{ trans('admin.edituser') }}" data-original-title="{{ trans('admin.edituser') }}"><i class="icon-pencil"></i></a>
                            <a href="{{ url('/admin/user/'.$r->id.'/destroy') }}" class="btn btn-danger tip" title="{{ trans('admin.deletethistips') }}" data-original-title="{{ trans('admin.deletethistips') }}"><i class="icon-trash"></i></a>

                        </p>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop