<?php

namespace App\Repositories\UserVerification;

interface UserVerificationInterface
{
  public function generateEmailVerification($model, $modelId, $code);
  public function generatePasswordVerification($model, $modelId, $code);
  public function isExistsVerificationCode($code, $type);
  public function deleteVerificationEmail($model, $modelId);
  public function deleteVerificationForgotPassword($model, $modelId);
  public function updateVerificationCode($model, $modelId, $type, $code);
}