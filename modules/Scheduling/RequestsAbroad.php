<?php
require_once 'classes/curl.php';

// Get Courses in AJAX, returns XML.
if ( $_REQUEST['modfunc'] === 'XMLHttpCoursesRequest' )
{
	header( "Content-Type: text/xml\n\n" );

	$university = DBGet( "SELECT UNIVERSITY_URL, UNIVERSITY_TOKEN FROM UNIVERSITIES_ABROAD  WHERE " .
	    ( $_REQUEST['university_id'] ? "UNIVERSITY_ID='" . (int) $_REQUEST['university_id'] . "' " : '' ));
	
	$response = _getCoursesAPI($university[1]['UNIVERSITY_URL'], $university[1]['UNIVERSITY_TOKEN'], "courses");

	echo '<?phpxml version="1.0" standalone="yes"?><courses>';
	
	foreach($response["records"] as $key=>$course)
	{
	    if ($course['subject_id'] ==  $_REQUEST['subject_id'])
	    {
    		echo '<course><id>' . $course['course_id'] . '</id>
    		<title>' . htmlspecialchars($course['title']    ) . '</title>
    		</course>';
	    }
	}
	echo '<university><id>' . $_REQUEST['university_id']. '</id></university>';
	echo '<subject><id>' . $_REQUEST['subject_id']. '</id>
          <title>' . $_REQUEST['subject_id']. '</title></subject>';
	echo '</courses>';
	

	exit;
}



if ( $_REQUEST['modfunc'] === 'XMLHttpSubjectsRequest' )
{
    header( "Content-Type: text/xml\n\n" );
    
   
    $university = DBGet( "SELECT UNIVERSITY_URL, UNIVERSITY_TOKEN FROM UNIVERSITIES_ABROAD  WHERE " .
        ( $_REQUEST['university_id'] ? "UNIVERSITY_ID='" . (int) $_REQUEST['university_id'] . "' " : '' ));
    
    
    
    $response = _getCoursesAPI($university[1]['UNIVERSITY_URL'], $university[1]['UNIVERSITY_TOKEN'], "course_subjects");
        
        echo '<?phpxml version="1.0" standalone="yes"?><subjects>';
        
        foreach($response["records"] as $key=>$subjects)
        {
            echo '<subject><id>' . $subjects['subject_id'] . '</id>
		<title>' . htmlspecialchars( $subjects['title'], ENT_QUOTES ) . '</title>
		</subject>';
        }
        
        echo '</subjects>';
        
        exit;
}

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

Widgets( 'request' );

Search( 'student_id', $extra );

// Remove.
if ( $_REQUEST['modfunc'] === 'remove'
	&& AllowEdit() )
{
	if ( DeletePrompt( _( 'Request' ) ) )
	{
		DBQuery( "DELETE FROM REQUESTS_ABROAD
			WHERE REQUEST_ABROAD_ID='" . $_REQUEST['id'] . "'" );

		// Unset modfunc & ID & redirect URL.
		RedirectURL( array( 'modfunc', 'id' ) );
	}
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
				" WHERE STUDENT_ID='" . UserStudentID() . "'
				AND REQUEST_ID='" . $request_id . "'";

			DBQuery( $sql );
		}
	}

	// Unset modfunc & redirect URL.
	RedirectURL( 'modfunc' );
}

