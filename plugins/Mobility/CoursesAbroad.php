<?php
require_once 'classes/curl.php';

DrawHeader( ProgramTitle() );


function _getJson( $data )
{
	$decoded =  json_decode( $data, true );

	if ( json_last_error() !== JSON_ERROR_NONE )
	{
		return $data;
	}

	return $decoded;
}

/** 
 * Conects with the API of the abroad university 
 * @param $path is the path of the REST API
 * @return returns the requested data 
*/
function _getCoursesAPI($url, $token, $path){
    	
	$curl = new curl;
	$resp = $curl->get( $url, array( 'usertoken' => $token,
	'path' => $path,) );
	return $resp =  _getJson( $resp);
}

							

#header( 'Content-Type: application/json' );

#echo $response;

#print_r($response["records"][0]);

$universities_RET = DBGet( "SELECT UNIVERSITY_URL, UNIVERSITY_TOKEN, UNIVERSITY_NAME FROM UNIVERSITIES_ABROAD");

foreach ( (array) $universities_RET as $university )
{

    $response = _getCoursesAPI($university['UNIVERSITY_URL'], $university['UNIVERSITY_TOKEN'], "course_subjects");
    $responseCourses = _getCoursesAPI($university['UNIVERSITY_URL'], $university['UNIVERSITY_TOKEN'],"courses");
    
$table= '
	<div class="list-outer subjects">
		<div class="list-wrapper">
			<table class="list widefat" width="100%">
				<thead>';
                    $table .= '<tr><th>'. $university['UNIVERSITY_NAME'] .'</th>';
                    $table .= '	</tr></thead><tbody>';
				foreach($response["records"] as $key=>$subjects)
				{
					$table .= '<tr><td class="highlight">'. $subjects['title'] .'</td></tr>';
					foreach($responseCourses["records"] as $key=>$courses)
					{					    
						if ($courses['subject_id'] ===  $subjects['subject_id'])
						{
							$table .= '<tr><td><span>&nbsp&nbsp&nbsp</span>'.$courses['title'].'</td></tr>';
						}	
					}	
					$table .= '</tbody>'  ;
				} 				

            
		$table .= '</table>
		</div>
	</div> <br /> <br />';

echo $table;
}

exit;