<!DOCTYPE html>
<html lang="en">

@include('admin.header')

<body>

<!-- Fixed top -->
@include('admin.topmenu')
<!-- /fixed top -->


<!-- Content container -->
<div id="container">



    @include('admin.leftbar')

        <!-- Content -->
         <div id="content">
             <div class="wrapper">

             <!-- Content wrapper -->
             @include('admin.breadcumb')
             <!-- /content wrapper -->

                  @yield('content')


             </div>
         </div>
        <!-- /content -->


</div>
<!-- /content container -->


<!-- Footer -->
@include('admin.footer')
<!-- /footer -->

</body>
</html>
