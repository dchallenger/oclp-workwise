<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//leaves
//$route['forms/leaves/ajax_save'] = 'firstbalfour/forms/firstbalfour_leaves/ajax_save';
$route['forms/leaves'] = 'firstbalfour/forms/firstbalfour_leaves/index';
$route['forms/leaves/get_approvers'] = 'firstbalfour/forms/firstbalfour_leaves/get_approvers';
$route['forms/obt/get_sub_by_project'] = 'firstbalfour/forms/firstbalfour_obt/get_sub_by_project';

//leave balance
$route['dtr/leave_balance'] = 'firstbalfour/dtr/firstbalfour_leave_balance/index';
$route['dtr/leave_balance/listview'] = 'firstbalfour/dtr/firstbalfour_leave_balance/listview';

//periods
$route['dtr/periods/get_dropdown_options'] = 'firstbalfour/dtr/firstbalfour_periods/get_dropdown_options';
$route['dtr/periods/process'] = 'firstbalfour/dtr/firstbalfour_periods/process';
$route['dtr/periods/getprogress'] = 'firstbalfour/dtr/firstbalfour_periods/getprogress';

//recruitment
// $route['recruitment/annual_manpower_planning/detail'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/detail';
$route['recruitment/annual_manpower_planning/get_amp_user_type'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_amp_user_type';
$route['recruitment/annual_manpower_planning/get_category_value'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_category_value';
$route['recruitment/annual_manpower_planning/get_position_per_category'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_position_per_category';
$route['recruitment/annual_manpower_planning/get_position_per_category_edit'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_position_per_category_edit';
// $route['recruitment/annual_manpower_planning/get_existing_headcount'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_existing_headcount';
$route['recruitment/annual_manpower_planning/get_positions'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_positions';
$route['recruitment/annual_manpower_planning/get_form_existing_position'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_form_existing_position';
$route['recruitment/annual_manpower_planning/get_previous_headcount'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/get_previous_headcount';
// $route['recruitment/annual_manpower_planning/validation'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/validation';
// $route['recruitment/annual_manpower_planning/ajax_save'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/ajax_save';
// $route['recruitment/annual_manpower_planning/excel_export'] = 'firstbalfour/recruitment/firstbalfour_annual_manpower_planning/excel_export';

$route['recruitment/manpower_loading_schedule/get_position_per_project'] = 'firstbalfour/recruitment/firstbalfour_manpower_loading_schedule/get_position_per_project';
$route['recruitment/manpower_loading_schedule/get_positions'] = 'firstbalfour/recruitment/firstbalfour_manpower_loading_schedule/get_positions';
$route['recruitment/manpower_loading_schedule/get_form_position'] = 'firstbalfour/recruitment/firstbalfour_manpower_loading_schedule/get_form_position';
$route['recruitment/manpower_loading_schedule/get_division_head'] = 'firstbalfour/recruitment/firstbalfour_manpower_loading_schedule/get_division_head';

$route['recruitment/manpower/get_company_positions'] = 'firstbalfour/recruitment/firstbalfour_manpower/get_company_positions';
$route['recruitment/candidates/qualify_candidate_form'] = 'firstbalfour/recruitment/firstbalfour_candidates/qualify_candidate_form';
$route['recruitment/candidates/save_qualified_candidate'] = 'firstbalfour/recruitment/firstbalfour_candidates/save_qualified_candidate';
$route['recruitment/candidate_schedule/listview'] = 'firstbalfour/recruitment/firstbalfour_candidate_schedule/listview';
$route['recruitment/applicants/listview'] = 'firstbalfour/recruitment/firstbalfour_applicants/listview';

//employee
$route['employees'] = 'firstbalfour/firstbalfour_employees/index';
$route['employees/listview'] = 'firstbalfour/firstbalfour_employees/listview';
$route['employees/get_query_fields2'] = 'firstbalfour/firstbalfour_employees/get_query_fields2';

$route['employee/dtr/listview'] = 'firstbalfour/employee/firstbalfour_dtr/listview';

//employee update
$route['employee/employee_update/send_email'] = 'firstbalfour/employee/firstbalfour_employee_update/send_email';
$route['employee/employee_update/ajax_save'] = 'firstbalfour/employee/firstbalfour_employee_update/ajax_save';
$route['employee/employee_update/change_status'] = 'firstbalfour/employee/firstbalfour_employee_update/change_status';

$route['employee/movement/get_employee'] = 'firstbalfour/employee/firstbalfour_movement/get_employee';