<?php

/**
 * HELPERS CONSTANT VARIABLE
 * DEVELOPER: MUHAMMAD RIZKY FIRDAUS
 * DEV DATE: 14/01/2022
 *
 * MODIFY WITH YOUR OWN RISK
 * YOU CAN ADD MORE CONSTANT VARIABLE IN HERE
*/

const DEFAULT_CURRENCY = 'IDR';

const FRONTEND_URL = 'http://localhost:2000'; // change this FE URL with ur own

const MALE_GENDER = 'male';
const FEMALE_GENDER = 'female';

const VERIFICATION_STATUS_PENDING = 0;
const VERIFICATION_STATUS_ACCEPTED = 1;

const USER_MANAGER_TYPE = 0;
const USER_EMPLOYEE_TYPE = 1;

const EMAIL_VERIFICATION_TYPE = 'email';
const PASSWORD_VERIFICATION_TYPE = 'password';

/* ACTIVITIES CONSTANT */
const CREATE_ROLE_PERMISSION = 'Create Role Permission';
const UPDATE_ROLE_PERMISSION = 'Update Role Permission';
const DELETE_ROLE_PERMISSION = 'Delete Role Permission';
const INVITE_NEW_USER = 'Invite New User Manager';
const CHANGE_ROLE_USER = 'Change Role User Manager';
const CANCEL_OR_REMOVE_USER = 'Cancel Or Remove Invite User Manager';

/* WORKSPLACE CONSTANT */
const OFFICE_PLACE = '0';
const REMOTE = '1';

/* ABSENT STATUS */
const ON_TIME = '0';
const LATE = '1';
const ABSENT = '2';

/* CLOCK TYPE */
const CLOCK_IN = '0';
const CLOCK_OUT = '1';

/* EMPLOYEE STATUS */
const EMPLOYEE_INACTIVE = '0';
const EMPLOYEE_ACTIVE = '1';
const EMPLOYEE_PENDING_INVITE = '2';

/** JOB STATUS */
const JOB_STATUS_PERMANENT = '0';
const JOB_STATUS_CONTRACT = '1';
const JOB_STATUS_PROBATION = '2';

/** SALARY TYPE */
const SALARY_INCOME = 'income';
const SALARY_CUTS = 'cut';

/** SALARY PAYMENT_TYPE */
const SALARY_MONTHLY_PAYMENT = '0';
const SALARY_WEEKLEY_PAYMENT = '1';

/** IDENTITY TYPE */
const NATIONAL_ID_IDENTITY = '0';
const PASSPORT_ID_IDENTITY = '1';
const DRIVER_LICENSE_IDENTITY = '2';

/** EMPLOYEE SALARY SETTING */
const OVERTIME = 'overtime';
const WARNING_LETTER = 'warning';

/** LEAVE TYPE */
const PERMIT = 'permit';
const PAID_LEAVE = 'paid_leave';

/** OVERTIME STATUS */
const HAS_BEEN_APPLIED = '0';
const APPROVED = '1';
const REJECTED = '2';

/** PAID LEAVE STATUS */
const LEAVE_PENDING = '0';
const LEAVE_APPROVE = '1';
const LEAVE_REJECTED = '2';

/** PAYROLL PROCESS GENERATE */
const GENERATING = 'generating';
const GENERATED = 'generated';
const FAILED = 'failed';

/** PAYROLL STATUS */
const GENERATED_PAYROLL = '0';
const PARTIALLY_SENDED = '1';
const ALL_SENDED = '3';

/** PAYSLIP EMPLOYEE STATUS */
const GENERATED_PAYSLIP = 'generated';
const SENDED = 'sended';

/** NOTIFICATION TYPE */
const NOTIF_EMPLOYEE = 'employee';
const NOTIF_MANAGER = 'manager';

/** EXCEL TASK STATUS */
const EXCEL_PENDING = 'pending';
const EXCEL_PROCESS = 'processing';
const EXCEL_FINISH = 'finished';
const EXCEL_FAILED = 'failed';

/** EXCEL TASK TYPE */
const EXCEL_EXPORT = 'export';
const EXCEL_IMPORt = 'import';

/** HEADBRANCH CHANGE TYPE */
const ASSIGN_HEADBRANCH = 'assign';
const CHANGE_HEADBRANCH = 'change';