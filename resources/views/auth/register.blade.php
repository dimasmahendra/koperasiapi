@extends('admin.master')
@section('title')
    Register
@stop
@section('content')



    <h5 class="widget-name"><i class="icon-align-justify"></i>@yield('title')</h5>

    <fieldset>


        <div class="widget row-fluid">
            <div class="navbar">
                <div class="navbar-inner">

                    <h6>&nbsp;</h6>

                </div>
            </div>

            <div class="well">


    {!! Form::open(array('url' => 'admin/register/save','class'=>'form')) !!}



     <div class="control-group {{$errors->has('maxandro') ? 'has-error':'' }}">

         {!! Form::label('maxandro','Maximum Create User Android',['class' => 'control-label']) !!}
               <div class="controls">
                  {!! Form::text('maxandro',null,['class' => 'span3', 'placeholder'=>'ex. 3']) !!}
                   {!!   $errors->first('maxandro','<span class="help-block">:message</span>') !!}
                </div>

      </div>

    <div class="control-group {{$errors->has('personincharge') ? 'has-error':'' }}">

        {!! Form::label('personincharge','Person In Charge',['class' => 'control-label']) !!}
        <div class="controls">
            {!! Form::text('personincharge',null,['class' => 'span6']) !!}
            {!!   $errors->first('personincharge','<span class="help-block">:message</span>') !!}
        </div>

    </div>


    <div class="control-group {{$errors->has('email') ? 'has-error':'' }}">

        {!! Form::label('email','Email Address',['class' => 'control-label']) !!}
        <div class="controls">
            {!! Form::email('email',null,['class' => 'span6']) !!}
            {!!   $errors->first('email','<span class="help-block">:message</span>') !!}
        </div>

    </div>


     <div class="control-group {{$errors->has('username') ? 'has-error':'' }}">

                    {!! Form::label('username','Username',['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('username',null,['class' => 'span6']) !!}
                        {!!   $errors->first('username','<span class="help-block">:message</span>') !!}
                    </div>

       </div>


    <div class="control-group {{$errors->has('password') ? 'has-error':'' }}">

        {!! Form::label('password','Password',['class' => 'control-label']) !!}
        <div class="controls">
            {!! Form::password('password',null,['class' => 'span6']) !!}
            {!!   $errors->first('password','<span class="help-block">:message</span>') !!}
        </div>

    </div>


    <div class="control-group {{$errors->has('password_confirmation') ? 'has-error':'' }}">

        {!! Form::label('password','Password Confirmation',['class' => 'control-label']) !!}
        <div class="controls">
            {!! Form::password('password_confirmation',null,['class' => 'span6']) !!}
            {!!   $errors->first('password_confirmation','<span class="help-block">:message</span>') !!}
        </div>

    </div>


    <div class="control-group {{$errors->has('phonenumber') ? 'has-error':'' }}">

        {!! Form::label('phonenumber','Phone Number',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('phonenumber',null,['class' => 'span6']) !!}
             {!!   $errors->first('phonenumber','<span class="help-block">:message</span>') !!}
         </div>

     </div>


    <div class="control-group {{$errors->has('restaurantname') ? 'has-error':'' }}">

        {!! Form::label('restaurantname','Restaurant Name',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('restaurantname',null,['class' => 'span6']) !!}
             {!!   $errors->first('restaurantname','<span class="help-block">:message</span>') !!}
         </div>

     </div>


    <div class="control-group {{$errors->has('address') ? 'has-error':'' }}">

        {!! Form::label('address','Restaurant Address',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::textarea('address',null,['class' => 'span6']) !!}
             {!!   $errors->first('address','<span class="help-block">:message</span>') !!}
         </div>

     </div>

   <div class="control-group {{$errors->has('country') ? 'has-error':'' }}">

        {!! Form::label('country','Country',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('country',null,['class' => 'span6']) !!}
             {!!   $errors->first('country','<span class="help-block">:message</span>') !!}
         </div>

     </div>

 <div class="control-group {{$errors->has('province') ? 'has-error':'' }}">

        {!! Form::label('province','Province',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('province',null,['class' => 'span6']) !!}
             {!!   $errors->first('province','<span class="help-block">:message</span>') !!}
         </div>

     </div>

<div class="control-group {{$errors->has('city') ? 'has-error':'' }}">

        {!! Form::label('city','City',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('city',null,['class' => 'span6']) !!}
             {!!   $errors->first('city','<span class="help-block">:message</span>') !!}
         </div>

     </div>





                <!--

<div class="control-group {{$errors->has('logo') ? 'has-error':'' }}">

        {!! Form::label('logo','Logo',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('logo',null,['class' => 'span6']) !!}
             {!!   $errors->first('logo','<span class="help-block">:message</span>') !!}
         </div>

     </div>

 <div class="control-group {{$errors->has('status') ? 'has-error':'' }}">

        {!! Form::label('status','Status',['class' => 'control-label']) !!}
         <div class="controls">
             {!! Form::text('status',null,['class' => 'span6']) !!}
             {!!   $errors->first('status','<span class="help-block">:message</span>') !!}
         </div>

     </div> -->






                <div class="form-actions align-right">


        <div class="form-group ">
            {!! Form::submit('Submit',['class' => 'btn btn-primary']) !!}


        </div>

    </div>

    {!! Form::close() !!}


            </div>
        </div>


    </fieldset>




@stop