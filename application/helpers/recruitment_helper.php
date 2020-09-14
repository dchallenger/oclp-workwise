<?php
if ( !defined( 'BASEPATH' ) )
	exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

if ( !function_exists( 'get_candidate_tab_count' ) ) {

	/**
	 * Returns the number of specified candidate status.
	 *
	 * @param null
	 *
	 * @return int
	 */
	function get_candidate_tab_count() {
		$ci =& get_instance();

		$array_count = array();
		$mrf_count = 0;
		$schedule_count = 0;
		$result_count = 0;
		$joboffer_count = 0;
		$contractsign_count = 0;
		$others_count = 0;
		$archive_count = 0;
		$bcheck_count = 0;
		//$ci->db->where('status','Approved');
		//$ci->db->or_where('status','In-Process');
		$ci->db->where('deleted','0');
		// 
		if (CLIENT_DIR == 'firstbalfour') {
			$ci->db->where_not_in('status', array("Declined","Cancelled", "Closed"));
		}else{
			$ci->db->where('( status = "Approved" OR status = "In-Process" )');
		}
		$mrf = $ci->db->get('recruitment_manpower');

		if ($mrf && $mrf->num_rows() > 0){
			$mrf_count = $mrf->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		// $ci->db->where('status <>','Closed');
		$ci->db->where_in('candidate_status_id',array(1,2,3,4,5,11,12,13,17,18,19,20));
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$schedule = $ci->db->get('recruitment_manpower_candidate');
		if ($schedule && $schedule->num_rows() > 0){
			$schedule_count = $schedule->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		// $ci->db->where('status <>','Closed');
		$ci->db->where_in('candidate_status_id',array(20));
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$result = $ci->db->get('recruitment_manpower_candidate');
		if ($result && $result->num_rows() > 0){
			$result_count = $result->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		$ci->db->where('candidate_status_id','5');
		// $ci->db->where('status <>','Closed');
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$joboffer = $ci->db->get('recruitment_manpower_candidate');
		if ($joboffer && $joboffer->num_rows() > 0){
			$joboffer_count = $joboffer->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		$ci->db->where('candidate_status_id','12');
		// $ci->db->where('status <>','Closed');
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$contractsigning = $ci->db->get('recruitment_manpower_candidate');
		if ($contractsigning && $contractsigning->num_rows() > 0){
			$contractsign_count = $contractsigning->num_rows();
		}		

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		// $ci->db->where('status <>','Closed');
		$ci->db->where_in('candidate_status_id',array(7,8,9,10,15,16));
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$others = $ci->db->get('recruitment_manpower_candidate');
		if ($others && $others->num_rows() > 0){
			$others_count = $others->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		// $ci->db->where('status <>','Closed');
		$ci->db->where('candidate_status_id','6');
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$archive = $ci->db->get('recruitment_manpower_candidate');
		if ($archive && $archive->num_rows() > 0){
			$archive_count = $archive->num_rows();
		}

		$ci->db->where('recruitment_manpower_candidate.deleted','0');
		// $ci->db->where('status <>','Closed');
		$ci->db->where('candidate_status_id','4');
		$ci->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$bcheck = $ci->db->get('recruitment_manpower_candidate');
		if ($bcheck && $bcheck->num_rows() > 0){
			$bcheck_count = $bcheck->num_rows();
		}

		$array_count['posted_jobs'] = $mrf_count;
		$array_count['schedule'] = $schedule_count;
		$array_count['result'] = $result_count;
		$array_count['bcheck'] = $bcheck_count;
		$array_count['joboffer'] = $joboffer_count;
		$array_count['contractsigning'] = $contractsign_count;
		$array_count['others'] = $others_count;
		$array_count['archive'] = $archive_count;

		return $array_count;
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_candidate_count' ) ) {

	/**
	 * Returns the number of specified candidate status.
	 *
	 * @param null
	 *
	 * @return int
	 */
	function get_candidate_count( $id=null ) {
		$ci =& get_instance();

		if ( !is_null( $id ) ) {
			$ci->db->where( 'candidate_status_id', $id );
		}

		$ci->db->where( 'recruitment_manpower_candidate.deleted', 0 );
		$ci->db->join( 'recruitment_applicant', 'recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id' );
		$ci->db->join( 'recruitment_manpower rm', 'rm.request_id = recruitment_manpower_candidate.mrf_id');
		$ci->db->where( 'recruitment_applicant.deleted', 0 );
		$ci->db->where( 'rm.status <>', 'Closed' );

		if ( !$ci->is_recruitment() && !$ci->is_superadmin ) {
			$ci->db->where( '(requested_by =' . $ci->userinfo['user_id'] . ' OR approved_by = ' . $ci->userinfo['user_id'] . ' OR final_interviewer_id = ' . $ci->userinfo['user_id'] . ')' );
		}

		$result = $ci->db->get( 'recruitment_manpower_candidate' );

		return $result->num_rows();
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_manpower_count' ) ) {

	/**
	 * Returns the number of specified candidate status.
	 *
	 * @param null
	 *
	 * @return int
	 */
	function get_manpower_count( $id=null ) {
		$ci =& get_instance();

		if ( !is_null( $id ) ) {
			$ci->db->where( 'status', $id );
		}

		$ci->db->where( 'deleted', 0 );

		if ( !$ci->is_recruitment() && !$ci->is_superadmin ) {
			$ci->db->where( 'created_by', $ci->userinfo['user_id'] );
		}

		$result = $ci->db->get( 'recruitment_manpower' );

		return $result->num_rows();
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_candidate_statuses' ) ) {
	function get_candidate_statuses() {
		$ci =& get_instance();

		$ci->db->where('code !=',0);
		$ci->db->where('candidate_status_id !=' ,'6');
		$ci->db->order_by('ordered_by');
		$result = $ci->db->get_where( 'recruitment_candidate_status', array( 'deleted' => 0 ) );

		if ( $result && $result->num_rows() > 0 ) {
			return $result->result_array();
		}
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_new_employee_onboarding_count' ) ) {

	function get_checklist_count( $table ) {
		$ci = & get_instance();

		if ( !$ci->db->table_exists( $table ) ) {
			return 0;
		}

		$table = $ci->db->dbprefix . $table;

		if ( $table == $ci->db->dbprefix . 'employee' ) {
			$query = "SELECT *
                    FROM {$ci->db->dbprefix}recruitment_preemployment
                    WHERE deleted = 0 AND candidate_id
                        IN (
                            SELECT candidate_id FROM {$ci->db->dbprefix}recruitment_manpower_candidate
                                WHERE applicant_id
                                    NOT IN (
                                        SELECT applicant_id FROM {$ci->db->dbprefix}employee WHERE deleted = 0
					) AND candidate_status_id = 13
			)";
		
		} else {
			$query = 'SELECT * FROM `'.$ci->db->dbprefix.'recruitment_preemployment` t0
                    JOIN '.$ci->db->dbprefix.'recruitment_manpower_candidate tt ON t0.candidate_id = tt.candidate_id
                    JOIN '.$ci->db->dbprefix.'recruitment_applicant ra ON ra.applicant_id = tt.applicant_id
                    WHERE t0.deleted = 0
                    	AND tt.candidate_status_id = 13
                        AND t0.preemployment_id NOT IN (
				SELECT preemployment_id
					FROM ' . $table . ' t1
					WHERE t1.completed = 1 AND t1.deleted = 0
					)
                        AND tt.deleted = 0
                        AND ra.deleted = 0;';
		}

		$result = $ci->db->query( $query );
		
		return ( $result ) ? $result->num_rows() : false;
	}

}

// ------------------------------------------------------------------------

function get_checklist_data( $checklist_module, $get_status = false ) {
	$ci = & get_instance();

	$ci->db->where( 'module_id', $checklist_module['module_id'] );
	$result = $ci->db->get( 'module' );
	$row = $result->row_array();

	$response['label'] = $row['long_name'];
	$response['table'] = $row['table'];
	$response['link'] = $row['class_path'];
	$response['module_id'] = $row['module_id'];
	$response['code'] = $row['code'];

	if ( $get_status ) {
		$response['status'] = get_checklist_status( $row );
	}

	return $response;
}

// ------------------------------------------------------------------------

function get_checklist_status( $checklist ) {
	$ci = & get_instance();

	if ( !$ci->db->table_exists( $checklist['table'] ) ) {
		return false;
	}

	$select = 'CONCAT(ub.firstname, " ", ub.lastname) as updated_by';

	if ( $checklist['table'] != 'employee' ) {
		$select .= ', completed, date_complete, , date_updated,
            CONCAT(cb.firstname, " ", cb.lastname) as completed_by';
		$ci->db->join( 'user cb', 'cb.user_id = completed_by', 'left' );
		$ci->db->join( 'user ub', 'ub.user_id = updated_by', 'left' );

		$table = $checklist['table'];
	} else {
		$table = $ci->db->dbprefix . 'recruitment_preemployment';
		$select .= ', ' . $checklist['table'] . '.modified_date as date_updated';

		$ci->db->join( 'recruitment_manpower_candidate mc', 'mc.candidate_id = ' . $table . '.candidate_id' );
		$ci->db->join( $checklist['table'], $checklist['table'] . '.applicant_id = mc.applicant_id' );
		$ci->db->join( 'user ub', 'ub.user_id = ' . $checklist['table'] . '.modified_by', 'left' );
	}

	$ci->db->where( 'preemployment_id', $ci->input->post( 'record_id' ) );
	$ci->db->where( $checklist['table'] . '.deleted', 0 );
	$ci->db->select( $select, false );

	$result = $ci->db->get( $table );

	if ( $result && $result->num_rows() > 0 ) {
		$row = $result->row_array();
		if ( isset( $row['completed'] ) && $row['completed'] == 1 ) {
			$response['completed_by'] = $row['completed_by'];
			$response['completed_on'] = date( 'M d, Y h:ia', strtotime( $row['date_complete'] ) );
		} else {
			$response['updated_by'] = $row['updated_by'];
			$response['updated_on'] = date( 'M d, Y h:ia', strtotime( $row['date_updated'] ) );
		}

		return $response;
	} else {
		return false;
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_candidate_filters' ) ) {
	function get_candidate_filters( $statuses = array() ) {
		$filters = array();

		if ( count( $statuses ) == 0 ) {
		}
		$statuses = get_candidate_statuses();

		if ( $statuses ):
			$ctr = 0;
		foreach ( $statuses as $status ):
			$filters[$ctr]['text'] = $status['candidate_status'];
		$filters[$ctr]['link'] = site_url( 'recruitment/candidates/filter/' . $status['candidate_status_id'] );

		if ( $status['candidate_status_id']==13 )
			$filters[$ctr]['link'] = site_url( 'recruitment/preemployment' );

		$filters[$ctr]['count'] = get_candidate_count( $status['candidate_status_id'] );

		$ctr++;
		endforeach;
		$filters[0]['text'] = 'ALL';
		$filters[0]['link'] = site_url( 'recruitment/candidates/' );
		$filters[0]['count'] = get_candidate_count();
		endif;

		return $filters;
	}
}

// ------------------------------------------------------------------------

if ( !function_exists( 'get_applicant_filters' ) ) {
	function get_applicant_filters( $statuses = array() ) {
		$ci =& get_instance();

		$filters = array();

		if ( count( $statuses ) == 0 ) {
			if (CLIENT_DIR == "oams") {
				$status = $ci->db->get_where( 'application_status', array( 'deleted' => 0) );
			}else{
				$ci->db->order_by('ordered_by');
				$status = $ci->db->get_where( 'application_status', array( 'deleted' => 0, 'code !=' => 0 ) );
			}
			
			if ( $status->num_rows() > 0 ) {
				foreach ( $status->result() as $stat ) {
					$statuses[] = $stat->application_status_id;
				}
			}
		}

		if ( $statuses ):
			$ctr = 0;
		foreach ( $statuses as $status ):
			$app_stat = $ci->db->get_where( 'application_status', array( 'application_status_id' => $status ) )->row();

		$filters[$ctr]['text'] = $app_stat->application_status;
		$filters[$ctr]['link'] = site_url( 'recruitment/applicants/filter/' . $status );

		$ci->db->where( 'application_status_id', $status );
		$ci->db->where( 'deleted', 0 );
		$ci->db->select( 'applicant_id' );

		$count = $ci->db->get( 'recruitment_applicant' );

		$filters[$ctr]['count'] = $count->num_rows();
		$ctr++;
		endforeach;
		endif;

		return $filters;
	}
}

// ------------------------------------------------------------------------

function get_manpower_filters( $statuses = array() ) {
	$ci =& get_instance();

	$recruitment_id = $ci->hdicore->get_module('recruitment');

	$filters = array();

	$filters[0]['text'] = 'ALL';
	$filters[0]['link'] = site_url( 'recruitment/manpower/' );
	$filters[0]['count'] = get_manpower_count();
	
	if ( count( $statuses ) > 0 ):
		$ctr = 1;
		foreach ( $statuses as $key => $status ):
			switch ( $status ) {
			case 'For Approval':
				$priority = 'high';
				break;
			case 'Draft':
				$priority = 'medium';
				break;
			case 'Waiting':
				$priority = 'low';
				break;
			}

			$filters[$ctr]['text']     = $status;
			$filters[$ctr]['link']     = site_url( 'recruitment/manpower/filter/' . $key );
			$filters[$ctr]['priority'] = $priority;

			$ci->db->where( 'status', $status );
			$ci->db->where( 'deleted', 0 );

/*			if ( !$ci->is_recruitment() && !$ci->is_superadmin ) {
				if ( $status == 'Draft' ) {
					$ci->db->where( 'requested_by', $ci->userinfo['user_id'] );
				} else {
					$ci->db->where(
						'(approved_by = ' . $ci->userinfo['user_id']
						. ' OR requested_by = ' . $ci->userinfo['user_id']
						. ' OR concurred_by = ' . $ci->userinfo['user_id'] . ')'
					);
				}
			}*/

			if ( $status == 'Draft' ) {
				$ci->db->where( 'created_by', $ci->userinfo['user_id'] );
			}

			if ( !$ci->is_recruitment($recruitment_id->module_id) && !$ci->is_superadmin ) {
				// $ci->db->where( 'requested_by', $ci->userinfo['user_id'] );

				$ci->db->where('(( '.$ci->db->dbprefix('recruitment_manpower').'.request_id IN ( SELECT request_id 
						FROM '.$ci->db->dbprefix('recruitment_manpower_approver').'
						WHERE approver = '.$ci->userinfo['user_id'].' )  ) OR ( '.$ci->db->dbprefix('recruitment_manpower').'.requested_by = '.$ci->userinfo['user_id'].' ) OR ( '.$ci->db->dbprefix('recruitment_manpower').'.created_by = '.$ci->userinfo['user_id'].' ))');
				
				// $ci->db->where(
				// 	'(approved_by = ' . $ci->userinfo['user_id']
				// 	. ' OR requested_by = ' . $ci->userinfo['user_id']
				// 	. ' OR created_by = ' . $ci->userinfo['user_id']
				// 	. ' OR concurred_by = ' . $ci->userinfo['user_id'] . ')'
				// );
			}

			$count = $ci->db->get( 'recruitment_manpower' );

			$filters[$ctr]['count'] = $count->num_rows();
			$ctr++;
		endforeach;
	endif;

	return $filters;
}
