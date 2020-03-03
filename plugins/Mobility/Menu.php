<?php
/**
 * Abroad module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package Mobility plugin
 */

$menu['Mobility']['admin'] = array(
	'title' => _( 'Mobility' ),
	'default' => 'plugins/Mobility/CoursesAbroad.php',
    'plugins/Mobility/CoursesAbroad.php' => _( 'Courses Abroad' ),
	'plugins/Mobility/ScheduleAbroad.php' => _( 'Student Mobility Schedule' ),
	'plugins/Mobility/RequestsAbroad.php' => _( 'Student Mobility Requests' ),
	1 => _( 'External' ),
    'plugins/Mobility/RequestsUniversitiesAbroad.php' => _( 'Universities Mobility Requests' ),
);

$menu['Mobility']['parent'] = array(
    'title' => _( 'Mobility' ),
    'default' => 'plugins/Mobility/CoursesAbroad.php',
    'plugins/Mobility/CoursesAbroad.php' => _( 'Courses Abroad' ),
    'plugins/Mobility/RequestsAbroad.php' => _( 'Student Mobility Requests' ),
);

$exceptions['Mobility'] = array();
