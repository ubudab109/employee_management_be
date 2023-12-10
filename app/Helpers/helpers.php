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
use App\Models\UserManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

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
 * @param mixed $key from array
 * @param mixed $value from array
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
 * @return string
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
    $result = "Rp " . number_format($number, 0, ',', ',');
    return $result;
}

/**
 * It checks if the user has at least one permission assigned to the scope
 * 
 * @param UserManager $user The user manager object
 * @param string $scopeName The name of the scope you want to check.
 */
function isScopeAccess(UserManager $user, $scopeName)
{
    $scope = DB::table('permission_scope')->where('name', $scopeName)->first();
    $permissions = DB::table('permissions')->where('scope_id', $scope->id)->get();
    $userBranch = $user->branch()->with('branch')->first();
    if ($userBranch) {
        // check if current scope have permission, if not, then is_scope_access is false
        if (count($permissions) < 1) {
            return false;
        }
        $dataScope['permissions'] = array();
        /* GET PERMISSIONS FROM PERMISSION SCOPE */
        foreach ($permissions as $permission) {
            if ($userBranch->roles()->first()) {
                $prm['is_assigned']     = $userBranch->roles()->first()->hasPermissionTo($permission->name) ? true : false;
            } else {
                $prm['is_assigned']     = false;
            }
            $dataScope['permissions'][] = $prm;
        }
        // Count how many permission in current scope was assigned. If at least have one, then key 'is_scope_access' was true and if not then false
        return arrFilterCount($dataScope['permissions'], 'is_assigned', true) > 0;
    } else {
        return false;
    }
}

/**
 * The function calculates the total amount of overtime pay based on the gross salary and number of
 * hours worked.
 * 
 * @param double $grossSalary The gross salary of an employee.
 * @param double $takenHour The number of hours worked overtime.
 * 
 * @return integer total amount of overtime pay for a given gross salary and number of hours worked. The
 * overtime pay is calculated based on a constant formula and a temporary formula for the first and
 * subsequent hours of overtime. The function returns the sum of all the overtime pay amounts.
 */
function getTotalAmountOvertime($grossSalary, $takenHour)
{
    $constantFormula = 1 / 173;
    $overtimePay = $grossSalary * $constantFormula;
    /**
     !!!
      - TEMPORARY TO SAVE A OVERTIME WAGE PER HOUR
      - IF IN FIRST HOUR THEN THE FORMULA IS LIKE THIS (1.5 * $overtimePay)
      - THEN IN THE NEXT HOUR IS (2.5 * $overtimePay)
      - THIS CALCULATION IS REFERENCE BY DEPNAKER
     !!!
     */
    $totalAmountOvertime = [];
    for ($i = 1; $i <= $takenHour; $i++) {
        if ($i == 1) {
            $totalAmountOvertime[] = 1.5 * $overtimePay;
        } else {
            $totalAmountOvertime[] = 2 * $overtimePay;
        }
    }
    $total = array_sum($totalAmountOvertime);
    return floor($total);
}

/**
 * The function "handle" takes a closure as a parameter and executes it.
 * 
 * @param Closure closure The parameter `` is a closure, which is a type of anonymous function
 * in PHP. It can be assigned to a variable and passed around as a parameter to other functions. In
 * this case, the `handle` function takes a closure as its parameter and then immediately calls it
 * using the `()`
 */
function handle(Closure $closure) 
{
    $closure();
}

/**
 * Default response error for spesific environment
 * @param string|null $text - text error
 * @return string
 */
function defaultResponseError($text = null) 
{
    if (config('app.env') !== 'development') {
        Log::info($text);
        return 'Internal Server Error';
    }

    return $text ?? 'Internal Server Error';
}

/**
 * Format Excel Cell's Value
 *
 * @param string|RichText $text
 * @param string $formattedText
 * @param array $format
 * @return RichText
 * @throws Exception
 */
function formatValue($text, $formattedText, $format = [])
{
    if ($text instanceof RichText) {
        $value = $text;
    } else {
        // if $text not RichText, change it to RichText
        $value = new RichText();
        $value->createText($text);
    }

    // add formatted text behind the original text
    $objPayable = $value->createTextRun($formattedText);

    if (array_key_exists('bold', $format) && $format['bold']) {
        $objPayable->getFont()->setBold(true);
    }
    if (array_key_exists('color', $format) && !empty($format['color'])) {
        $objPayable->getFont()->setColor(new Color($format['color']));
    }

    return $value;
}
