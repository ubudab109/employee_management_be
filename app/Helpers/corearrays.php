<?php

/**
 * VALUE CORE ARRAY FROM CONSTANT VARIABLE
 * DEVELOPER: MUHAMMAD RIZKY FIRDAUS
 * DEV DATE: 14/01/2022
 *
 * MODIFY WITH YOUR OWN RISK
 * YOU CAN ADD MORE CORE ARRAYS FUNCTION IN HERE
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