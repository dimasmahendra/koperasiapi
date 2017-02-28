
{!! Form::open(['route'=>'password.reset', 'class'=>'form-horizontal']) !!}
    {!! csrf_field() !!}
    <input type="hidden" name="token" value="">

    <div>
        <input type="email" name="email" value="">
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