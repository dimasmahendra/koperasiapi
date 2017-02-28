<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>@yield('title')</title>
    <link href="{{ url('asset_admin/css/main.css') }}" rel="stylesheet" type="text/css" />
    <!--[if IE 8]><link href="{{ url('asset_admin/css/ie8.css') }}" rel="stylesheet" type="text/css" /><![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" type="image/png" href="{{ url('asset_admin/img/favicon.png')}}"/>


    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <!-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDY0kkJiTPVd2U7aTOAwhc9ySH6oHxOIYM&amp;sensor=false"></script> -->

    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/charts/excanvas.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/charts/jquery.flot.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/charts/jquery.flot.resize.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/charts/jquery.sparkline.min.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.easytabs.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.collapsible.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.mousewheel.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/prettify.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.bootbox.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.colorpicker.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.timepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.jgrowl.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.fancybox.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.fullcalendar.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/ui/jquery.elfinder.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/uploader/plupload.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/uploader/plupload.html4.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/uploader/plupload.html5.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/uploader/jquery.plupload.queue.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.autosize.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.inputlimiter.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.tagsinput.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.inputmask.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.listbox.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.validation.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.validationEngine-en.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.form.wizard.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/forms/jquery.form.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/plugins/tables/jquery.dataTables.min.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/files/bootstrap.min.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/files/functions.js') }}"></script>

    <script type="text/javascript" src="{{ url('asset_admin/js/charts/graph.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/charts/chart1.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/charts/chart2.js') }}"></script>
    <script type="text/javascript" src="{{ url('asset_admin/js/charts/chart3.js') }}"></script>

</head>




<body class="no-background">

<!-- Fixed top -->
<div id="top">
    <div class="fixed">

        <a href="{{ url('/') }}" title="" class="logo"><img src="{{ url('asset_admin/img/logo.png')}}" style="width: auto; height:48px; " alt="" /></a>


        <ul class="top-menu">
            <!-- <li><a href="#"><img src="{{ url('/img/flag/id.jpg')}}" style="width: auto; height:40px;" alt="" /></a></li> -->
         <!--   <li><a href="{{ url('locale/id') }}"><img src="{{ url('/img/flag/id.png')}}" style="width: auto; height:40px;" alt="" /></a></li>
            <li><a href="{{ url('locale/en') }}"><img src="{{ url('/img/flag/en.jpg')}}" style="width: auto; height:40px;" alt="" /></a></li>
            <li><a href="{{ url('locale/fr') }}"><img src="{{ url('/img/flag/fr.jpg')}}" style="width: auto; height:40px;" alt="" /></a></li>
            <li class="dropdown">



            </li> -->
        </ul>

    </div>
</div>
<!-- /fixed top -->


@yield('content')




        <!-- Footer -->
<div id="footer">
    <div class="copyrights">&copy;  </div>
    <!--   <ul class="footer-links">
           <li><a href="" title=""><i class="icon-cogs"></i>Contact admin</a></li>
           <li><a href="" title=""><i class="icon-screenshot"></i>Report bug</a></li>
       </ul>
   </div> -->
    <!-- /footer -->

</body>
</html>
