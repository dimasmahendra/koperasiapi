<div class="control-group {{$errors->has('countrylang_id') ? 'has-error':'' }}">
    {!! Form::label('countrylang_id', trans('admin.countrylang'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::select('countrylang_id', $countrylang, null,['class' => 'span4']) !!}
        {!!   $errors->first('countrylang_id','<span class="help-block">:message</span>') !!}
    </div>
</div>

<div class="control-group {{$errors->has('title') ? 'has-error':'' }}">
    {!! Form::label('title', trans('admin.title'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::text('title',null,['class' => 'span6']) !!}
        {!!   $errors->first('title','<span class="help-block">:message</span>') !!}
    </div>
</div>
<div class="control-group {{$errors->has('text') ? 'has-error':'' }}">
    {!! Form::label('text', trans('admin.descriptions'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::textarea('text',null,['class' => 'span6']) !!}
        {!!   $errors->first('text','<span class="help-block">:message</span>') !!}
    </div>
</div>
<div class="control-group {{$errors->has('img') ? 'has-error':'' }}">
    {!! Form::label('img', trans('admin.image'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::file('img',null,['class' => 'span6']) !!}
        {!!   $errors->first('img','<span class="help-block">:message</span>') !!}
        @if($type=='edit')
        <br><br>
			@if($training->img==''||$training->img=='no_image.png')
				<img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
			@else
				<img src="{{ url('/public/images/training/thumb_'.$training->img)  }}" width="90" height="90">
			@endif
        @endif
    </div>
</div>

<div class="control-group {{$errors->has('logo1') ? 'has-error':'' }}">
    {!! Form::label('logo1', trans('admin.logo1'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::file('logo1',null,['class' => 'span6']) !!}
        {!!   $errors->first('logo1','<span class="help-block">:message</span>') !!}
        @if($type=='edit')
            <br><br>
            @if($training->logo1==''||$training->logo1=='no_image.png')
                <img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
            @else
                <img src="{{ url('/public/images/training/thumb_'.$training->logo1)  }}" width="90" height="90">
            @endif
        @endif
    </div>
</div>

<div class="control-group {{$errors->has('logo2') ? 'has-error':'' }}">
    {!! Form::label('logo2', trans('admin.logo2'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::file('logo2',null,['class' => 'span6']) !!}
        {!!   $errors->first('logo2','<span class="help-block">:message</span>') !!}
        @if($type=='edit')
            <br><br>
            @if($training->logo2==''||$training->logo2=='no_image.png')
                <img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
            @else
                <img src="{{ url('/public/images/training/thumb_'.$training->logo2)  }}" width="90" height="90">
            @endif
        @endif
    </div>
</div>

<div class="control-group {{$errors->has('logo3') ? 'has-error':'' }}">
    {!! Form::label('logo3', trans('admin.logo3'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::file('logo3',null,['class' => 'span6']) !!}
        {!!   $errors->first('logo3','<span class="help-block">:message</span>') !!}
        @if($type=='edit')
            <br><br>
            @if($training->logo3==''||$training->logo3=='no_image.png')
                <img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
            @else
                <img src="{{ url('/public/images/training/thumb_'.$training->logo3)  }}" width="90" height="90">
            @endif
        @endif
    </div>
</div>


<div class="form-actions align-right">
    <div class="form-group ">
        {!! Form::submit(trans('admin.submit'),['class' => 'btn btn-primary']) !!}
    </div>
</div>