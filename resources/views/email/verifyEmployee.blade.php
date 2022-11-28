@include('email.header_new')
<h3>{{__('Hello')}}, {{ $user['name']  }}</h3>
<p>
    {{__('You have been invited as Employee ')}} in {{ allCompanySetting('company_name')}} {{__('. Please click on the verification link below to verified Your Account. This is Your credentials:')}}
</p>
<p>
    {{__('Email : ')}} {{$user['email']}}
</p>
<p>
    {{__('Password : ')}} {{$user['password']}}
</p>
{{-- <p>
    
    <a style="text-decoration: none;background: #4A9A4D;color: #fff;padding: 5px 10px;border-radius: 3px;" href="{{FRONTEND_URL.'?token='.$user['key'].'&email='.$user['email']}}">{{__('Verify')}}</a>
</p> --}}
<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{ allCompanySetting('company_name')}}
</p>
@include('email.footer_new')