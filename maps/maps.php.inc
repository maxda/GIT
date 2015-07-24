<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/


/**
 *   Authorization profile
 */
define ("_ADMIN_MAPS",'maps administrator');  			
define ("_VIEW_MAPS",'view maps');  			




function maps_permission() {
    $data= array(
    	_ADMIN_MAPS,
    	_VIEW_MAPS
    	);  			

    return $data;
}



