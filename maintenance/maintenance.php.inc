<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/

//define ("_TESTED_TABLE",'tested'); 	//define new node addon table for module requests


/**
 *   Authorization profile
 */
define ("_VIEW_MAINTENANCE",'view maintenance');	//only view 
define ("_INSERT_MAINTENANCE",'insert maintenance');			//only insert 
define ("_DELETE_MAINTENANCE",'delete maintenance');			//only delete
define ("_MODIFY_MAINTENANCE",'modify maintenance' );		// only modify 
define ("_ADMIN_MAINTENANCE",'administration maintenance' );		//manage all






function maintenance_permission() {
    $data= array(
    	_VIEW_MAINTENANCE,
        _INSERT_MAINTENANCE,
        _DELETE_MAINTENANCE,
        _MODIFY_MAINTENANCE,
        _ADMIN_MAINTENANCE,
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
function maintenance_status_message($sel=NULL) {

}

/*

function update_list() {
    $form['#attributes'] = array('enctype' => "multipart/form-data");
    $form['upload_cnd']=array(
        '#title'=>'Carca il catalogo codici CND',
        '#description'=>'File CSV a campi delimitatu da ; con o senza delimitatori di stringhe. Il formato deve essere cos&igrave; impostato:<br>
		                 codice-CND;descrizione (senza puntovirgole);note;CIVAB;livello(numero);lettera ultimo livello(S o N);',
        '#type'=>'file',
    );
    $form['upload_struct']=array(
        '#title'=>'Carica il catalogo delle strutture',
        '#description'=>'File CSV a campi delimitati da ; senza delimitatori di stringhe. Il formato deve essere senza intestazione.',
        '#type'=>'file',
    );
    $form['upload']=array(
        '#title'=>'Carica i cataloghi',
        '#value'=> 'Carica',
        '#type'=>'submit',
    );
//    $form['exec']=array(
//        '#title'=>'Carica i cataloghi',
//        '#value'=> 'exec',
//        '#type'=>'submit',
//    );
    return $form;
}
function update_list_validate($form,&$form_state) {

    switch ($form_state['values']['op']) {
        case 'Carica':
            $i=0;
            $msg='';
            if (file_save_upload('upload_cnd')){
                $file= file(file_save_upload('upload_cnd')->filepath);
                foreach ($file as $line) {
                    $data = explode(';',$line);
                    $i++;
                    if (count($data)<>5) {
                        $msg="Errore caricamento catalogo CND: alla riga $i ci sono pi&ugrave; campi del consentito";
                        continue;
                    }
                }
            }
            if (file_save_upload('upload_struct')){
                $file= file(file_save_upload('upload_struct')->filepath);
                foreach ($file as $line) {
                    $data = explode(';',$line);
                    $i++;
                    if (count($data)<>5) {
                        $msg="Errore caricamento struttura: alla riga $i ci sono pi&ugrave; campi del consentito";
                        continue;
                    }
                }
            }
            break;
    }
}

function update_list_submit($form,&$form_state) {
    switch ($form_state['values']['op']) {
        case 'Carica':
            $file= file(file_save_upload('upload_cnd')->filepath);
            $lc=1;
            foreach ($file as $line) {
                update_CND_LIST($line,$lc);
                $lc++;
            }
            break;
        case 'exec':
            include_once('transfert_from_access.php.inc');
            transfert_all();
            break;
    }
}

function update_CND_LIST($line,$line_count){
     $line=preg_replace('/("[^"]*)(;)([^"]*")/', '\1|\3', $line); //cleaning dirty line
     $data = explode(';',$line);
     // verifiche correttezza campi
     if (preg_match('/^[A-Z][0-9]*$/', $data[0])==0) {
         $err='errore nel primo campo:'.$data[0].'. La riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
     if (!is_numeric($data[4])) {
         $err='errore nel III campo:'.$data[4].'. Non è un intero,la riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
     $data[5]= substr(strtoupper($data[5]),0,1);
     if (preg_match('/^[S|N]$/', $data[5])==0 ) {
         $err='errore nel IV campo:'.$data[5].'. Non è S/N, la riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
     //verifica se il codice esiste
     $qy=db_query("SELECT * FROM "._CUD_TABLE." WHERE id='%s'",$data[0]);
     $test=TRUE;
     if($dd=db_fetch_array($qy)){
         $test=$test && $dd['id']==$data[0];
         $test=$test && $dd['DESC']==$data[1];
         $test=$test && $dd['note']==$data[2];
         $test=$test && $dd['Tree_level']==$data[4];
         $test=$test && $dd['last_level']==$data[5];
         $test=$test && $dd['CIVAB']==$data[3];

         if ($test) return; //il codice esiste e non è cambiato 
     }
     db_query("REPLACE INTO "._CUD_TABLE." (id,`DESC`,note,Tree_level,last_level,CIVAB,created) 
                                      VALUES ('%s','%s','%s',%d,'%s','%s',%d)",
             $data[0],$data[1],$data[2],$data[4],$data[5],$data[3],time());
     $msg='Inserito o aggiornato codice CND <i>'.$data[0].'</i>.';
     drupal_set_message($msg,'status'); 
     watchdog('gratcore', $msg);
 
}
*/