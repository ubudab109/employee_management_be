<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait CurrentBranch {
  protected static function bootMultitenant()
  {
    if (Auth::guard('sanctum:manager')->check()) {
      $guardName = 'sanctum:manager';
    } else if (Auth::guard('sanctum:employee')->check()) {
      $guardName = 'sanctum:employee';
    }
    $branchId = branchSelected($guardName);
    
    static::creating(function ($model) use ($branchId) {
      $model->branch_id = $branchId;
    });

    static::addGlobalScope('branch_id', function (Builder $builder) use ($branchId) {
      if ($branchId != null) {
        $builder->where('branch_id', $branchId);
      }
    });
  }
}