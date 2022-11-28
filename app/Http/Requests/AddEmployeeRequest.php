<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstname'                         => 'required',
            'lastname'                          => '',
            'email'                             => 'required|email|unique:users,email',
            'mobile_phone'                      => 'required',
            'phone_number'                      => '',
            'pob'                               => 'required',
            'bob'                               => 'required',
            'gender'                            => 'required',
            'marital_status'                    => '',
            'blood_type'                        => '',
            'identity_type'                     => 'required',
            'identity_number'                   => 'required',
            'ientity_expired'                   => '',
            'postal_code'                       => 'required',
            'citizent_address'                  => 'required',
            'resident_address'                  => 'required',
            'nip'                               => 'required|unique:users,nip',
            'job_level'                         => 'required',
            'job_status'                        => 'required',
            'department'                        => 'required',
            'join_date'                         => 'required',
            'end_date'                          => 'required_if:job_status,!=,0',
            'salary'                            => 'array',
            'cuts'                              => 'array',
            'bank_name'                         => 'required',
            'account_name'                      => 'required',
            'holder'                            => 'required',
            'division_id'                       => 'required',
            'payment_date'                      => 'required',
        ];
    }
}