// Add.
if ( $_REQUEST['modfunc'] === 'add' )
{
	if ( $_REQUEST['course_id']
		&& AllowEdit() )
	{
		$course_id = $_REQUEST['course_id'];

		$subject_id = $_REQUEST['subject_id'];
		
		$university_id = $_REQUEST['university_id'];
		
		$course_title = $_REQUEST['course_title'];
		
		$subject_title = $_REQUEST['subject_title'];
		
		

		DBQuery( "INSERT INTO REQUESTS_ABROAD (UNIVERSITY_ID,STUDENT_ID,SUBJECT_ID,COURSE_ID, SUBJECT_TITLE, COURSE_TITLE)
			VALUES ('". $university_id . "','" . UserStudentID(). "','" .  $subject_id . "','" 
		    . $course_id . "','" . $subject_title. "','" . $course_title. "')");
	}

	// Unset modfunc & course & redirect URL.
	RedirectURL( array( 'modfunc', 'course' ) );
}

if ( ! $_REQUEST['modfunc']
	&& UserStudentID() )
{
?>
<script>
function SendSubjectsRequest(university_id)
{
	if (window.XMLHttpRequest)
		connection = new XMLHttpRequest();
	else if (window.ActiveXObject)
		connection = new ActiveXObject("Microsoft.XMLHTTP");
	connection.onreadystatechange = processRequestSubjects;
	connection.open("GET","Modules.php?modname=<?php echo $_REQUEST['modname']; ?>&_ROSARIO_PDF=true&modfunc=XMLHttpSubjectsRequest&university_id=" + university_id , true );
	connection.send(null);
}


function SendXMLRequestCourses(university_id, subject_id, subject)
{
	console.log(subject);
	if (window.XMLHttpRequest)
		connection = new XMLHttpRequest();
	else if (window.ActiveXObject)
		connection = new ActiveXObject("Microsoft.XMLHTTP");
	connection.onreadystatechange = processRequestCourses;
	connection.open("GET","Modules.php?modname=<?php echo $_REQUEST['modname']; ?>&_ROSARIO_PDF=true&modfunc=XMLHttpCoursesRequest&university_id=" + university_id+"&subject_id=" + subject_id + "&subject_title=" + encodeURIComponent(subject), true );
	connection.send(null);
}

function doOnClick(course, subject, university, course_title, subject_title)
{
	ajaxLink("Modules.php?modname=<?php echo $_REQUEST['modname']; ?>&modfunc=add&course_id=" + course + "&subject_id=" + subject+ "&university_id=" + university + "&course_title=" + course_title+ "&subject_title=" + subject_title);
}

function processRequestSubjects()
{
	// LOADED && ACCEPTED
	if (connection.readyState == 4 && connection.status == 200)
	{
		XMLResponse = connection.responseXML;
		document.getElementById("subjects_div").style.display = "block";
		subject_list = XMLResponse.getElementsByTagName("subjects");
		subject_list = subject_list[0];
		subjects = subject_list.getElementsByTagName("subject");


		$subjectsSel = '<select name="subject_id" onchange="document.getElementById(\'courses_div\').innerHTML = \'\';SendXMLRequestCourses(this.form.university_id.options[this.form.university_id.selectedIndex].value,this.form.subject_id.options[this.form.subject_id.selectedIndex].value, this.form.subject_id.options[this.form.subject_id.selectedIndex].text);">';
		$subjectsSel += '<option value="">' + <?php echo json_encode( _( 'All Subjects' ) ); ?> + '</option>';

			
		for(i=0;i<subjects.length;i++)
		{				
			id = subjects[i].getElementsByTagName("id")[0].firstChild.data;
			title = subjects[i].getElementsByTagName("title")[0].firstChild.data;
			$subjectsSel += '<option value="' + id+ '">' + title + '</option>';
			
		}
		$subjectsSel += '</select>';
		document.getElementById("subjects_div").innerHTML += $subjectsSel;

		if ( subjects.length === 0 )
		{
			document.getElementById("subjects_div").innerHTML += <?php echo json_encode( _( 'No courses found' ) ); ?>;
		}
	}
}


function processRequestCourses()
{
	// LOADED && ACCEPTED
	if (connection.readyState == 4 && connection.status == 200)
	{
		XMLResponse = connection.responseXML;
		document.getElementById("courses_div").style.display = "block";
		course_list = XMLResponse.getElementsByTagName("courses");
		course_list = course_list[0];
		courses = course_list.getElementsByTagName("course");
		university = course_list.getElementsByTagName("university");
		university_id = university[0].getElementsByTagName("id")[0].firstChild.data;
		subject = course_list.getElementsByTagName("subject");
		subject_id = subject[0].getElementsByTagName("id")[0].firstChild.data;
		subject_title = subject[0].getElementsByTagName("title")[0].firstChild.data;
	

		for(i=0;i<courses.length;i++)
		{				
			id = courses[i].getElementsByTagName("id")[0].firstChild.data;
			title = courses[i].getElementsByTagName("title")[0].firstChild.data;
			document.getElementById("courses_div").innerHTML += "<a onclick=\"doOnClick(\'"+ id +"\', \'"+ subject_id +"\', \'"+ university_id +"\', \'"+ title +"\', \'"+ subject_title +"\'); return false;\" href=\"#\">" + title + "</a><br />";
			
		}

		if ( courses.length === 0 )
		{
			document.getElementById("subjects_div").innerHTML += <?php echo json_encode( _( 'No courses found' ) ); ?>;
		}
	}
}

</script>
<?php

	$functions = array(
		'COURSE' => '_makeCourse'
	);

	$requests_RET = DBGet( "SELECT REQUEST_ABROAD_ID, COURSE_ID, COURSE_TITLE as COURSE, UNIVERSITY_ID		
		FROM REQUESTS_ABROAD
		WHERE STUDENT_ID='" . UserStudentID() . "'", $functions );

	$columns = array(
		'COURSE' => _( 'Course' ),
	);

	$universities_RET = DBGet( "SELECT UNIVERSITY_ID, UNIVERSITY_NAME
		FROM UNIVERSITIES_ABROAD");
	
	$universities = '<select name="university_id" onchange="document.getElementById(\'subjects_div\').innerHTML = \'\';SendSubjectsRequest(this.form.university_id.options[this.form.university_id.selectedIndex].value);">';
	$universities .= '<option value="">' . _( 'All Universities' ) . '</option>';
	
	foreach ( (array) $universities_RET as $university )
	{
	    $universities .= '<option value="' . $university['UNIVERSITY_ID'] . '">' . $university['UNIVERSITY_NAME'] . '</option>';
	}

	$universities .= '</select>';
	
	$link['remove'] = array(
		'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=remove',
		'variables' => array( 'id' => 'REQUEST_ABROAD_ID' ),
	);

	echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=update" method="POST">';

	DrawHeader( '', SubmitButton() );

	$link['add']['span'] = ' ' . _( 'Add a Request' ) .
	': <span class="nobr"> ' . _( 'University' ) . ' ' . $universities .
		' ' . _( 'Subject Title' ) .
		' <input type="text" id="subject_title" name="subject_title" onkeypress="if (event.keyCode==13)return false;" onkeyup="document.getElementById(\'subjects_div\').innerHTML = \'\';SendSubjectsRequest(this.form.university_id.options[this.form.university_id.selectedIndex].value);"></span>
		<div id="subjects_div"></div><div id="courses_div"></div>';

	echo '<div style="position:relative;">';

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

/**
 * Conects with the API of the abroad university
 * @param $url is the URL of REST API
 * @param $token is the authorization token of REST API
 * @param $path is the path of the REST API 
 * @return returns the requested data
 */
function _getCoursesAPI($url, $token, $path){
    $curl = new curl;
    $resp = $curl->get( $url, array( 'usertoken' => $token,
        'path' => $path,) );
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