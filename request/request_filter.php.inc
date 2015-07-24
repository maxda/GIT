<?php


/*******************************************************************************
 * filter functions
 *******************************************************************************/


// ettichetta per il filtro nella sessione
define ("_SESSION_FILTER",'request_filter');

//form del filtro
function requests_filter() {
    global $user;
    $filter = isset($_SESSION[_SESSION_FILTER]) && count($_SESSION[_SESSION_FILTER]);
    //  print_r($_SESSION);
    //  $session = is_array($session) ? $session : array();

    $filters=request_get_filter(); //recupero dei filtri eventualmente memorizzati
    $form['flt']=array(
        '#type'=>'fieldset',
        '#title'=>'Filtro'.(($filter)?' (filtro attivo)':''),
        '#collapsible'=>TRUE,
        //'#collapsed'=>!$filter,
        //'#attributes'=> array('class'=>'active'),
    );
    

    //$form['flt']['current'][]=array('#value'=>$filters['description']);

    $form['flt']['fields']=array('#prefix' => '<div class="container-inline">', '#suffix' => '</div>');

    $status=array('all'=>'Tutti');
    $status+=request_status_message();
    $form['flt']['fields']['status']=array(
        '#type'=>'checkboxes',
        '#title'=>'Stato',
        '#options'=>request_status_message(),
        '#default_value'=>request_status_default(),
        '#SQL_FIELD'=>'r.rcq_status',
        '#suffix'=>'<br>',
    );

    if(!(user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ)))
        $struct=$user->struct;
    else
        $struct=NULL;

    $form['flt']['fields']['struct']=array(
        '#type'=>'select',
        '#title'=>'Struttura richiedente',
        '#options'=>get_structure(NULL,40,$struct),
        '#default_value'=>0,
        '#SQL_FIELD'=>'struct',
    );
    $form['flt']['fields']['structII']=array(
        '#type'=>'fieldset',
        '#title'=>'Struttura',
        '#SQL_FIELD'=>'struct',
    );
    $form['flt']['fields']['structII']['tree']=build_structure_form($struct,isset($form_state['values']['TREE'])?$form_state['post']['TREE']:NULL);
    
    $form['flt']['fields']['type_acq']=array(
        '#type'=>'select',
        '#title'=>'Tipo di acquisto',
        '#options'=>add_unselection(get_type_acq()),
        '#default_value'=>-1,
        '#SQL_FIELD'=>'type_acq',

    );
    $form['flt']['fields']['classification']=array(
        '#type'=>'select',
        '#title'=>'Classificazione',
        '#options'=>add_unselection(get_classification()),
        '#default_value'=>-1,
        '#SQL_FIELD'=>'classification',
        '#access'=>(user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ)),
    );
    $form['flt']['fields']['priority']=array(
        '#type'=>'select',
        '#title'=>'Priorit&agrave;',
        '#options'=>add_unselection(get_priority(NULL,TRUE)),
        '#default_value'=>-1,
        '#SQL_FIELD'=>'priority',
        '#access'=>(user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ)),


    );
    $year=date("Y");
    $form['flt']['fields']['year_created']=array(
        '#type'=>'select',
        '#title'=>'Anno',
        '#options'=>Array($year=>"$year",--$year=>"$year",--$year=>"$year",--$year=>"$year",--$year=>"$year",--$year=>"$year",-1=>'(seleziona)'),
        '#default_value'=>-1,
        '#SQL_FIELD'=>'Year(from_UNIXTIME(nd.created))',

    );/**/
    $form['flt']['button']['filter']=array(
        '#type'=>'submit',
        '#value'=>'Filtra',
        '#execute_submit_callbck'=>TRUE,

    );
    if ($filter) {
        $form['flt']['button']['remove_last']=array(
            '#type'=>'submit',
            '#value'=>'Elimina ultimo',
            '#execute_submit_callbck'=>TRUE,

        );
        $form['flt']['button']['remove']=array(
            '#type'=>'submit',
            '#value'=>'Rimuovi filtro',
            '#execute_submit_callbck'=>TRUE,
        );
        $form['flt']['button']['add']=array(
            '#type'=>'submit',
            '#value'=>'Aggiungi selezione',
            '#execute_submit_callbck'=>TRUE,

        );
    }
    if (isset($filters['description']) && $filters['description']<>'' )
        $form['flt']['description']=array(
            '#type'=>'fieldset',
            '#title'=>'Filtro',
            '#value'=>$filters['description'],
            '#attributes'=>array('class'=>'filterSet'),
        );
    return $form;
}

//funzione per la selezione vuota

