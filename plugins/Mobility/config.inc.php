<?php
/**
 * Plugin configuration interface
 *
 * @package Mobility plugin
 */

// Check the script is called by the right program & plugin is activated.
if ( $_REQUEST['modname'] !== 'School_Setup/Configuration.php'
	|| ! $RosarioPlugins['Mobility']
	|| $_REQUEST['modfunc'] !== 'config' )
{
	$error[] = _( 'You\'re not allowed to use this program!' );

	echo ErrorMessage( $error, 'fatal' );
}

// Note: no need to call ProgramTitle() here!

if ( isset( $_REQUEST['save'] )
	&& $_REQUEST['save'] === 'true' )
{
    
    if ( ! empty( $_REQUEST['values'] )
        && ! empty( $_POST['values'] )
        && AllowEdit() )
    {
        foreach ( (array) $_REQUEST['values'] as $request_id => $columns )
        {
            $sql = "UPDATE UNIVERSITIES_ABROAD SET ";
            
            foreach ( (array) $columns as $column => $value )
            {
                $sql .= DBEscapeIdentifier( $column ) . "='" . $value . "',";
            }
            
            $sql = mb_substr( $sql, 0, -1 ) .
            " WHERE UNIVERSITY_ID='" . $request_id . "'";
            
           
            DBQuery( $sql );
        }
    }
    


	// Unset save & values & redirect URL.
	RedirectURL( 'save', 'values' );
}


// New.
if ( isset( $_REQUEST['new'] )
    && $_REQUEST['new'] === 'true' )
{
    
    if ( ! empty( $_POST ) && AllowEdit() )
    {

        DBQuery( "INSERT INTO UNIVERSITIES_ABROAD (UNIVERSITY_NAME, UNIVERSITY_URL, UNIVERSITY_TOKEN)
			VALUES ('" . $_POST['UNIVERSITY_NAME'] . "', '" . $_POST['UNIVERSITY_URL'] . "', '" . $_POST['UNIVERSITY_TOKEN'] . "')" );
        
    }
        // Unset modfunc & ID & redirect URL.
        RedirectURL( array('add', 'new', 'values') );
}

// Remove.
if ( isset( $_REQUEST['remove'] )
    && $_REQUEST['remove'] === 'true' )
{
    if ( DeletePrompt( _( 'Request' ) ) )
    {
        DBQuery( "DELETE FROM UNIVERSITIES_ABROAD
			WHERE UNIVERSITY_ID='" . $_REQUEST['id'] . "'" );
        
        // Unset modfunc & ID & redirect URL.
        RedirectURL( 'remove', 'values' );
    }
}


// Add.
if ( isset( $_REQUEST['add'] )
    && $_REQUEST['add'] === 'true' )
{
    
    PopTable(
        'header',
        dgettext( 'University Abroad', 'University Abroad' )
        );
    
    echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&tab=plugins&modfunc=config&plugin=Mobility&new=true" method="POST">';
    echo '<table class="width-100p">';
    
    // University Name
    echo '<tr><td>' . TextInput(
        '',
        'UNIVERSITY_NAME',
        _( 'University Name' ),
        'size=100 '
        ) . '</td></tr>';
        
        
        // University API URL
        echo '<tr><td>' . TextInput(
            '',
            'UNIVERSITY_URL',
            _( 'API URL' ),
            'size=100 '
            ) . '</td></tr>';
            
            // University TOKEN
            echo '<tr><td>' . TextInput(
                '',
                'UNIVERSITY_TOKEN',
                _( 'Token' ),
                'size=100 '
                ) . '</td></tr>';
            echo '</table>';
            
            echo '<br /><div class="center">' . SubmitButton( _( 'Save' ) ) . '</div></form>';
            
            PopTable( 'footer' );
            
            
            
}


if ( empty( $_REQUEST['save']) && 
    empty( $_REQUEST['remove'])&&
    empty( $_REQUEST['add'])&&
    empty( $_REQUEST['new']))
{
    
    $functions = array(
        'UNIVERSITY_NAME' => '_makeUniversity',
        'UNIVERSITY_URL' => '_makeUniversity',
        'UNIVERSITY_TOKEN' => '_makeUniversity',
    );
    
    $requests_RET = DBGet( "SELECT * FROM UNIVERSITIES_ABROAD ", $functions );
    
    $columns = array(
        'UNIVERSITY_NAME' => _( 'University Name' ),
        'UNIVERSITY_URL' => _( 'University URL API' ),
        'UNIVERSITY_TOKEN' => _( 'University Token' ),
    );
    
    $link['remove'] = array(
        'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=plugins&modfunc=config&plugin=Mobility&remove=true',
        'variables' => array( 'id' => 'UNIVERSITY_ID' ),
    );
    
    $link['add'] = array(
        'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=plugins&modfunc=config&plugin=Mobility&add=true',
    );
    $link['add']['title'] = _( 'Add Partner University' );
    echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&tab=plugins&modfunc=config&plugin=Mobility&save=true" method="POST">';
    
    
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


function _makeUniversity( $value, $column )
{
    global $THIS_RET;
    
   
    return TextInput(
        $value,
        'values[' . $THIS_RET['UNIVERSITY_ID'] . ']['.$column.']',
        '',
        'size=50 ',
        false
        );

}

