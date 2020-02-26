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
function _getCoursesAPI($path){
	$api_config = ProgramUserConfig( 'REST_API' );
	$api_abroad_config = ProgramUserConfig( 'Abroad' );
	$api_user_token = ! empty( $api_config['USER_TOKEN'] ) ? $api_config['USER_TOKEN'] : '';	
	$curl = new curl;
	$api_url  = ! empty( $api_abroad_config['UNIVERSITY_URL'] ) ? $api_abroad_config['UNIVERSITY_URL'] : '';
	$resp = $curl->get( $api_url, array( 'usertoken' => $api_user_token,
	'path' => $path,) );
	return $resp =  _getJson( $resp);
}

/** 
 * Conects with the API of the abroad university 
 * @param $path is the path of the REST API
 * @return returns the requested data 
*/
function _getCoursesAPI2($path){
    $university = DBGet( "SELECT UNIVERSITY_URL, UNIVERSITY_TOKEN FROM UNIVERSITIES_ABROAD  WHERE " .
		 "UNIVERSITY_ID='1'");
		 
    	
	$curl = new curl;
	$resp = $curl->get( $university[1]['UNIVERSITY_URL'], array( 'usertoken' => $university[1]['UNIVERSITY_TOKEN'],
	'path' => $path,) );
	return $resp =  _getJson( $resp);
}

							
$response = _getCoursesAPI("course_subjects");
$responseCourses = _getCoursesAPI("courses");

#header( 'Content-Type: application/json' );

#echo $response;

#print_r($response["records"][0]);



$table= '
	<div class="list-outer subjects">
		<div class="list-wrapper">
			<table class="list widefat" width="100%">
				<thead>';

				foreach($response["records"] as $key=>$subjects)
				{
					$table .= '<tr><th>'. $subjects['title'] .'</th>';
					$table .= '<table><tbody>';
					foreach($responseCourses["records"] as $key=>$courses)
					{
						if ($courses['subject_id'] ===  $subjects['subject_id'])
						{
							$table .= '<tr><td class="highlight">'.$courses['title'].'</td></tr>';
						}	
					}	
					$table .= '</tbody></table></tr>'  ;

				} 				
$table .= '	</thead>
			</table>
		</div>
	</div>';

echo $table;

exit;