function add_unselection(array $data) {
    $data[-1]='(seleziona)';
    return $data;
}

/**
 * azione del submit del filtro, imposta i parametri nelle variabili di sessione
 * @param array $form
 * @param array $form_state
 * @return none
 */
function requests_filter_submit($form, &$form_state) {
    $op=$form_state['values']['op'];
    $values=$form_state['values'];
    $values['struct_selection']=$_POST['struct_selection'];
    switch ($op) {
        case 'Filtra':
            request_reset_filter();
            foreach ($form['flt']['fields'] as $field => $array) {
                if (is_array($array) && isset($array['#type']) && ($array['#type']=='select' || $array['#type']=='checkboxes')) {
                    $id=$values[$field];
                    $list=flat_array($array['#options']);
                    if($id!=$array['#default_value']) {
                        request_set_filter($array['#SQL_FIELD'],$array['#title'],$id,$list);
                    }
                }
            }
            break;
        case 'Aggiungi selezione':
            foreach ($form['flt']['fields'] as $field => $array) {
                if (is_array($array) && isset($array['#type']) && ($array['#type']=='select' || $array['#type']=='checkboxes')) {
                    $id=$values[$field];
                    $list=flat_array($array['#options']);
                    if($id!=$array['#default_value']) {
                        request_set_filter($array['#SQL_FIELD'],$array['#title'],$id,$list);
                    }
                }
            }
            break;
        case 'Elimina ultimo':
            request_drop_filter();
            break;
        case 'Rimuovi filtro':
            request_reset_filter();
            break;

    }
    $form_state['redirect']='request/list';
    return;
}

/**
 * ricostruisce la WHERE di filtraggio impostata dalla form del filtro
 * @return SQL string (filtro)
 */
function request_get_filter() {
    $result=array('SQL'=>'','description'=>'');
    $session=(isset($_SESSION[_SESSION_FILTER])?$_SESSION[_SESSION_FILTER]:array());
    foreach($session as $filter) {
        $value=$filter['value'];
        if ($filter['value']!=''&& isset($filter['field'])) {
            if (is_array($value)) {
                $res='';$desc='';
                foreach($value as $key => $val) {
                    if ($val <>0) {
                        $res.=($res<>''?', ':'').$key; //compone la lista delle chiavi per la struttura IN ( )
                        $desc.= ($desc<>''?'+':'').$filter['label'][$key];
                    }
                }
                if ($res <> '') {
                    $result['SQL'].= ($result['SQL']<>''?' AND ':'').$filter['field'].' IN ('.$res.') ';
                    $result['description'].= ($result['description']!==''?'<i> con </i>':'').$filter['field_label'].' in <strong>'.$desc.'</strong> ';
                }
            }
            else {
                $result['SQL'].= ($result['SQL']<>''?' AND ':'').$filter['field'].'='.$value.' ';
                //TODO: la select gerarchica da problemi con questo tipo di ettichette andrebbe resa a due soli livelli
                $result['description'].= ($result['description']!==''?'<i> con </i>':'').$filter['field_label'].'=<strong>'.(isset($filter['label'])?$filter['label'][$value]:$value).'</strong> ';
            }
            $result['description'].='<br>';
        }
    }
    return $result;

}

/**
 * Imposta il filtro nella sessione corrente
 * @param string $field : campo da filtrare nel DB
 * @param string $field_label : ettichetta da visualizzare per il campo 
 * @param type $value : valore del campo
 * @param type $label : ettichetta da associare al valore 
 */
function request_set_filter($field,$field_label,$value,$label) {
    $filter=array();
    $filter['value']=$value;
    $filter['field']=$field;
    $filter['field_label']=$field_label;
    $filter['label']=$label;
    //	print_r($filter);
    if (!isset($_SESSION[_SESSION_FILTER]))$_SESSION[_SESSION_FILTER]=array();
    $_SESSION[_SESSION_FILTER][]=$filter;
}
/**
 *  cancella l'ultimo impostato
 */
function request_drop_filter() {
    array_pop($_SESSION[_SESSION_FILTER]);
}

/**
 *  canella tutti i filtri impostati
 */
function request_reset_filter() {
    unset($_SESSION[_SESSION_FILTER]);
}

/**
 * riduce l'array a uno monodimensionale
 * @param array $array
 * @return array
 * 
 */
function flat_array($array){
    reset($array);
    while (list($key, $value)=each($array)){
        if(is_array($value))
            foreach ($value as $k => $val) $array[$k]=$val;
        else
            $ret[$key]=$value;
        unset($array[$key]);
        reset($array);
    }
    return $ret;
}