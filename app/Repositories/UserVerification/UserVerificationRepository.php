<?php

namespace App\Repositories\UserVerification;

use App\Models\UserVerification;
use Carbon\Carbon;

class UserVerificationRepository implements UserVerificationInterface
{
    /**
    * @var App\Models\UserVerification
    */
    protected $model;

    public function __construct(UserVerification $model)
    {
      $this->model = $model;
    }

    /**
     * Generate Code Email Verification
     * @param String $model
     * @param Int $modelId
     * @param String $code
     * @return App\Models\UserVerification
     */
    public function generateEmailVerification($model, $modelId, $code)
    {
      return $this->model->create([
        'model_type'        => $model,
        'model_id'          => $modelId,
        'code'              => $code,
        'expired_at'        => date('Y-m-d', strtotime('+1 days')),
        'verification_type' => EMAIL_VERIFICATION_TYPE,
      ]);
    }

    /**
     * Generate Code Forgot Password Verification
     * @param String $model
     * @param Int $modelId
     * @param String $code
     * @return App\Models\UserVerification
     */
    public function generatePasswordVerification($model, $modelId, $code)
    {
      return $this->model->create([
        'model_type'        => $model,
        'model_id'          => $modelId,
        'code'              => $code,
        'expired_at'        => date('Y-m-d', strtotime('+1 days')),
        'verification_type' => PASSWORD_VERIFICATION_TYPE,
      ]);
    }

    /**
     * Check if verification code is exists or not expired
     * @param String $code
     * @param String $type (Verification Type)
     * @return App\Models\UserVerification
    */
    public function isExistsVerificationCode($code, $type)
    {
      return $this->model
      ->where([
        'code'    => $code,
        'type'    => $type,
        'status'  => VERIFICATION_STATUS_PENDING
      ])->whereDate('expired_at', '>', Carbon::now())->exists();
    }

    /**
     * Delete Verification Email Code
     * @param String $model
     * @param Int $modelId
     * @return App\Models\UserVerification
    */
    public function deleteVerificationEmail($model, $modelId) 
    {
      return $this->model->where([
        'model_type'          => $model,
        'model_id'            => $modelId,
        'verification_type'   => EMAIL_VERIFICATION_TYPE,
      ])->first()->delete();
    }

    /**
     * Delete Verification Forgot Password Code
     * @param String $model
     * @param Int $modelId
     * @return App\Models\UserVerification
    */
    public function deleteVerificationForgotPassword($model, $modelId) 
    {
      return $this->model->where([
        'model_type'          => $model,
        'model_id'            => $modelId,
        'verification_type'   => PASSWORD_VERIFICATION_TYPE,
      ])->first()->delete();
    }

    /**
     * Update Code Email Verification
     * @param String $model
     * @param Int $modelId
     * @param String $type (Verification Type)
     * @param String $code
     * @return App\Models\UserVerification
     */
    public function updateVerificationCode($model, $modelId, $type, $code)
    {
      $data = $this->model->where([
        'model_type'          => $model,
        'model_id'            => $modelId,
        'verification_type'   => $type,
        'code'                => $code,
      ]);
      return $data->update([
        'status' => 1
      ]);
    }
}