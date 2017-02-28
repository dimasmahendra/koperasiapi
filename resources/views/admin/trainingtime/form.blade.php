<div class="control-group {{$errors->has('training_id') ? 'has-error':'' }}">
    {!! Form::label('training_id', trans('admin.trainingtitle'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::select('training_id', $training, null,['class' => 'span4']) !!}
        {!!   $errors->first('training_id','<span class="help-block">:message</span>') !!}
    </div>
</div>

<div class="control-group {{$errors->has('trainingdate_id') ? 'has-error':'' }}">
    {!! Form::label('trainingdate_id', trans('admin.date'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::select('trainingdate_id',$trainingdate,null,['class' => 'span6']) !!}
        {!!   $errors->first('trainingdate_id','<span class="help-block">:message</span>') !!}
    </div>
</div>

<div class="control-group {{$errors->has('time') ? 'has-error':'' }}">
    {!! Form::label('time', trans('admin.time'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::text('time',null,['class' => 'span6']) !!}
        {!!   $errors->first('time','<span class="help-block">:message</span>') !!}
    </div>
</div>



<div class="form-actions align-right">
    <div class="form-group ">
        {!! Form::submit(trans('admin.submit'),['class' => 'btn btn-primary']) !!}
    </div>
</div>