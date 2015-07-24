<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for budget module
*/

define ("_BUDGET_TABLE",'budget'); 	        //define new node addon table for module budget
define ("_BONDED_REQUESTS",'bonded_requests'); 	//define nodes bonded to  budget
define ("_FOUNDS",'founds'); 	                //define founds
define ("_FOUNDS_BINDINGS",'founds_bindings'); 	//define founds destinations

/**
*   Authorization profile */

/**/
define (_ONLY_VIEW,'Visione budget');  		//pu� solo vedere i dati
define (_MODIFY,'Modifica budget' );			//pu� modificare i dati ma non bloccare
define (_MODIFY_REQUEST,'Modifica richieste associate' );			//pu� modificare i dati delle richieste
define (_DELETE_BUDGET,'Cancellazione budget' );	//abilitazione alla cancellazione
/*
 unique hook_perm function
*/

function budget_permission() {
  return  array( _ONLY_VIEW,_MODIFY,_MODIFY_REQUEST,_DELETE_BUDGET);
}



/*definizione dello stato */

define ("_OPEN_BDG",1);
define ("_MODIFIED_BDG",2);
define ("_ENDED_BDG",100);


/* hook status for core*/
function budget_status_message($sel=NULL){
   $data=array (
   	   _OPEN=>'Aperto',
   	   _MODIFIED=>'Modificato',
   	   _ENDED=>'Esaurito',
	   );

	if ($sel!==NULL) return $data[$sel];
	else return $data;
}


function get_founds($sel=NULL) {
    $data=array (
        1=>'Finanziamento regionale',
        2=>'Fondi Propri',
        3=>'Donazione',
        4=>'Comodato gratuito',
        5=>'Service');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}