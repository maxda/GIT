<?php

define(_SI3C_CONNECT,'10.24.5.145:1521/si3c.srv1.udinesmm.italtbs.com');
define(_SI3C_USR,'REFERENTE');
define(_SI3C_PWD, 'udine');
define(_ASCOTT_CONNECT,'172.25.1.51:1547/conanew');
define(_ASCOTT_USR,'E14SEL');
define(_ASCOTT_PWD,'sel');
        
function admin_settings(){
    return drupal_get_form('admin_db_settings');
}


function admin_db_settings(){
    $form['si3c']=array(
        '#type'=> 'fieldset',
        '#description'=>'Parametri connessione: &lt;<i>host(IP or name)</i>:<i>port(number)</i>/<i>sid or service name or schema</i>&gt;',
        '#title'=>'Configurazione DB Si3c',
        '#collapsible'=>TRUE,
        '#collapsed'=>TRUE        
    );
    $form['si3c']['si3c_connect']=array(
        '#type'=> 'textfield',
        '#description'=>'Stringa di connessione al db',
        '#title'=>'Connessione al DB',
        '#default_value'=>variable_get('si3c_connect',_SI3C_CONNECT)   
    );
    $form['si3c']['si3c_usr']=array(
        '#type'=> 'textfield',
        '#title'=>'Utente',
        '#default_value'=>variable_get('si3c_usr',_SI3C_USR)   
    );
    $form['si3c']['si3c_pwd']=array(
        '#type'=> 'textfield',
        '#title'=>'Password',
        '#default_value'=>variable_get('si3c_pwd',_SI3C_PWD)   
    );
    
    //$name='SI3C.T_APPA';
    $form['si3c']['CUSTOM_TABLES']= admin_table_settings();
/*    $form['si3c']['tab']['#type']='fieldset';
    $form['si3c']['tab']['#title']="Ettichette tabella $name";
    $form['si3c']['tab']['#description']="Modifica le ettichette della tabella $name";
    $form['si3c']['tab']['#collapsible']=TRUE;
    $form['si3c']['tab']['#collapsed']=TRUE;
    $form['si3c']['tab']['#theme']='admin_table_settings';
 */
    
    $form['ascott']=array(
        '#type'=> 'fieldset',
        '#description'=>'Parametri connessione: &lt;<i>host(IP or name)</i>:<i>port(number)</i>/<i>sid or service name or schema</i>&gt;',
        '#title'=>'Configurazione DB ascott',
        '#collapsible'=>TRUE,
        '#collapsed'=>TRUE        
    );
    $form['ascott']['ascott_connect']=array(
        '#type'=> 'textfield',
        '#description'=>'Stringa di connessione al db',
        '#title'=>'Connessione al DB',
        '#default_value'=>variable_get('ascott_connect',_ASCOTT_CONNECT)   
    );
    $form['ascott']['ascott_usr']=array(
        '#type'=> 'textfield',
        '#title'=>'Utente',
        '#default_value'=>variable_get('ascott_usr',_ASCOTT_USR)   
    );
    $form['ascott']['ascott_pwd']=array(
        '#type'=> 'textfield',
        '#title'=>'Password',
        '#default_value'=>variable_get('ascott_pwd',_ASCOTT_PWD)   
    );
    $form['config']=array(
        '#type'=> 'textarea',
        '#title'=>'Configurazione',
        '#description'=>'Carica la configurazione serializzata delle tabelle',
        '#default_value'=>var_export(unserialize(variable_get('CUSTOM_TABLES',serialize(array()))),TRUE)
    );
    
    $form['salva']=array(
            '#type'=>'button',
            '#value'=>'salva',
            '#executes_submit_callback'=>TRUE,
        );
    $form['Carica configurazione']=array(
            '#type'=>'button',
            '#value'=>'carica',
            '#executes_submit_callback'=>TRUE,
        );
    $form['Reset configurazione']=array(
            '#type'=>'button',
            '#value'=>'reset',
            '#executes_submit_callback'=>TRUE,
        );
    $form['Annulla']=array(
            '#type'=>'button',
            '#value'=>'Annulla',
            '#executes_submit_callback'=>TRUE,
        );
    $form['#submit'][]='admin_settings_submit';
    
    return $form;
}
/**
 * crea una from  per ettichettare la tabella
 * 
 * @return $form 
 */
function admin_table_settings(){
    $custom_tables=unserialize(variable_get('CUSTOM_TABLES',serialize(array())));
    foreach($custom_tables as $name=>$table){
        $form[$name]=load_custom_table($name,$table);
        $form[$name]['#type']='fieldset';
        $form[$name]['#title']="Ettichette tabella $name";
        $form[$name]['#description']="Modifica le ettichette della tabella $name";
        $form[$name]['#collapsible']=TRUE;
        $form[$name]['#collapsed']=TRUE;
        $form[$name]['#theme']='admin_table_settings';   
    }
    return $form;
}


/** 
* crea la form per la gestione delle ettichette della tabella indicata
 * @param string $name nome tabella
 * @return array forma di modifica
 */
