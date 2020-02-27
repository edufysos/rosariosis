<?php
require_once 'classes/curl.php';
require_once 'modules/Students/includes/SaveEnrollment.fnc.php';
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
        " AND MODNAME='Scheduling/RequestsAbroad.php'
		AND CAN_EDIT='Y'" );
    
    if ( $can_edit_RET )
    {
        $_ROSARIO['allow_edit'] = true;
    }
}



// Enroll.
if ( $_REQUEST['modfunc'] === 'enroll'
    && AllowEdit() )
{
    
 
    $requests_RET = DBGet( "SELECT DISTINCT UNIVERSITY_TOKEN, STUDENT_ID FROM ENROLLMENT_REQUESTS WHERE STATUS = 'AD' ");
    

    foreach ( (array) $requests_RET as $request )
    {
        $students = DBGet( "SELECT STUDENT_ID, LAST_NAME, FIRST_NAME, MIDDLE_NAME, NAME_SUFFIX, USERNAME, PASS_STUDENT, EMAIL
         FROM ENROLLMENT_REQUESTS WHERE STUDENT_ID = '". $request['STUDENT_ID'] . "' AND STATUS = 'AD' ");

        $student_id = DBSeqNextID( 'students_student_id_seq' );
        $sql = "INSERT INTO STUDENTS (STUDENT_ID, LAST_NAME, FIRST_NAME, MIDDLE_NAME, NAME_SUFFIX, USERNAME, PASSWORD, ".ROSARIO_STUDENTS_EMAIL_FIELD.") VALUES
    ('" .  $student_id . "', '". $students[1]['LAST_NAME']. "','". $students[1]['FIRST_NAME']. "','". $students[1]['MIDDLE_NAME']. "','". $students[1]['NAME_SUFFIX']. "',
    '". $student_id .$students[1]['USERNAME']. "','". $students[1]['PASS_STUDENT']. "','". $students[1]['EMAIL']. "')";

        DBQuery( $sql );        
      
        // Create enrollment.
        SaveEnrollment();

        // Hook.
        $_REQUEST['STUDENT_ID'] = $student_id;
        $_REQUEST['moodle_create_student_abroad'] = true;
        
        do_action( 'Students/Student.php|create_student_abroad' );
        
        
        $enroll_RET = DBGet( "SELECT E.COURSE_ID, E.COURSE_PERIOD_ID, C.MP, C.MARKING_PERIOD_ID 
            FROM (ENROLLMENT_REQUESTS E INNER JOIN COURSE_PERIODS C ON C.COURSE_PERIOD_ID = E.COURSE_PERIOD_ID) 
             WHERE STUDENT_ID = '". $request['STUDENT_ID'] . "' AND STATUS = 'AD' ");
        
        $date = DBDate();
        
        foreach ( (array) $enroll_RET as $enroll )
        {
            
            DBQuery( "INSERT INTO SCHEDULE (SYEAR,SCHOOL_ID,STUDENT_ID,START_DATE,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID)
        values('" . UserSyear() . "','" . UserSchool() . "','" . $student_id . "','" . $date . "','" . $enroll['COURSE_ID'] . "','" . $enroll['COURSE_PERIOD_ID']  . "','" . $enroll['MP'] . "','" . $enroll['MARKING_PERIOD_ID'] . "')" );
           
            $_REQUEST['course_period_id'] = $enroll['COURSE_PERIOD_ID'];
            do_action( 'Scheduling/Schedule.php|schedule_student' );
            
        }
        $sql = "UPDATE ENROLLMENT_REQUESTS SET STATUS = 'FF', STUDENT_DEST_ID = '".$student_id."' WHERE STATUS = 'AD' ";
        DBQuery( $sql );
    }
    
    RedirectURL( 'modfunc' );
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
            $sql = "UPDATE ENROLLMENT_REQUESTS SET ";
            
            foreach ( (array) $columns as $column => $value )
            {
                $sql .= DBEscapeIdentifier( $column ) . "='" . $value . "',";
            }
            
            $sql = mb_substr( $sql, 0, -1 ) .
            " WHERE ENROLLMENT_REQUESTS_ID='" . $request_id . "'";

            
            DBQuery( $sql );
        }
    }
    
    // Unset modfunc & redirect URL.
    RedirectURL( 'modfunc' );
}

if ( ! $_REQUEST['modfunc'] )
{

	$functions = array(
	    'STATUS' => '_makeStatus',
	    'COURSE_PERIOD_ID' => '_makePeriod',
	);

	$requests_RET = DBGet( "SELECT E.ENROLLMENT_REQUESTS_ID as ENROLLMENT_REQUESTS_ID, U.UNIVERSITY_NAME as UNIVERSITY, 
                        CONCAT(E.FIRST_NAME, ' ', E.LAST_NAME) as STUDENT, E.COURSE_ID, COURSE_PERIOD_ID, E.SUBJECT_ID, C.TITLE as COURSE, S.TITLE AS SUBJECT, E.STATUS        		
		FROM (((ENROLLMENT_REQUESTS E INNER JOIN COURSE_SUBJECTS S ON S.SUBJECT_ID = E.SUBJECT_ID) INNER JOIN COURSES C
        ON C.COURSE_ID = E.COURSE_ID) JOIN UNIVERSITIES_ABROAD U ON E.UNIVERSITY_TOKEN = U.UNIVERSITY_TOKEN)", $functions );

	$columns = array(
	    'UNIVERSITY' => _( 'Origin University' ),
	    'STUDENT' => _( 'Student Name' ),
	    'SUBJECT' => _( 'Subject' ),
	    'COURSE' => _( 'Course' ),
	    'STATUS'=> _( 'Status' ),
	    'COURSE_PERIOD_ID'=> _( 'Course Period' ),
	);

	
	echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=update" method="POST">';


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


	echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=enroll" method="POST">';
	
	
	include 'modules/Students/includes/Enrollment.inc.php';

 	echo '<br /><div class="center">' . SubmitButton('Enroll Students Abroad') . '</div>';
	echo '</form>';


	
}

function _makeCourse( $value, $column )
{
	return $value;
}


function _makePeriod( $value, $column )
{
    global $THIS_RET;
    
    $periods_RET = DBGet( "SELECT COURSE_PERIOD_ID,TITLE FROM COURSE_PERIODS 
                WHERE COURSE_ID='" . $THIS_RET['COURSE_ID'] . "' AND SYEAR='" . UserSyear() . "' ORDER BY SHORT_NAME,TITLE");
    $options = array();
    foreach ( (array) $periods_RET as $period )
    {
        $options[$period['COURSE_PERIOD_ID']] = $period['TITLE'];
    }
    
    
    return '<div style="display:table-cell;"></div>
		<div style="display:table-cell;">' .
		SelectInput(
		    $value,
		    'values[' . $THIS_RET['ENROLLMENT_REQUESTS_ID'] . '][COURSE_PERIOD_ID]',
		    '',
		    $options, false
		    ) .
		    '</div>';
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
		    'values[' . $THIS_RET['ENROLLMENT_REQUESTS_ID'] . '][STATUS]',
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

