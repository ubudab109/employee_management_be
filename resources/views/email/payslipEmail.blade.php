@include('email.header_new')
<h3>{{__('Hello')}}, {{ $user['name']  }}</h3>
<p>
  Your Payslip is ready. Find the attachment below to download your Payslip or You can click the link below to open the document.
</p>

<a href="{{$user['url']}}" target="_blank">URL Document</a>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{ allCompanySetting('company_name')}}
</p>
@include('email.footer_new')