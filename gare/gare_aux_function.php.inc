<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for gare module
*/

define ("_GARE_TABLE",'gare'); 	//define new node addon table for module gare

define ("_GARE_SESSION_FILTER","gare_filter");  // define session filter entry point

/**
*   Authorization profile */


define (_AUTH_VIEW,'Solo visione');  		//only view info
define (_AUTH_CHANGE_LOW,'Modifica di basso livello gara');		//modify all data but administrator role
define (_AUTH_CHANGE_HI,'Modifica di alto livello  gara' );		//modify all
define (_AUTH_OWNER,'Solo proprietario gara' );			//owner only change
define (_ROOT_OWNER,'Controllo di tutto sulla gara' );			//root

define (_PUBLISH_NOTIFY_MAIL,'notify_mail' );			// variabile per la notifica e-mail


/*
 unique hook_perm funtion
*/

function gare_permission() {
  return array( _AUTH_VIEW,
		_AUTH_CHANGE_LOW,
		_AUTH_CHANGE_HI,
		_AUTH_OWNER,
		_ROOT_OWNER);
}



/*definizione dello stato */

define ("_PROPOSED",500);
define ("_ON_BUILD",501);
define ("_IN_PROGRESS",502);
define ("_RELEASED",503);
define ("_EXPIRED",599);



/* hook status for core*/
function gare_status_message($sel=NULL){
   $data=array (
   	   _PROPOSED=>'Proposta/Pianificata',
   	   _ON_BUILD=>'Presa in carico',
   	   _RELEASED=> 'In sospeso',
   	   _IN_PROGRESS=>'Gara in Corso',
   	   _EXPIRED=>'Conclusa',
	   );

	if ($sel!==NULL) return $data[$sel];
	else return $data;
}



/*------------------------------------------------
functions for select, option buttons and checkbox
--------------------------------------------------*/


/*** prototype --------------------------*/


