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

<div class="control-group {{$errors->has('benefit') ? 'has-error':'' }}">
    {!! Form::label('benefit', trans('admin.benefit'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::textarea('benefit',null,['class' => 'span6']) !!}
        {!!   $errors->first('benefit','<span class="help-block">:message</span>') !!}
    </div>
</div>

<div class="control-group {{$errors->has('img') ? 'has-error':'' }}">
    {!! Form::label('img', trans('admin.image'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::file('img',null,['class' => 'span6']) !!}
        {!!   $errors->first('img','<span class="help-block">:message</span>') !!}
        @if($type=='edit')
        <br><br>
			@if($promotion->img==''||$promotion->img=='no_image.png')
				<img src="{{ url('/public/images/no_image.png')  }}" width="90" height="90">
			@else
				<img src="{{ url('/public/images/promotion/thumb_'.$promotion->img)  }}" width="90" height="90">
			@endif
        @endif
    </div>
</div>
<div class="form-actions align-right">
    <div class="form-group ">
        {!! Form::submit(trans('admin.submit'),['class' => 'btn btn-primary']) !!}
    </div>
</div>