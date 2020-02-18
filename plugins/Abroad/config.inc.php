<?php
/**
 * Plugin configuration interface
 *
 * @package REST_API plugin
 */

// Check the script is called by the right program & plugin is activated.
if ( $_REQUEST['modname'] !== 'School_Setup/Configuration.php'
	|| ! $RosarioPlugins['Abroad']
	|| $_REQUEST['modfunc'] !== 'config' )
{
	$error[] = _( 'You\'re not allowed to use this program!' );

	echo ErrorMessage( $error, 'fatal' );
}

// Note: no need to call ProgramTitle() here!

if ( isset( $_REQUEST['save'] )
	&& $_REQUEST['save'] === 'true' )
{
	if ( $_REQUEST['values']['PROGRAM_USER_CONFIG']
		&& $_POST['values']
		&& AllowEdit() )
	{
		// Update the PROGRAM_USER_CONFIG table.
		$sql = '';

		if ( isset( $_REQUEST['values']['PROGRAM_USER_CONFIG'] )
			&& is_array( $_REQUEST['values']['PROGRAM_USER_CONFIG'] ) )
		{
			foreach ( (array) $_REQUEST['values']['PROGRAM_USER_CONFIG'] as $column => $value )
			{
				ProgramUserConfig( 'Abroad', User( 'STAFF_ID' ), array( $column => $value ) );
			}
		}

		if ( $sql != '' )
		{
			DBQuery( $sql );

			$note[] = button( 'check' ) . '&nbsp;' . _( 'The plugin configuration has been modified.' );
		}
	}

	// Unset save & values & redirect URL.
	RedirectURL( 'save', 'values' );
}


if ( empty( $_REQUEST['save'] ) )
{


	echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&tab=plugins&modfunc=config&plugin=Abroad&save=true" method="POST">';

	DrawHeader( '', SubmitButton( _( 'Save' ) ) );

	echo ErrorMessage( $note, 'note' );

	echo ErrorMessage( $error, 'error' );
	
	$api_config = ProgramUserConfig( 'Abroad' );
	
	
	$api_uni_name = ! empty( $api_config['UNIVERSITY_NAME'] ) ? $api_config['UNIVERSITY_NAME'] : '';
	$api_uni_url = ! empty( $api_config['UNIVERSITY_URL'] ) ? $api_config['UNIVERSITY_URL'] : '';


	echo '<br />';

	PopTable(
		'header',
		dgettext( 'Abroad', 'Abroad' )
	);
	

	echo '<table class="width-100p">';

	// University Name
	echo '<tr><td>' . TextInput(
	    $api_uni_name,
	    'values[PROGRAM_USER_CONFIG][UNIVERSITY_NAME]',
	    _( 'University Name' ),
	    'size=100 '
	    ) . '</td></tr>';

    
	    // University API URL
	    echo '<tr><td>' . TextInput(
	        $api_uni_url,
	        'values[PROGRAM_USER_CONFIG][UNIVERSITY_URL]',
	        _( 'API URL' ),
	        'size=100 '
	        ) . '</td></tr>';
	    

	echo '</table>';

	PopTable( 'footer' );

	echo '<br /><div class="center">' . SubmitButton( _( 'Save' ) ) . '</div></form>';
}