function load_custom_table($name,$table){
//$table_struct=table_structures($name);
  
    $form['table_name']=array('#type'=>'hidden','#value'=>$name);
    foreach($table as $key=>$settings){
        $form['name'][$key]=array(
            '#value'=>$key,
        );
        $form['show'][$key]=array(
            '#type'=>'checkbox',
            '#default_value'=>$settings['show'],
            '#parents'=>array('CUSTOM_TABLES',$name,$key,'show'),
       );
        $form['weigth'][$key]=array(
            '#type'=>'textfield',
            '#default_value'=>$settings['weigth'],
            '#size'=>3,
            '#parents'=>array('CUSTOM_TABLES',$name,$key,'weigth'),
        );
        $form['title'][$key]=array(
            '#type'=>'textfield',
            '#default_value'=>$settings['title'],
            '#size'=>50,
            '#parents'=>array('CUSTOM_TABLES',$name,$key,'title'),
        );
    }
    return $form;    
}

function admin_settings_submit($form, &$form_state){
    $values=$form_state['values'];
    switch ($values['op']){     
        case 'salva':
            if (isset($values['CUSTOM_TABLES'])){
                variable_set('CUSTOM_TABLES', serialize($values['CUSTOM_TABLES']));
            }
            if (isset($values['si3c_connect'])){
                variable_set('si3c_connect',$values['si3c_connect']);                  
                variable_set('si3c_usr',$values['si3c_usr']);
                variable_set('si3c_pwd',$values['si3c_pwd']);
            }
            if (isset($values['ascott_connect'])){
                variable_set('ascott_connect',$values['ascott_connect']);                  
                variable_set('ascott_usr',$values['ascott_usr']);
                variable_set('ascott_pwd',$values['ascott_pwd']);
            }
        break;
        case 'carica':
             eval('$config='.$values['config'].';');
             variable_set('CUSTOM_TABLES', serialize($config));
        break;
        case 'reset':
            variable_del('CUSTOM_TABLES');
        break;
    }
    return true;
}
/**
 *  temizza la tabella delle ettichette  
 * @param type $form
 * @return type 
 */
function theme_admin_table_settings($form){
    $rows=array();
    foreach(element_children($form['name']) as $key ){
        $row=array();
        $weigth=$form['weigth'][$key]['#default_value'];
        $row[]=drupal_render($form['name'][$key]);
        $row[]=drupal_render($form['show'][$key]);
        $row[]=drupal_render($form['weigth'][$key]);
        $row[]=drupal_render($form['title'][$key]);
        if (is_numeric($weigth)){
            $rows[(integer)$weigth]=$row;
        }
        else {
            $rows[]=$row;
        }
    }
    ksort($rows,SORT_NUMERIC );
    $header=array('Field','Enable','weigth','Label');
    $output=theme('table',$header,$rows);
    $output.=drupal_render($form);
    return $output;
}

/** 
 *  carica le ettichette salvate per la specifica tabella
 * @param type $table_name
 * @return type 
 
function table_structures($table_name){
    $custom_tables=unserialize(variable_get('CUSTOM_TABLES',serialize(array())));
    if (isset($custom_tables[$table_name])) {
        return $custom_tables[$table_name];
    }
    else {
        $custom_tables[$table_name]=array();
        variable_set('CUSTOM_TABLES',serialize($custom_tables));
        return array();
    }
}*/

/**
 * funzione per sostituire i campi del DB con le ettichette personalizzate
 * @param string/array $table nome della tabella o array degli alias=> tabelle utilizzate
 * @param string $field nome del campo da ettichettare
 * @return string nuova ettichetta o falso se non la trova 
 */
function labellize_field($table,$field){
//    $fields=table_structures($table);
    //la $table contiene gli alias?
    if (is_array($table)){
        //separazione degli alias
        $alias=substr($field,0, strpos($field,'.'));
        $table=$table[$alias];
        $field=substr($field,strlen($alias)+1,strlen($field)-strlen($alias)-1);
    }
    
    if (is_numeric($field)) return FALSE;// filtra la doppia estrazione dai DB: ordinale e key->val
    $custom_tables=unserialize(variable_get('CUSTOM_TABLES',serialize(array())));
    if (isset($custom_tables[$table])) {
        $fields=$custom_tables[$table];
    }
    else {
        $custom_tables[$table]=array();
 //      $qy="SLECT column_name FROM user_tab_cols WHERE table_name=upper('$table')";
        variable_set('CUSTOM_TABLES',serialize($custom_tables));
        $fields=array();
    }
    if(!isset($fields[$field])) { 
       // inizializza al volo le ettichette mancanti dei campi  
       $custom_tables=unserialize(variable_get('CUSTOM_TABLES',serialize(array())));
       $custom_tables[$table][$field] =array('weigth'=>'','title'=>$field,'show'=>TRUE);
       variable_set('CUSTOM_TABLES',serialize($custom_tables));
       return ($fields[$field]['show'])?$field:FALSE;
    }
       
    if($fields[$field]['show']) return $fields[$field]['title'];
    return FALSE;
}

/**
 * restituisce la stringa deicampi abilitati nella tabella 
 * @param string $name nome tabella
 * @return string 
 */
function get_table_fields($name,$prefix=NULL){
    $custom_tables=unserialize(variable_get('CUSTOM_TABLES',serialize(array())));
    unset($fields);
    $i=0;
    if (isset($custom_tables[$name])){
        
        if($custom_tables[$name]!==array()){
            foreach($custom_tables[$name] as $label=>$field){
                if($field['show']==FALSE) continue;
                if ($prefix) $label=$prefix.'.'.$label;
                $fields[]=$label;
                $order[]=is_numeric($field['weigth'])?$field['weigth']:$i++;
            }
        }     
    }
    if (isset($fields)){ 
        array_multisort($order,$fields);
        return implode (',', $fields);
    }
    else return '*';
}