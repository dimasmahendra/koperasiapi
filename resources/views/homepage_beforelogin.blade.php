@extends('masterbeforelogin')
@section('title')
{{ trans('setlang.homepage') }} | {{ Config::get('constant.PRODUCT_NAME') }}
@stop
@section('content')

<div class="wrapper">

    <!-- Action tabs -->
    <div class="actions-wrapper">
        <div class="actions">
            <ul class="action-tabs czindex">
                <li><a href="#welcome" title="">{{ trans('setlang.welcome') }}</a></li>
                <li><a href="#directory" title="">{{ trans('setlang.directory') }}</a></li>
                <!-- <li><a href="#booking" title="">{{ trans('setlang.booking') }}</a></li> -->
                <li><a href="#guide" title="">{{ trans('setlang.guide') }}</a></li>
                <li><a href="#request" title="" >{{ trans('setlang.requestarestaurant') }}</a></li>
                <li><a href="#contactus" title="">{{ trans('setlang.contactus') }}</a></li>
            </ul>
            <div id="welcome">
                <br>

                <ul class="">
                    <li>
                        @include('homepage.welcome')
                    </li>
                </ul>
            </div>
            <div id="directory">
                <br>
                <br>
                <br>
                <ul class="">
                    <li>
                        @include('homepage.direktori')
                    </li>
                </ul>
            </div>
           <!-- <div id="booking">
                <ul class="statistics">
                    <li>
                        @include('homepage.booking')
                    </li>
                </ul>
            </div> -->
            <div id="guide">
                <ul class="">
                    <li>
                        @include('homepage.guide')
                    </li>
                </ul>
            </div>
            <div id="request">
                <br>
                <br>
                <br>
                <ul class="round-buttons">
                    <li>
                        <div class="depth"><a href="{{url('/reqresto')}}" title="{{ trans('setlang.clicktojoin') }} {{ Config::get('constant.PRODUCT_NAME') }}" class="tip"><i class="icon-group"></i></a></div>
                    </li>
                </ul>
            </div>
            <div id="contactus">
                <ul class="statistics">
                    <li>
                        @include('homepage.contactus')
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /action tabs -->
</div>
@stop