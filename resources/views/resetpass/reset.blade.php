@if (session('status'))
    <script>
        $.jGrowl('{{ session('status') }}', { sticky: true, theme: 'growl-success', header: '{{ trans("admin.success") }}!' });
    </script>
@endif

{!! Form::open(array('url' => 'api/resetpassword','class'=>'form')) !!}

<input type="text" name="token" value="{{Request::segment(4)}}">

<?php $request = Request::segment(3);
$email = urldecode($request);
?>
<div>
    <input type="text" name="email" value="{{$email}}">
</div>

<div>
    <input type="password" name="password">
</div>

<div>
    <input type="password" name="password_confirmation">
</div>

<div>
    <button type="submit">
        Reset Password
    </button>
</div>
</form>