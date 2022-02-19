@include('email.header_new')

<p>Hello, <?php echo $data->name; ?></p>
<p>   Your {{ allSetting()['company_name'] }} email verification code is {{$key}}.
    <br>
</p>
<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{ allSetting()['company_name'] }}
</p>
@include('email.footer_new')