<?php
/**
 * Scheduling module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package RosarioSIS
 * @subpackage modules
 */

$menu['Scheduling']['admin'] = array(
	'title' => _( 'Scheduling' ),
	'default' => 'Scheduling/Schedule.php',
	'Scheduling/Schedule.php' => _( 'Student Schedule' ),
	'Scheduling/Requests.php' => _( 'Student Requests' ),
	'Scheduling/MassSchedule.php' => _( 'Group Schedule' ),
	'Scheduling/MassRequests.php' => _( 'Group Requests' ),
	'Scheduling/MassDrops.php' => _( 'Group Drops' ),
	1 => _( 'Reports' ),
	'Scheduling/PrintSchedules.php' => _( 'Print Schedules' ),
	'Scheduling/PrintClassLists.php' => _( 'Print Class Lists' ),
	'Scheduling/PrintClassPictures.php' => _( 'Print Class Pictures' ),
	'Scheduling/PrintRequests.php' => _( 'Print Requests' ),
	'Scheduling/MasterScheduleReport.php' => _( 'Master Schedule Report' ),
	'Scheduling/ScheduleReport.php' => _( 'Schedule Report' ),
	'Scheduling/RequestsReport.php' => _( 'Requests Report' ),
	'Scheduling/UnfilledRequests.php' => _( 'Unfilled Requests' ),
	'Scheduling/IncompleteSchedules.php' => _( 'Incomplete Schedules' ),
	'Scheduling/AddDrop.php' => _( 'Add / Drop Report' ),
	2 => _( 'Setup' ),
	'Scheduling/Courses.php' => _( 'Courses' ),
	'Scheduling/CoursesAbroad.php' => _( 'Courses Abroad' ),
	'Scheduling/Scheduler.php' => _( 'Run Scheduler' ),
);

$menu['Scheduling']['teacher'] = array(
	'title' => _( 'Scheduling' ),
	'default' => 'Scheduling/Schedule.php',
	'Scheduling/Schedule.php' => _( 'Schedule' ),
	// Activate Courses for Teachers & Parents & Students.
	'Scheduling/Courses.php' => _( 'Courses' ),
	1 => _( 'Reports' ),
	'Scheduling/PrintSchedules.php' => _( 'Print Schedules' ),
	'Scheduling/PrintClassLists.php' => _( 'Print Class Lists' ),
	'Scheduling/PrintClassPictures.php' => _( 'Print Class Pictures' ),
);

$menu['Scheduling']['parent'] = array(
	'title' => _( 'Scheduling' ),
	'default' => 'Scheduling/Schedule.php',
	'Scheduling/Schedule.php' => _( 'Schedule' ),
	'Scheduling/Requests.php' => _( 'Student Requests' ),
	// Activate Courses for Teachers & Parents & Students.
	'Scheduling/Courses.php' => _( 'Courses' ),
	'Scheduling/CoursesAbroad.php' => _( 'Courses Abroad' ),
	1 => _( 'Reports' ),
	// FJ activate Print Schedules for parents and students.
	'Scheduling/PrintSchedules.php' => _( 'Print Schedules' ),
	'Scheduling/PrintClassPictures.php' => _( 'Class Pictures' ),
);

$exceptions['Scheduling'] = array(
	'Scheduling/Requests.php' => true,
	'Scheduling/MassRequests.php' => true,
	'Scheduling/Scheduler.php' => true
);
