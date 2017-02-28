<div id="top">
    <div class="fixed">
        <a href="{{ url('admin') }}" title="{{ Config::get('constant.PRODUCT_NAME') }}" class="logo"><img src="{{ url('asset_admin/img/logo.png')}}" style="width: auto; height:45px;" alt="" /></a>
        <ul class="top-menu">
            <li><a class="fullview"></a></li>
            <li><a class="showmenu"></a></li>
            <li>


            <a href="#" title="" class="messages"><i class=""></i></a></li>

            <li class="dropdown">
                <a class="user-menu" data-toggle="dropdown"><i class="icon-globe" style="color:#fff;"></i><span> {{ trans('admin.language') }} <b class="caret"></b></span></a>
                <ul class="dropdown-menu">
                    <li><a href="{{ url('locale/en') }}">English</a></li>
                    <li><a href="{{ url('locale/fr') }}">France</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a class="user-menu" data-toggle="dropdown">
                    @if(Auth::user()->img==''||Auth::user()->img=='no_image.png')

                    <img style="height:30;width:30px" src="{{ url('public/images/no_image.png') }}" alt="profpic" />

                    @else
                        <img style="height:30;width:30px" src="{{ url('public/images/admin/'.Auth::user()->img) }}" alt="profpic" />

                        @endif

                    <span>  {{ Auth::user()->restaurantname  }}   <b class="caret"></b></span></a>
                <ul class="dropdown-menu">
                    <li><a href="{{ url('admin/myaccount/'.Auth::user()->id.'/show') }}"><i class="icon-user"></i>{{ trans('admin.profile') }}</a></li>
                    <!-- <li><a href="#" title=""><i class="icon-inbox"></i>Messages<span class="badge badge-info">9</span></a></li> -->
                    <li><a href="{{ url('logout') }}"><i class="icon-remove"></i>{{ trans('admin.logout') }}</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>