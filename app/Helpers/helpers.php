<?php

/**
 !!! 
    HELPERS SERVICE APPLICATION
    DEVELOPER: MUHAMMAD RIZKY FIRDAUS
    DEV DATE: 14/01/2022
    MODIFY WITH YOUR OWN RISK
    YOU CAN ADD MORE HELPERS FUNCTION IN HERE
 !!!
*/

use App\Models\CompanyBranch;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;


/**
 * Branch ID for creating any data that related to company branch
 * @param bool $isSuperAdmin - checking if current user is superadmin or else
 * @param int $branchId - id from company branch. If null, then this param will set to central branch id but this central branch id only will be used if user is superadmin, else the branch id will set according the header branch selected from request
 * @return int
 */
function branchIdForCreateData($isSuperAdmin, $branchId = null)
{
    if ($isSuperAdmin) {
        if ($branchId != null) {
            $branch = $branchId;
        } else {
            $branch = CompanyBranch::where([
                'is_centered'   => 1
            ])->first()->id;
        }
    } else {
        $branch = branchSelected('sanctum:manager')->pivot->branch_id;
    }

    return $branch;
}

/**
 * Detect current user is Superadmin
 * 
 * @return bool
 */
function isSuperAdmin()
{
    if (Auth::guard('sanctum:manager')->check()) {
        if (Auth::guard('sanctum:manager')->user()->branch == null) {
            return true;
        }
        return false;
    }
    return false;
}

/**
 * Get current role name user
 * 
 * @param string $guard_name
 * @return string
 */
function currentUserRole($guard_name)
{
    if (Auth::guard($guard_name)->check()) {
        $user = Auth::guard($guard_name)->user();
        if ($user->branch == null) {
            return $user->roles()->first()->name;
        } else {
            return $user->branchAssign()->first()->pivot->roles()->first()->name;
        }
    }

    return '';
}
/**
 * Detect current branch user
 * 
 * @param string $guard_name - Is the Auth Guard for the current session (manager or employee)
 * @return Object
 */
function branchSelected(string $guard_name)
{
    return Request::header('Branch-Selected') ?
        Auth::guard($guard_name)->user()->branchAssign()->find(Request::header('Branch-Selected'))
        : null;
}

/**
 * Generate random string for random password
 * @return String
 */
function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

/**
 * Get All Company Setting
 * @param array $array
 * @return Boolean
 */
function allCompanySetting($array = null)
{
    if (!isset($array[0])) {
        $allSettings = CompanySetting::get();
        if ($allSettings) {
            $output = [];
            foreach ($allSettings as $setting) {
                $output[$setting->key] = [
                    'setting_name'  => $setting->setting_name,
                    'setting_value' => $setting->value
                ];
            }
            return $output;
        }
        return false;
    } else if (is_array($array)) {
        $allSettings = CompanySetting::where('setting_key', $array)->get();
        if ($allSettings) {
            $output = [];
            foreach ($$allSettings as $setting) {
                $output[$setting->key] = [
                    'setting_name'  => $setting->setting_name,
                    'setting_value' => $setting->value
                ];
            }
            return $output;
        }
        return false;
    } else {
        $allsettings = CompanySetting::where(['setting_key' => $array])->first();
        if ($allsettings) {
            $output = $allsettings->value;
            return $output;
        }
        return false;
    }
}

/**
 * Get One Company Setting
 * @param string $keys
 * @return string | null
 */
function settings($keys)
{
    $setting = CompanySetting::where('setting_key', $keys)->first();
    return empty($setting) ? null : $setting->value;
}

/**
 * Generate image name
 * @param String $extension
 * @return preg_replace random name
 */
function generateImageName($extension)
{
    return preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime()) . '.' . $extension;
}

/**
 * Storing images to strage
 * @param Path to store
 * @param File
 * @return String file name
 */
function storeImages($path, $file)
{
    $extension = $file->getClientOriginalExtension();
    $imageName = generateImageName($extension);
    $file->storeAs(
        $path,
        $imageName
    );
    return $imageName;
}

/**
 * Count Data From Array
 * @param array $array
 * @param Any $key from array
 * @param Any $value from array
 * @return int
 */
function arrFilterCount(array $array, $key, $value)
{
    $cnt = count(array_filter($array, function ($element) use ($key, $value) {
        return $element[$key] == $value;
    }));

    return $cnt;
}

/**
 * Generate Verification Key
 * @return String
 */
function generate_email_verification_key()
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($pool, 5)), 0, 30);
}

/**
 * It returns the number of working days between two dates, excluding holidays.
 * 
 * @param string $from - The start date of the period.
 * @param string $to - The date you want to calculate the difference to.
 * @param array $holidays - array of dates to exclude from the count
 * 
 * @return integer The number of working days between two dates.
 */
function workingDays($from, $to, $holidays)
{
    // DAYS OF WORKING (MONDAY TO FRIDAY)
    $workingDays = [1, 2, 3, 4, 5]; 

    $from = new DateTime($from);
    $to = new DateTime($to);
    $to->modify('+1 day');
    $interval = new DateInterval('P1D');
    $periods = new DatePeriod($from, $interval, $to);

    $days = 0;
    foreach ($periods as $period) {
        if (!in_array($period->format('N'), $workingDays)) continue;
        if (in_array($period->format('Y-m-d'), $holidays)) continue;
        if (in_array($period->format('*-m-d'), $holidays)) continue;
        $days++;
    }
    return $days;
}

/**
 * GENERATING RUPIAH FORMAT
 * @param integer $number
 * @return string
 */
function rupiah($number)
{
	$result = "Rp " . number_format($number,0,',',',');
	return $result;
}