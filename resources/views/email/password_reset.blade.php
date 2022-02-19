@include('email.header_new')
<h3>{{__('Hello')}}, {{isset($user) ? $user->name : ''}}</h3>
<p>
    You are receiving this email because we received a password reset request for your account.
    Please use the code below to reset your password.
</p>
<a style="text-decoration: none;background: #4A9A4D;color: #fff;padding: 5px 10px;border-radius: 3px;" href="{{route('getForgotPasswordLink.process').'?token='.$key.'&idsiid='.$user->uuid}}">{{__('Verify')}}</a>
<p>You can change your password with this link :: <a href="{{route('getForgotPasswordLink.process').'?token='.$key.'&idsiid='.$user->uuid}}">Click Here</a></p>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{ allSetting()['company_name'] }}
</p>
@include('email.footer_new')