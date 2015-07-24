<?php
/* install or update user custom fields
*/

/* main interface to user settings */
function  userAddOns($op, &$edit, &$account, $category ) {

/*
&$edit The array of form values submitted by the user.
&$account The user object on which the operation is being performed.
$category The active category of user information being edited.
*/


    switch ($op) {//What kind of action is being performed. Possible values (in alphabetical order):

        case "after_update":  	//The user object has been updated and changed. Use this if (probably along with 'insert') if you want to reuse some information from the user object.
            break;
        case "categories": 		//A set of user information categories is requested.
            break;
        case "delete": 			//The user account is being deleted. The module should remove its custom additions to the user object from the database.
            break;
        case "form": 			//The user account edit form is about to be displayed. The module should present the form elements it wishes to inject into the form.
            return userFormAddons($edit,$account->struct);
            break;
        case "insert": 			//The user account is being added. The module should save its custom additions to the user object into the database and set the saved fields to NULL in $edit.
            return set_user_struct($account);
            break;
        case "load": 			//The user account is being loaded. The module may respond to this
            get_user_struct($account);
            break;
        case "login": 			//The user just logged in.
            break;
        case "logout":			//The user just logged out. and insert additional information into the user object.
            break;
        case "register": 		//The user account registration form is about to be displayed. The module should present the form elements it wishes to inject into the form.
            return userFormAddons($edit,$account->struct,TRUE);
            break;
        case "submit": 			//Modify the account before it gets saved.
            break;
        case "update": 			//The user account is being changed. The module should save its custom additions to the user object into the database and set the saved fields to NULL in $edit.
            return set_user_struct($account);
            break;
        case "validate": 		//The user account is about to be modified. The module should validate its custom additions to the user object, registering errors as necessary.
            break;
        case "view": 			//The user's account information is being displayed. The module should format its custom additions for display, and add them to the $account->content array.
            if (isset($account->struct))
                $account->content['struct']=array(
                    '#type'=>'item',
                    '#title'=>'Strutture di riferimento',
                    '#value'=>implode('<br>',get_structure($account->struct,NULL,NULL,'Tutte')).'<br>',
                    '#weight'=>0
                );
            break;
    }

}

function userFormAddons(&$form,$struct,$register=FALSE) {
    global $user;
    // add fields
    //$form['account']=;
   // else $ff=array();

    $ff['struct'] = array(
        '#type' => 'select',
        '#description'=>'Struttura al quale afferisce la persona. Questa impostazione permette di attribuire eventuali eventi relativi ad una struttura a questa determinata persona.
                        Usare il tasto <i>SHIFT</i> per le selezioni multiple continue e il tasto <i>CTRL</i> pel le selezioni multiple casuali.',
        '#title' => t('Struttura di riferimento' ),
        '#default_value' => isset($struct) ? $struct : array(),
        '#options' => get_structure(),
        '#access'=>($user->uid==1),
        '#multiple'=>TRUE,
        '#size'=> min(10,get_structure()),
        '#weight'=>0,
        '#access'=>user_access(_MANAGE_PROFILE) || user_access(_MANAGE_SETTINGS)
    );
    if ($register)
        return $ff; // non sovrascrive il fieldset "account"
    else
        return array('account'=>$ff);
}

function get_user_struct(&$account){
    if (isset($account->struct) ) return TRUE;
    $qy="SELECT struct FROM "._STRUCT_REFERRERS." WHERE uid=%d ORDER BY sort";
    $rs=db_query($qy,$account->uid);
    $account->struct=array();
    while($rw=  db_fetch_object($rs)){
        $account->struct[]=$rw->struct;
    }
}

function set_user_struct(&$account){
    $qy="DELETE FROM "._STRUCT_REFERRERS." WHERE uid=%d";
    db_query($qy,$account->uid);
    foreach ($account->struct as $st){
        $qy="INSERT INTO "._STRUCT_REFERRERS." (uid,struct,created) VALUES (%d,%d,%d);";
        db_query($qy,$account->uid,$st,time());
    }

}
