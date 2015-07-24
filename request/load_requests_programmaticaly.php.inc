<?php
/*
 * tool per l'inserimento automatico deli nodi da File CSV
 */

/**
 * form per l'impostazione di default dei campi obbligatori
 * @return form 
 */

include_once 'request.php.inc';
include_once 'request_aux_function.php.inc';


function requests_bulk_default_form(){

    $form['requests']=array(
        '#type'=>'fieldset',
        '#title'=>'Caricamento Richieste',
        '#description'=>"Carica le richieste in automatico dal file indicato. Il foemato del file deve essere CSV con campi separati da ;.<br>
                         L' ordine dei campi è: 
                         <ol>
                         <li>Titolo</li>
                         <li>Descrizione</li>
                         <li>quantit&agrave;(numerico)</li>
                         <li>Valore (numerico)</li>
                         <li>CND</li>
                         <li>DPT/SOC/SOS di riferimento</li>
                         </ol>" ,
    );
    $form['requests']['type_acq'] = array(
        '#type' => 'radios',
        '#title' => t('Tipologia dell\'acquisto'),
        '#default_value'=> 0,
        '#options' => array('Apparecchiatura nuova(saltare i punti 1. e 2. e compilare dal 3 in poi)',
        'Sostituzione/reintegro apparecchiatura esistente','Dispositivo per la sicurezza e salute dei lavoratori'),
    );
        
    $form['requests']['priority'] = array(
        '#type' => 'radios',
        '#title' => t('Priorit&agrave;'),
        '#default_value' => isset($node->priority) ? $node->priority : 0,
        '#options' => get_priority(),
        '#description'=>t('Indicare la priorit&agrave; dell\'attrezzatura.'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td></tr></table>',
    );

    $form['requests']['value_vat'] = array(
        '#type' => 'select',
        '#title' => t('Regime IVA per tutti.'),
        '#default_value'=> (isset($node->value_vat)?$node->value_vat:0), // default 'con sistema informativo aziendale'
        '#options' => get_iva(),
        '#parents' => array('value_vat'),
        '#weight' => 2,
    );
    $form['requests']['classification']=array(
        '#type'=>'radios',
        '#title'=>t('Classificazione acquisto'),
        '#default_value'=> (isset($node->classification)?$node->classification:'1'),
        '#options' => get_classification(),
        '#parents'=>array('classification'),
        '#required' => TRUE,
    );
     $form['request_bulk_file']=array(
        '#title'=>'Carica la lista delle richieste da file',
        '#description'=>'File CSV a campi delimitati da ; senza delimitatori di stringhe. è indicato sopra.',
        '#type'=>'file',
    );
    return $form;
}



function requests_bulk_default_submit($form,&$form_state) {
    
    if (file_save_upload('request_bulk_file')) {
        $file= file(file_save_upload('request_bulk_file')->filepath);
        $lc=1;
        foreach ($file as $line) {
            insert_request_data($line,$lc,$form_state['values']);
            $lc++;
        }
    }
}


function insert_request_data($data_line,$line_count,$values){
    global $user;
  //  $u=$user;
  //  $user=user_load(array('uid'=>1)); // forza l'utente che effettua il caricamento 
    $line=preg_replace('/("[^"]*)(;)([^"]*")/', '\1|\3', $line); //cleaning dirty line
    $data = explode(';',$line);
    
    if (preg_match('/^[A-Z][0-9]*$/', $data[4])==0) {
         $err='errore nel campo CND:'.$data[4].'. La riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
    if (preg_match('/^[1-9][0-9]*$/', $data[2])==0) {
         $err='errore nel campo quantit&agrave;:'.$data[2].'. La riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
    if (preg_match('/^[1-9][0-9]*$/', $data[3])==0) {
         $err='errore nel campo valore:'.$data[3].'. La riga '.$line_count.' non verrà importata.';
         drupal_set_message($err,'warning'); 
         watchdog('gratcore', $err);
         return;
     }
     
    $node=new stdClass();
    $node->title =$data[0];  
    $node->qta=$data[2];
    $node->cud=$data[4];
    $node->value=$data[3];
 //   $node->struct=$data[5];
    $node->main['descrizione_apparecchiatura']=$data[1];
    $node->type='request';
    $node->priority=$values['priority'];
    $node->value_vat=$values['value_vat'];
    $node->cost=0;
    $node->cost_vat=0;
    $node->type_acq=$values['type_acq'];
    $node->classification=$values['classification'];
    $node->rcq_status=_OPEN;
    if($node=node_submit($node)) node_save($node);        
    $user=$u;
}