<?php

/**
 * VALUE CORE ARRAY FROM CONSTANT VARIABLE
 * DEVELOPER: MUHAMMAD RIZKY FIRDAUS
 * DEV DATE: 14/01/2022
 *
 * MODIFY WITH YOUR OWN RISK
 * YOU CAN ADD MORE CORE ARRAYS FUNCTION IN HERE
*/

use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeReimburshment;
use App\Models\EmployeeWarningLetter;
use App\Models\Payroll;
use Illuminate\Support\Facades\Log;

/**
 * It returns an array of key-value pairs, where the key is the input and the value is the output.
 * 
 * @param $string input The value you want to get the name of.
 * 
 * @return array of key-value pairs.
 */
function getWorkPlaceName($input = null) {
  $data = [
    OFFICE_PLACE => __("On Office"),
    REMOTE      => __("Remote"),
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}


/**
 * It returns an array of status absent
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function getStatusAbsent($input = null) {
  $data = [
    ON_TIME => __("On Time"),
    LATE    => __("Late"),
    ABSENT  => __("Absent"),
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a color based on the input.
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function badgeWorkPlaces($input = null) {
  $data = [
    OFFICE_PLACE => "#DAE4FF",
    REMOTE      => "#FFE8FF",
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a color based on the input. If no input is given, it returns all the colors.
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function textColorWorkSpaces($input = null) {
  $data = [
    OFFICE_PLACE => "#1959FF",
    REMOTE      => "#FA00FF",
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a color code based on the input.
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function badgeStatusAbsen($input = null) {
  $data = [
    ON_TIME => "#BBFFCC",
    LATE    => "#FFF6D5",
    ABSENT  => "#FFD9D9",
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a color code based on the input
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function textColorStatusAbsent($input = null) {
  $data = [
    ON_TIME => "#008836",
    LATE    => "#FFC900",
    ABSENT  => "#FF1111",
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns the name of the job status if the input is not null, otherwise it returns all the job
 * status names.
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function getJobStatusName($input = null) {
  $data = [
    JOB_STATUS_PERMANENT  => 'Permanent',
    JOB_STATUS_CONTRACT   => 'Contract',
    JOB_STATUS_PROBATION  => 'Probation'
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns an array of identity types if no input is given, or the name of the identity type if the
 * input is given.
 * 
 * @param string input The value of the input.
 * 
 * @return array of key-value pairs.
 */
function getIdentityTypeName($input = null) {
  $data = [
    NATIONAL_ID_IDENTITY => 'National ID Card (KTP)',
    PASSPORT_ID_IDENTITY => 'Passport',
    DRIVER_LICENSE_IDENTITY => 'Driver License (SIM)'
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a string based on the input
 * 
 * @param string input The value of the status.
 * 
 * @return array of key-value pairs.
 */
function getStatusNameOvertime($input = null)
{
  $data = [
    HAS_BEEN_APPLIED => 'Has Been Applied',
    APPROVED => 'Approved',
    REJECTED => 'Rejected',
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns a string color based on the input
 * 
 * @param string input The value of the status.
 * 
 * @return array of key-value pairs.
 */
function getStatusNameColor($input = null)
{
  $data = [
    '0' => '#FCB756',
    '1' => '#19C8FF',
    '2' => '#FF1111',
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns an array of leave status names, or a single leave status name if you pass in a leave
 * status ID
 * 
 * @param string input The value you want to get the name of.
 * 
 * @return array array of key-value pairs.
 */
function getGlobalStatusEnum($input = null)
{
  $data = [
    '0'  => 'Pending',
    '1'  => 'Approved',
    '2' => 'Rejected',
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns an array of leave status names, or a single leave status name if you pass in a leave
 * status ID
 * 
 * @param string input The value you want to get the name of.
 * 
 * @return array array of key-value pairs.
 */
function getLeaveStatusName($input = null)
{
  $data = [
    LEAVE_PENDING  => 'Pending',
    LEAVE_APPROVE  => 'Approved',
    LEAVE_REJECTED => 'Rejected',
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * GET GLOBAL STATUS NAME ATTRIBUTE
 * @param string $input
 * @return array
 */
function getStatusNameAttribute($input = null) {
  $data = [
    '0' => 'Pending',
    '1' => 'Approved',
    '2' => 'Rejected',
  ];

  if ($input != null) {
    return $data[$input];
  }

  return $data;
}

/**
 * It returns an array of month names, with the key being the month number and the value being the
 * month name.
 * 
 * @param integer input The month number you want to get the name of.
 * 
 * @return array array of months.
 */
function getMonthName($input = null)
{
  $month = [
    1  => 'JANUARY',
    2  => 'FEBRUARY',
    3  => 'MARCH',
    4  => 'APRIL',
    5  => 'MAY',
    6  => 'JUNE',
    7  => 'JULY',
    8  => 'AUGUST',
    9  => 'SEPTEMBER',
    10 => 'OCTOBER',
    11 => 'NOVEMBER',
    12 => 'DECEMBER',
  ];

  if ($input != null) {
    return $month[$input];
  }

  return $month;
}

/**
 * It returns the icon path based on the class name and type
 * 
 * @param string $class The class name of the notification
 * @param string $type The type of notification.
 * 
 * @return array
 */
function getNotifIcon($class = null, $type = null)
{
  $icons = [
    Payroll::class => asset('notif_icon/Paper.png'),
    EmployeeReimburshment::class => asset('notif_icon/File.png'),
    EmployeeOvertime::class => asset('notif_icon/Tumer.png'),
    EmployeeLeave::class => [
      PAID_LEAVE => asset('notif_icon/Calendar.png'),
      PERMIT => asset('notif_icon/Date.png')
    ],
    EmployeeWarningLetter::class => asset('notif_icon/Warning.png')
  ];

  if (!is_null($class)) {
    $iconClass = $icons[$class];
    if (!is_null($type)) {
      $icon = $iconClass[$type];
    } else {
      $icon = $iconClass;
    }

    return $icon;
  }
  return $icons;
}


/**
 * It returns a URL based on the class name
 * 
 * @param string $class The class name of the model
 * @param integer $idEmployee the id of the employee
 */
function getFeEndpointNotification($class = null, $idEmployee = null)
{
  $url = [
    Payroll::class => '/finance',
    EmployeeReimburshment::class => '/finance',
    EmployeeOvertime::class => '/time-management',
    EmployeeAttendance::class => '/attendance-management',
    EmployeeLeave::class => '/employee/detail/'.$idEmployee,
    EmployeeWarningLetter::class => '/employee/detail/'.$idEmployee
  ];

  if (!is_null($class)) {
    return $url[$class];
  }

  return $url;
}

/**
 * It returns the value of the key that matches the value of the parameter passed to the function
 * Get the permission scope based on class
 * 
 * @param string $class The class name of the model you want to check.
 * 
 * @return string The value of the key in the array.
 */
function getScopePermissionClass($class)
{
  $permissionScope = [
    EmployeeAttendance::class => 'Attendance',
    Payroll::class => 'Payroll',
    EmployeeOvertime::class => 'Employee Overtime',
    EmployeeLeave::class => 'Employee Paid Leave',
    EmployeeReimburshment::class => 'Employee Reimbursement',
  ];

  return $permissionScope[$class];
}