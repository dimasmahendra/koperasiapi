<!-- Sidebar -->
<div id="sidebar">
    <div class="sidebar-tabs">
        <!-- <ul class="tabs-nav one-items">
            <li><a href="#general" title=""><i class="icon-reorder"></i></a></li>
            <li><a href="#stuff" title=""><i class="icon-cogs"></i></a></li>
        </ul> -->
        <div id="general">

            <ul class="navigation widget">
                <li class="{{ (Request::segment(2)=='')  ? 'active' : '' }}" >
                    <a href="{{url('/admin')}}" title=""><i class="icon-home"></i>{{ trans('admin.dashboard') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='myaccount')  ? 'active' : '' }}">
                    <a href="{{url('/admin/myaccount/'.Auth::user()->id.'/show')}}" title="" ><i class="icon-user"></i>{{ trans('admin.myaccount') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='user')  ? 'active' : '' }}">
                    <a href="{{url('/admin/user')}}" title="" ><i class="icon-user"></i>{{ trans('admin.user') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='article')  ? 'active' : '' }}">
                    <a href="{{url('/admin/article')}}" title="" ><i class="icon-user"></i>{{ trans('admin.article') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='tips')  ? 'active' : '' }}">
                    <a href="{{url('/admin/tips')}}" title="" ><i class="icon-user"></i>{{ trans('admin.tips') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='training')  ? 'active' : '' }}">
                    <a href="{{url('/admin/training')}}" title="" ><i class="icon-user"></i>{{ trans('admin.training') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='trainingdate')  ? 'active' : '' }}">
                    <a href="{{url('/admin/trainingdate')}}" title="" ><i class="icon-user"></i>{{ trans('admin.trainingdate') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='trainingtime')  ? 'active' : '' }}">
                    <a href="{{url('/admin/trainingtime')}}" title="" ><i class="icon-user"></i>{{ trans('admin.trainingtime') }}</a>
                </li>

                <li class="{{ (Request::segment(2)=='promotion')  ? 'active' : '' }}">
                    <a href="{{url('/admin/promotion')}}" title="" ><i class="icon-user"></i>{{ trans('admin.promotion') }}</a>
                </li>


                <li class="{{ (Request::segment(2)=='bookinglist')  ? 'active' : '' }}">
                    <a href="{{url('/admin/bookinglist')}}" title="" ><i class="icon-user"></i>{{ trans('admin.bookinglist') }}</a>
                </li>




            </ul>

        </div>
    </div>
</div>
<!-- /sidebar -->