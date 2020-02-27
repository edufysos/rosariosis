<?php
/**
 * Abroad module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package Abroad plugin
 */

$menu['Abroad']['admin'] = array(
	'title' => _( 'Abroad' ),
	'default' => 'plugins/Abroad/CoursesAbroad.php',
    'plugins/Abroad/CoursesAbroad.php' => _( 'Courses Abroad' ),
	'plugins/Abroad/ScheduleAbroad.php' => _( 'Student Schedule Abroad' ),
	'plugins/Abroad/RequestsAbroad.php' => _( 'Student Requests Abroad' ),
	1 => _( 'External' ),
    'plugins/Abroad/RequestsUniversitiesAbroad.php' => _( 'Universities Requests Abroad' ),
);

$menu['Abroad']['parent'] = array(
    'title' => _( 'Abroad' ),
    'default' => 'plugins/Abroad/CoursesAbroad.php',
    'plugins/Abroad/CoursesAbroad.php' => _( 'Courses Abroad' ),
    'plugins/Abroad/RequestsAbroad.php' => _( 'Student Requests Abroad' ),
);

$exceptions['Abroad'] = array();
