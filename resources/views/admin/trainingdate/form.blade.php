<div class="control-group {{$errors->has('training_id') ? 'has-error':'' }}">
    {!! Form::label('training_id', trans('admin.trainingtitle'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::select('training_id', $training, null,['class' => 'span4']) !!}
        {!!   $errors->first('training_id','<span class="help-block">:message</span>') !!}
    </div>
</div>

<div class="control-group {{$errors->has('date') ? 'has-error':'' }}">
    {!! Form::label('date', trans('admin.date'),['class' => 'control-label']) !!}
    <div class="controls">
        {!! Form::date('date',null,['class' => 'span6']) !!}
        {!!   $errors->first('date','<span class="help-block">:message</span>') !!}
    </div>
</div>


<div class="form-actions align-right">
    <div class="form-group ">
        {!! Form::submit(trans('admin.submit'),['class' => 'btn btn-primary']) !!}
    </div>
</div>