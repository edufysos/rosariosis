<?php
require_once 'classes/curl.php';
require_once 'plugins/Moodle/getconfig.inc.php';


DrawHeader( ProgramTitle() );

// Allow Parents & Students to Edit if have permissions.
if ( User( 'PROFILE' ) !== 'admin'
    && User( 'PROFILE' ) !== 'teacher' )
{
    $can_edit_from_where = " FROM PROFILE_EXCEPTIONS WHERE PROFILE_ID='" . User( 'PROFILE_ID' ) . "'";
    
    if ( User( 'PROFILE' ) !== 'student'
        && ! User( 'PROFILE_ID' ) )
    {
        $can_edit_from_where = " FROM STAFF_EXCEPTIONS WHERE USER_ID='" . User( 'STAFF_ID' ) . "'";
    }
    
    $can_edit_RET = DBGet( "SELECT MODNAME " . $can_edit_from_where .
        " AND MODNAME='plugins/Mobility/RequestsAbroad.php'
		AND CAN_EDIT='Y'" );
    
    if ( $can_edit_RET )
    {
        $_ROSARIO['allow_edit'] = true;
    }
}

Widgets( 'request' );

// Send.
if ( $_REQUEST['modfunc'] === 'send'
    && AllowEdit() )
{
    $sql = "SELECT R.REQUEST_ABROAD_ID, R.COURSE_ID, R.SUBJECT_ID, R.STATUS, R.UNIVERSITY_ID, S.STUDENT_ID, S.". ROSARIO_STUDENTS_EMAIL_FIELD." as EMAIL,
                S.LAST_NAME, S.FIRST_NAME, S.MIDDLE_NAME, S.NAME_SUFFIX, S.USERNAME, S.PASSWORD, U.UNIVERSITY_TOKEN, U.UNIVERSITY_URL
		FROM ((REQUESTS_ABROAD R INNER JOIN STUDENTS S on S.STUDENT_ID = R.STUDENT_ID)  INNER JOIN UNIVERSITIES_ABROAD U
        ON U.UNIVERSITY_ID = R.UNIVERSITY_ID) WHERE R.STATUS = 'AO' ";
    if (UserStudentID() ){
        $sql .= " AND R.STUDENT_ID='" . UserStudentID() . "'";
    }
    $requests_RET = DBGet( $sql);
    
   
    foreach ( (array) $requests_RET as $request )
    {
        $data = '{
            "university_token": "'.$request['UNIVERSITY_TOKEN'].'",
            "course_id": "'.$request['COURSE_ID'].'",
            "subject_id": "'.$request['SUBJECT_ID'].'",
            "student_id": "'.$request['STUDENT_ID'].'",
            "last_name": "'.$request['LAST_NAME'].'",
            "first_name": "'. $request['FIRST_NAME'].'",
            "middle_name" : "'. $request['MIDDLE_NAME'].'",
            "name_suffix": "'.$request['NAME_SUFFIX'].'",
            "username" : "'. $request['USERNAME'].'",
            "pass_student": "'. $request['PASSWORD'].'",
            "email": "'. $request['EMAIL'].'",
            "status" : "WD" 
            }';   
        
         
        $response = _createEnrollmentRequest($request['UNIVERSITY_URL'], $request['UNIVERSITY_TOKEN'], "enrollment_requests", $data);
        
        if (is_numeric($response)) {
            DBQuery( "UPDATE REQUESTS_ABROAD SET STATUS = 'WD', REQUEST_ENROLLMENT_ID = '".$response."'
			WHERE REQUEST_ABROAD_ID='" .$request['REQUEST_ABROAD_ID'] . "'" );
        }        
    }
       
  
    
    // Unset modfunc & course & redirect URL.
    RedirectURL( array( 'modfunc', ) );
}

// Update.
if ( $_REQUEST['modfunc'] === 'update' )
{

    if ( ! empty( $_REQUEST['values'] )
        && ! empty( $_POST['values'] )
        && AllowEdit() )
    {
        foreach ( (array) $_REQUEST['values'] as $request_id => $columns )
        {
            $sql = "UPDATE REQUESTS_ABROAD SET ";
            
            foreach ( (array) $columns as $column => $value )
            {
                $sql .= DBEscapeIdentifier( $column ) . "='" . $value . "',";
            }
            
            $sql = mb_substr( $sql, 0, -1 ) .
            " WHERE REQUEST_ABROAD_ID='" . $request_id . "'";
            if (UserStudentID() ){
                $sql .= " AND R.STUDENT_ID='" . UserStudentID() . "'";
            }

            
            DBQuery( $sql );
        }
    }
    
    // Unset modfunc & redirect URL.
    RedirectURL( 'modfunc' );
}

if ( ! $_REQUEST['modfunc'] )
{

	$functions = array(
		'COURSE' => '_makeCourse',
	    'STATUS' => '_makeStatus'
	);
	
	$sql = "SELECT R.REQUEST_ABROAD_ID, R.COURSE_ID, R.COURSE_TITLE as COURSE, R.STATUS, R.UNIVERSITY_ID, CONCAT(S.FIRST_NAME, ' ', S.LAST_NAME) as STUDENT		
		FROM REQUESTS_ABROAD R INNER JOIN STUDENTS S ON S.STUDENT_ID = R.STUDENT_ID ";
	if (UserStudentID() ){
	    $sql .= " WHERE R.STUDENT_ID='" . UserStudentID() . "'";
	}
	

	$requests_RET = DBGet(	$sql, $functions );

	$columns = array(
	    'COURSE' => _( 'Course' ),
	    'STUDENT' => _( 'Student' ),
	    'STATUS'=> _( 'Status' ),
	);

	
	echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=update" method="POST">';

	$link['add'] = array(
	    'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=send',
	);
	$link['add']['title'] = _( 'Send request to institution(s) abroad(s)' );
	

	ListOutput(
		$requests_RET,
		$columns,
		'Request',
		'Requests',
		$link
	);

	echo '</div>';
	
	echo '<br /><div class="center">' . SubmitButton() . '</div>';
	echo '</form>';
}

function _makeCourse( $value, $column )
{
	return $value;
}



function _makeStatus( $value, $column )
{
    global $THIS_RET;
    
   
    $options = array();
    $options['WO'] = "Waiting";
    $options['DO'] = "Denied origin";
    $options['AO'] = "Approved origin";
    $options['WD'] = "Waiting destination";
    $options['DD'] = "Denied destination";
    $options['AD'] = "Approved destination";
    $options['FF'] = "Finished";
    
    return '<div style="display:table-cell;"></div>
		<div style="display:table-cell;">' .
		SelectInput(
		    $value,
		    'values[' . $THIS_RET['REQUEST_ABROAD_ID'] . '][STATUS]',
		    '',
		    $options, false
		    ) .
		    '</div>';
}

/**
 * Conects with the API of the abroad university
 * @param $url is the URL of REST API
 * @param $token is the authorization token of REST API
 * @param $path is the path of the REST API 
 * @return returns the requested data
 */
function _createEnrollmentRequest($url, $token, $path, $options){
    $curl = new curl;
    $resp = $curl->post( $url, array( 'usertoken' => $token,
        'path' => $path, 'data' => $options) );
    return $resp =  _getJson( $resp);
}

function _getJson( $data )
{
    $decoded =  json_decode( $data, true );
    
    if ( json_last_error() !== JSON_ERROR_NONE )
    {
        return $data;
    }
    
    return $decoded;
}

