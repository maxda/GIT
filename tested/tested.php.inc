<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/

define ("_TESTED_TABLE",'tested'); 	//define new node addon table for module requests


/**
 *   Authorization profile
 */
define ("_VIEW_TESTED",'view test');  			//only view request status
define ("_INSERT_TESTED",'insert test');			//only insert request
define ("_DELETE_TESTED",'delete test');			//only insert request
define ("_MODIFY_TESTED",'modify test' );		//owner only change
define ("_MANGE_TESTED",'manage test' );		//manage only


/*definizione dello stato */
define ("_PREPARE",1900); 	   // collaudo in preparazione
define ("_TESTED_DONE",2000);      // collaudo terminato



function tested_permission() {
    $data= array(
    	_VIEW_TESTED,
        _INSERT_TESTED,
        _DELETE_TESTED,
        _MODIFY_TESTED,
        _MANGE_TESTED,
    );
    return $data;
}
/**
 *  selezione stati di default
 * @return <type>
 */



/**
 *  hook _status_message, ritorna le informazioni sullo  stato o la lista degli stati disponibili
 * @param <type> $sel
 *      id dello stato da mostrare
 * @return <type>
 *      descrizione dello stato (se sel <> NULL) o la lista degli stati disponibili
 */
function tested_status_message($sel=NULL) {
    $data=array (
        _PREPARE=>'Collaudo in preparazione', 	// richiesta cancellata dalle liste
        _TESTED_DONE=>'Collaudo terminato',	// prove per richieste
        );         
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}
