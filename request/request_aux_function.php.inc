<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/
include_once("request.php.inc");
//include_once("request_filter.php.inc");



/**
 *  serializza i dati di main per inserirli nel db
 * @param <object> $node
 *      oggetto da dove recuperare i dati
 * @return <string>
 *      dati serializzati
 */
function serialize_data($node) {

    if (!isset($node->main)) {
        return serialize(array(
            'reason' => $node->reason,
            'context' =>  $node->context,
            'needs' =>    $node->needs,
            'tech_needs' => $node->tech_needs,
            'exclusive' => $node->exclusive  ,
            'options' => $node->options,
            'manufactures' => $node->manufactures
        ));
    }
    else {
        $data['main']=array();
        foreach ($node->main as $key => $val) {
            $data['main'][$key]=$val;
        }
        return serialize($data);
    }
}
/**
 *  ecupera i dati serializzati e li immette nel nodo
 * @param <object> $node
 *      nodo destinazione
 * @param <string> $src
 *      dati seriali
 * @return
 *      nulla
 */
function unserialize_data(&$node,$src) {
    $data=unserialize($src);
    if (!is_array($data)) return;
    foreach ($data as $key => $val) $node->$key=$val;
    if (isset($data['main'])&& is_array($data['main'])) {
        if (!isset($node->main)) $node->main=array();
        foreach ($data['main'] as $key => $val) $node->main[$key]=$val;
    }
}

/**
 *  genera lastringa dei checkbox selezionati
 * @param <array> $data
 *      opzioni possibili
 * @param <int> $sel
 *      opzioni selezionate
 * @return <string>
 *      stringa descrittiva selezioni oppure l'array delle opzioni
 */
function main_option_selector($data,$sel) {
    $list='';
    if ($sel===NULL)
        return $data;
    else
        foreach($sel as $key => $val) $list.=($val<>0)?$data[$key].', ':'';
    return $list;
}


function request_listcheck($form_state,$select='all') {
//build request checklist form
    global $user;
    drupal_add_css('request/request.css','module');
    $ff=request_get_filter();
    $filter=$ff['SQL'];
    $header=array(
        array(),//'data'=> t('ID'),'field'=>'r.nid'),
        array('data'=> t('Titolo    <i>(note)</i>'),'field'=>'nd.title'),
        array('data'=> t('quantit&agrave;'),'field'=>'qta'),
        array('data'=> t('struttura'),'field'=>'st.description'),
        array('data'=> t('CND'),'field'=>'cud'),
        array('data'=> t('Valore &euro;'),'field'=>'value'),
//        array('data'=> t('Inserito'),'field'=>'nd.created'),
        array('data'=> t('Piano'),'field'=>'a.title'),
        array('data'=> t('Stato'),'field'=>'r.rcq_status'),
    );

    if (strpos($filter,'r.rcq_status') === FALSE)
        $filter.=($filter<>''?' AND':'').' '._PENDING_REQUESTS;

    if (!(user_access(_FULL_MANGE_RCQ) || user_access(_MANGE_RCQ))) {

        if(isset($user->struct) && (($struct=queryStruct($user->struct))<>FALSE)) {
            $filter=" r.struct IN (".$struct.') '.(($filter<>'')?' AND ':' ').$filter;
        }
        else if(!isset($user->struct) ){
            $filter=" nd.uid=".$user->uid.(($filter<>'')?' AND ':' ').$filter;
        }
    }
    $filter=(($filter<>'')?' WHERE ':' ').$filter;


     /* begin pager query */
     /*TODO: correct query to filter out other nodes*/
     //$base_sql="SELECT *, r.rcq_status as rcq_status, st.description as struct, nd.created as created,
    $base_sql="SELECT *, r.rcq_status as rcq_status, st.description as struct, if(a.title is null,'??',a.title) as created,
     			  sta.comment_count as comments, (r.value * IF(r.value_vat=0,"._WITH_IVA.",1)) as value
     				FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n ON r.nid=n.nid AND r.vid=n.vid
                    INNER JOIN {node} nd ON r.nid=nd.nid AND r.vid=nd.vid
                    LEFT JOIN  {node_comment_statistics} sta ON sta.nid=nd.nid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct 
                    LEFT JOIN (SELECT a.nid as nid2, n.title as title
                    			FROM "._BONDED_REQUESTS." a INNER JOIN {node} n ON n.nid=a.bnid  WHERE n.type='budget') a
                    			ON a.nid2=r.nid ". $filter . tablesort_sql($header);
/*	$count_sql="SELECT COUNT(*)  FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n on r.nid=n.nid and r.vid=n.vid
                    INNER JOIN {node} nd ON r.nid=nd.nid AND r.vid=nd.vid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    INNER JOIN ".query_status()." s ON s.nid=nd.nid ". $filter ; */
// print($base_sql);

/*    if (defined('_PAGER_DEFAULT_VALUE'))
    	$result=pager_query($base_sql,variable_get(_PAGER_DEFAULT_LABEL,_PAGER_DEFAULT_VALUE),0,$count_sql);
    else */
    $result=db_query($base_sql);

    $form['refs']=array('#tree'=> TRUE);
    $count=0;
    $value=0;
    while ($links = db_fetch_object($result)) {
        $count++;
        $value+=(is_numeric($links->value)?$links->value:0);
        $form['refs'][$links->nid] = array(
            '#type' => 'checkbox',
            '#title' => info_popup($links->nid),
        );
        $edit=($links->rcq_status==_OPEN && (user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)))?'/edit':'';
        $form['title'][$links->nid]= array('#value' => nl($links->nid)); //l($links->title.($links->comments>0?'    ('.$links->comments.')':''), 'node/'.$links->nid.$edit).theme('mark', node_mark($links->nid, $links->changed)));
        $form['qta'][$links->nid]= array('#value' => isset($links->qta)?$links->qta:0);
        $form['struct'][$links->nid]= array('#value' => '<small>'.$links->struct.'</small>');
        $form['cud'][$links->nid]= array('#value' => $links->cud);
        $form['value'][$links->nid]= array('#value' => currencyFormat($links->value));
//        $form['changed'][$links->nid]= array('#value' => '<small>'.format_date($links->created,'small').'</small>');
        $form['changed'][$links->nid]= array('#value' => '<small>'.$links->created.'</small>');
        $form['status'][$links->nid]= array('#value' => l(request_status_message($links->rcq_status),'node/statuslog/'.$links->nid));
        $form['status_class'][$links->nid]= array('#value' => $links->rcq_status);
    }
    $form['conteggio']=array('#value' => '<h1><strong>'.$count.'</strong></h1>');
    $form['totale']=array('#value' => '<big><b>'.currencyFormat($value).'</b></big><small> iva inclusa</small>');
    //     $form['totale_noiva']=array('#value' => '<small>'.currencyFormat($value).' +iva<small>');

    $w=0;
/*	$form['links']=array(
		'#type'=>'fieldset',
		'#weight'=>++$w,
		'#title'=>t(''),
		'#collapsible'=> FALSE,
	);
	$form['links']['add']=array(
		'#value' => l('Aggiungi richiesta','node/add/request'),
		'#postfix' => '<br>',
	);
*/


    //nserisce i pulsanti di stato
    $w=insert_status_buttons($form,$w);
    $form['CORRECTOR']=array(
        '#type'=>'fieldset',
        '#title' => 'tool di correzione e cancellazione completa',
        '#weight'=>++$w,
        '#collapsible'=> TRUE,
        '#collapsed'=> TRUE,
        '#access'=>  user_access(_FULL_MANGE_RCQ)
    );
    $status=request_status_message();
    $status[0]='(seleziona)';
    asort($status);
    $form['CORRECTOR']['list_status']=array(
        '#type'=> 'select',
        '#options'=>$status,
        '#default_option'=>0,
        '#weight'=>1,
    );
    $form['CORRECTOR']['chg_status']=array(
        '#type'=> 'button',
        '#weight'=>2,
        '#value'=>'Forza lo stato',
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('chg_status_sbmt'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi cambiare DEFINITIVAMENTE lo stato?");'),
    );
    $form['tools']=array(
        '#type'=>'fieldset',
        '#weight'=>++$w,
        '#title'=>'tool di estrazione',
        '#description'=> "I pulsanti permettono di produrre un file Excel contenente la tabella sopra. ATTENZIONE! Se i filtri sono impostati i file conterranno solo i dati filtrati ",
        '#collapsible'=> TRUE,
        '#collapsed'=>TRUE,
        '#access'=>  user_access(_FULL_MANGE_RCQ)||user_access(_MANGE_RCQ),
    );

/*     $form['tools']['estrazione']= array(
	    '#type'=> 'button',
		'#executes_submit_callback' => TRUE,
		'#submit'=>array('build_export_all_file'),
		'#value'=> t('Esporta'),
	);
*/
    $form['tools']['syntesis']= array(
        '#type'=> 'button',
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('build_export_syntesi_file'),
        '#value'=> t('Estrazione sintesi'),
    );


    $form['tools']['paged_syntesis']= array(
        '#type'=> 'button',
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('build_export_paged_syntesi_file'),
        '#value'=> t('Estrazione sintesi per struttura'),
        '#access'=>  user_access(_FULL_MANGE_RCQ)||user_access(_MANGE_RCQ),
    );

    $form['tools']['excel']= array(
        '#type'=> 'button',
        '#executes_submit_callback' => TRUE,
        '#description'=>'esporta un report gi&agrave;formattato per excel',
        '#submit'=>array('export_excel_syntesi_file'),
        '#value'=> t('Estrazione formattata Excel'),

    );

/*	$form['tools']['approved_syntesis']= array(
				'#type'=> 'button',
				'#executes_submit_callback' => TRUE,
				'#submit'=>array('build_export_approved_syntesi_file'),
				'#value'=> t('Estrazione approvate'),
				'#access'=>  user_access(_FULL_MANGE_RCQ)||user_access(_MANGE_RCQ),
	); */
//    $form['#attributes']=array('dialogModal'=>'grat/main','event'=>'mouseover');
//    $form['structII']=array(
//        '#type'=>'fieldset',
//        '#theme'=>'tree_view_check',
//        '#prefix'=>'<div id="treeview">',
//        '#suffix'=>'</div>',
//        '#parents'=>array('structII')
//        
//    );

  
    
    return $form;
}

function theme_request_listcheck($form) {

/*	$form['pager_settings']=array(
		'#title'=>'impostazioni paginazione',
		'#description'=>'Imposta le modalit? di visualizzazione della griglia',
		'#type'=>'fieldset',
		'#prefix'=>'<table><tr>',
		'#weight'=>0,
	);
	$form['pager_settings']['page_rows']=array(
			'#title'=>'Righe per pagina',
			'#default_value'=>variable_get(_PAGER_DEFAULT_LABEL,_PAGER_DEFAULT_VALUE),
			'#type'=>'textfield',
			'#prefix'=>'<td>',
			'#suffix'=>'<\td><\tr><\table>',
			'#weight'=>1,
	);
	*/

    if (isset($form['title']) && is_array($form['title'])) {
        foreach (element_children($form['title']) as $key) {
            $class='status_'.$form['status_class'][$key]['#value'];
            $rows[]= array(
                'data'=>array(
                    array('data'=>drupal_render($form['refs'][$key]),'class'=>$class),
                    drupal_render($form['title'][$key]),
                    drupal_render($form['qta'][$key]),
                    drupal_render($form['struct'][$key]),
                    drupal_render($form['cud'][$key]),
                    drupal_render($form['value'][$key]),
                    drupal_render($form['changed'][$key]),
                    array('data'=>drupal_render($form['status'][$key]),'class'=>$class)
                ),
                'class'=>$class
            );
            unset($form['status_class'][$key]);
        }
        $rows[]=array(array('data' => '<h1>Totale richieste:</h1>', 'colspan' => '2'),
            array('data' => drupal_render($form['conteggio']), 'colspan' => '1'),'',
            array('data' => drupal_render($form['totale']), 'colspan' => '4'),
        );

    }
    else {
        $rows[] = array(array('data' => t('No data available.'), 'colspan' => '7'));
    }
    $header=array(
        theme_table_select_header_cell(),//'data'=> t('ID'),'field'=>'r.nid'),
        array('data'=> t('Titolo    <i>(note)</i>'),'field'=>'nd.title'),
        array('data'=> t('quantit&agrave;'),'field'=>'qta'),
        array('data'=> t('struttura'),'field'=>'struct'),
        array('data'=> t('CND'),'field'=>'cud'),
        array('data'=> t('Valore &euro;'),'field'=>'value'),
//        array('data'=> t('Inserito'),'field'=>'nd.created'),
        array('data'=> t('Piano'),'field'=>'a.title'),
        array('data'=> t('Stato'),'field'=>'r.rcq_status'),
    );
    $output='';//drupal_get_form('requests_filter');
    //	if (defined('_PAGER_DEFAULT_VALUE')) $output .=theme('pager', NULL, variable_get(_PAGER_DEFAULT_LABEL,_PAGER_DEFAULT_VALUE), 0);
    $output .= theme('table',$header,$rows);
    //	if (defined('_PAGER_DEFAULT_VALUE')) $output .=theme('pager', NULL, variable_get(_PAGER_DEFAULT_LABEL,_PAGER_DEFAULT_VALUE), 0);
    $output .= drupal_render($form);
    return $output;
}


function request_listcheck_delete($form, &$form_state) {
    foreach ($form_state['values']['refs'] as $nid => $set) {
        if ($set) { request_delete(node_load($nid)); drupal_set_message("Cancellato $nid");}
    }
}

function chg_status_sbmt($form, &$form_state) {
    foreach ($form_state['values']['refs'] as $nid => $set) {
        if ($set) if ($form_state['values']['list_status'] <> 0) {
            update_rcq_status($nid, $form_state['values']['list_status'], 
                    'Forzato lo stato di avanzamanto a '.
                     request_status_message($form_state['values']['list_status']).'('.
                     $form_state['values']['list_status'].')');
             
             //set_status($nid,$form_state['values']['list_status']);
        }
    }
}
function enable_button($current_status,$new_status){
    if ($current_status===NULL) return TRUE;
    $res=request_status_check($current_status,$new_status)===TRUE;
    return $res;
}
function insert_status_buttons(&$form,$w,$node_status=NULL){
     $form['completed']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('In valutazione'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_EVALUTATE)).'>?");'), 
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
                    && enable_button($node_status,_EVALUTATE)
    );
    $form['approve']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Approva'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_APPROVED)).'>?");'), 
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_APPROVED)
    );
    $form['reject']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Rifiuta'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_REFUSED)).'>?");'), 
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_REFUSED)

    );
    $form['aquired']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Acquistato'),
        '#suffix'=>'<br>',
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_CLOSED)).'>?");'), 
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_CLOSED)
    );
    $form['move']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Recupera fondi'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_MOVED)).'>?");'), 
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_MOVED)
    );

/**/
    $form['review']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=> array('request_listcheck_sbmt'),
        '#value'=> t('Rivaluta'),
        '#description'=>'Rirpopone le richieste cancellate',
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_RIVALUTATE)).'>?");'),
        '#access'=> (user_access(_MODIFY_RCQ)||user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_RIVALUTATE)
    );
    $form['tec_ok']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=> array('request_listcheck_sbmt'),
        '#value'=> t('Doc. Tecnica OK'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_TEC_DOC)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ)|| user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_TEC_DOC)
    );
    $form['published']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=> array('request_listcheck_sbmt'),
        '#value'=> t('Pubblicata'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_PUBLISHED)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ)|| user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_PUBLISHED)
    );
    $form['tested']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Collaudata'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_TESTED)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_TESTED)
    );
    $form['not_completed']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Proposta'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_OPEN)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ))
        && enable_button($node_status,_OPEN)

    );
    $form['delete']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Cancella'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi cancellare  la/le richiesta/e selezionate?");'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_DELETED)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ) || user_access(_MODIFY_RCQ))
        && enable_button($node_status,_DELETED)
    );
    $form['proposed']=array(
        '#type'=> 'button',
        '#weight'=>++$w,
        '#executes_submit_callback' => TRUE,
        '#submit'=>array('request_listcheck_sbmt'),
        '#value'=> t('Riproponi'),
        '#attributes'=>array('onclick'=>'return confirm("Vuoi impostare lo stato in <'.strtoupper(request_status_message(_OPEN)).'>?");'),
        '#access'=> (user_access(_MANGE_RCQ)|| user_access(_FULL_MANGE_RCQ) || user_access(_INSERT_RCQ))
        && enable_button($node_status,_OPEN)
    );
    return $w;
}
function request_listcheck_sbmt($form, &$form_state) {
    $msg='';
    // controlla se la funzione è stata chiamata da un singolo nodo piuttosto che dalla lista
    if (!isset($form_state['values']['refs'])&& isset($form_state['values']['nid'])) {
        $nid=($form_state['values']['nid']);
        $form_state['values']['refs'][$nid]=$nid;
    }
    $op=$form_state['values']['op'];
    if (!empty($op))
        foreach ($form_state['values']['refs'] as $nid => $set) {
            if ($set<>0) {
                $node=node_load($nid);
                $status=$node->rcq_status;
                //$status=get_status($nid);
                switch ($op) {
                    case 'In valutazione':
                        $new_status=_EVALUTATE;
                        break;
                    case 'Proposta':
                        $new_status=_OPEN;
                        break;
                    case 'Approva':
                        $new_status=_APPROVED;
                        break;
                    case 'Rifiuta':
                        $new_status=_REFUSED;
                        break;
                    case 'Rivaluta':
                        $new_status=_OPEN;
                        break;
                    case 'Doc. Tecnica OK':
                        $new_status=_TEC_DOC;
                        break;
                    case 'Recupera fondi':
                        $new_status=_MOVED;
                        break;
                    case 'Cancella':
                        $new_status=_DELETED;
                        break;
                    case 'Acquistato':
                        $new_status=_CLOSED;
                        break;
                    case 'Collaudata':
                        $new_status=_TESTED;
                        break;
                    case 'Pubblicata':
                        $new_status=_PUBLISHED;
                        break;
                    case 'Riproponi':
                        $new_status=_OPEN;
                        break;
                }
                if (($msg=request_status_check($status,$new_status,$nid))===TRUE){
                    update_rcq_status($nid, $new_status, 'Stato di avanzamanto a '.request_status_message($new_status).' ('.
                            $new_status.') da '.request_status_message($status).' ('.
                            $status.')');
                    
                    //set_status($nid,$new_status);
                }
                    
                else
                    drupal_set_message($msg,'warning',TRUE);

            }
        }
  //  if ($msg<>'') drupal_set_message("$msg",'warning',TRUE);
}


/*data  extracting functions */
//construct CSV file
function build_export_all_file() {
    global $user;

    drupal_set_header('Content-Type: text/x-comma-seperated-values');
    drupal_set_header('Content-Disposition: attachment; filename="result-'.format_date(time(),'small').'.csv"');
    $head=TRUE;
    $output='';
/*    $result=db_query("SELECT *, r.rcq_status as rcq_status FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n on r.nid=n.nid and r.vid=n.vid
                    INNER JOIN {node} nd on r.nid=nd.nid and r.vid=nd.vid
                    INNER JOIN ".query_status()." s ON s.nid=n.nid ");
*/
	$ff=request_get_filter();
    $filter=$ff['SQL'];
    if (strpos($filter,'r.rcq_status')=== false) // filtro di default
        $filter.=($filter<>''?' AND':'').' '._PENDING_REQUESTS;
	$result=db_query("SELECT *, r.rcq_status as rcq_status, st.description as struct, n.title as title,
                          if(a.title is null,'??',a.title) as created,
     			  sta.comment_count as comments, (r.value * IF(r.value_vat=0,"._WITH_IVA.",1)) as value
     				FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n ON r.nid=n.nid AND r.vid=n.vid
                    INNER JOIN {node} nd ON r.nid=nd.nid AND r.vid=nd.vid
                    LEFT JOIN  {node_comment_statistics} sta ON sta.nid=nd.nid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    LEFT JOIN (SELECT a.nid as nid2, n.title as title
                    			FROM "._BONDED_REQUESTS." a INNER JOIN {node} n ON n.nid=a.bnid  WHERE n.type='budget') a
                    			ON a.nid2=r.nid ". $filter );

    while ($links = db_fetch_object($result)) {
        $row='';
        $header='';
        unserialize_data($links,$links->infos);
        unset($links->infos);
        foreach ($links->main as $key => $val) {
            if($head) $header.='"'.$key.'";';
            $row.=(is_numeric($val)?$val:'"'.$val.'"').';';
        }
        //output rows
        print (($head)?$header."\n ":'').$row."\n ";
        $head=FALSE;
    }
    exit();
}

/*****************************************
	sintesi extract funtion
******************************************/

function build_export_syntesi_file() {

    $filename='sintesi-'.format_date(time(),'small').'.csv';
    $filename=str_replace(':',"-", $filename);//clean filename for IE
    $filename=str_replace(' ',"_", $filename);
    $filename=str_replace('/',"_", $filename);

    drupal_set_header('Content-Type: text/x-comma-seperated-values');
    drupal_set_header('Content-Disposition: attachment; filename="'.$filename.'"');
    print build_export_syntesi();
    exit();
}

function build_export_paged_syntesi_file() {
    $filename='sintesi-paginata-'.format_date(time(),'small').'.csv';
    $filename=str_replace(':',"-", $filename);//clean filename for IE
    $filename=str_replace(' ',"_", $filename);
    $filename=str_replace('/',"_", $filename);

    drupal_set_header('Content-Type: text/x-comma-seperated-values');
    drupal_set_header('Content-Disposition: attachment; filename="'.$filename.'"');
    print build_export_syntesi(TRUE);
    exit();
}

function build_export_approved_syntesi_file() {
    $filename='sintesi-paginata-'.format_date(time(),'small').'.csv';
    $filename=str_replace(':',"-", $filename);//clean filename for IE
    $filename=str_replace(' ',"_", $filename);
    $filename=str_replace('/',"_", $filename);

    drupal_set_header('Content-Type: text/x-comma-seperated-values');
    drupal_set_header('Content-Disposition: attachment; filename="'.$filename.'"');
    print build_export_syntesi(FALSE);//,'r.rcq_status='._APPROVED);
    exit();
}

function export_excel_syntesi_file() {
    global $user;
    $filename='sintesi-paginata-'.format_date(time(),'small').'.xml';
    $filename=str_replace(':',"-", $filename);//clean filename for IE
    $filename=str_replace(' ',"_", $filename);
    $filename=str_replace('/',"_", $filename);

    drupal_set_header('Content-Type: application/vnd.ms-excel');
    drupal_set_header('Content-Disposition: attachment; filename="'.$filename.'"');
    $ff=request_get_filter();
    $filter=$ff['SQL'];
    if (strpos($filter,'r.rcq_status')=== false) // filtro di default
        $filter.=($filter<>''?' AND':'').' '._PENDING_REQUESTS;

 /*  $result=db_query("SELECT *, r.rcq_status as rcq_status, st.description as ward, r.nid as nid, YEAR(FROM_UNIXTIME(nd.created)) as year
					FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n on r.nid=n.nid and r.vid=n.vid
                    INNER JOIN {node} nd on r.nid=nd.nid and r.vid=nd.vid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    INNER JOIN ".query_status()." s ON s.nid=n.nid WHERE ".$filter."
                    ORDER BY st.description ");
*/
	$result=db_query("SELECT *, r.rcq_status as rcq_status, st.description as struct, n.title as title,
                          if(a.title is null,'??',a.title) as year,
     			  sta.comment_count as comments, (r.value * IF(r.value_vat=0,"._WITH_IVA.",1)) as value
     				FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n ON r.nid=n.nid AND r.vid=n.vid
                    INNER JOIN {node} nd ON r.nid=nd.nid AND r.vid=nd.vid
                    LEFT JOIN  {node_comment_statistics} sta ON sta.nid=nd.nid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    INNER JOIN "._STRUCT_TABLE." dpt ON (st.parent=dpt.id OR (st.id=dpt.id AND st.parent=0))
                    LEFT JOIN (SELECT a.nid as nid2, n.title as title
                    			FROM "._BONDED_REQUESTS." a INNER JOIN {node} n ON n.nid=a.nid  WHERE n.type='budget') a
                    			ON a.nid2=r.nid
                    WHERE ". $filter." ORDER BY dpt.description, st.description ");


    include 'excel_template.inc.php';               // Include the file
    $ff=request_get_filter();
    $filter=$ff['SQL'];
    $header=ereg_replace("<[^>]*>","",$ff['description']);

    excel_xml($filename,$result,$header);
    exit();
}

function build_export_syntesi($paged=FALSE, $status_filter=_PENDING_REQUESTS) {
    global $user;
    $rows='';
    $last_struct='';
/**/	$ff=request_get_filter();
    $filter=$ff['SQL'];
    if (strpos($filter,'r.rcq_status')=== false) // filtro di default
        $filter.=($filter<>''?' AND':'').' '._PENDING_REQUESTS;

    $header='"'.ereg_replace("<[^>]>","",$ff['description']).'";'."\n";
    $header.='"ID";"ID A.R.S.";"ID C.S.C.";"CAT";"PRI";'.(!$paged?'"SERVIZIO/UNITA\' OPERATIVA";':'').'"TITOLO ATTREZZATURA";"DESCRIZIONE ATTREZZATURA";'.
        '"NUM.";"INVENTARIO PER SOST.";"ANNO ACQ.";"VALORE CON IVA";"STATO";'.
        '"TIPO";"NOTE";'.
        '"COMMENTI";"LAVORI EDILIZI/IMPIANTI";"RESIDUI ANNI PREC.";"INFORMATICA/ECONOMALI";"PIANO STRAORDINARIO";"anno di riferimento";';
    $rows.= $header."\n ";
    //	$filter.=($filter<>''?' AND':'').' '.$status_filter;
/*    $result=db_query("SELECT *, r.rcq_status as rcq_status, st.description as ward, r.nid as nid, YEAR(FROM_UNIXTIME(nd.created)) as year,
						FLOOR(r.value * IF(r.value_vat=0,"._WITH_IVA.",1)) as value
					FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n on r.nid=n.nid and r.vid=n.vid
                    INNER JOIN {node} nd on r.nid=nd.nid and r.vid=nd.vid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    INNER JOIN ".query_status()." s ON s.nid=n.nid WHERE ".$filter."
                    ORDER BY st.description ");
*/
	$result=db_query("SELECT *, r.rcq_status as rcq_status, st.description as struct, n.title as title,
                          if(a.title is null,'??',a.title) as year,
     			  sta.comment_count as comments, (r.value * IF(r.value_vat=0,"._WITH_IVA.",1)) as value
     				FROM "._REQUESTS_TABLE." r
                    INNER JOIN {node_revisions} n ON r.nid=n.nid AND r.vid=n.vid
                    INNER JOIN {node} nd ON r.nid=nd.nid AND r.vid=nd.vid
                    LEFT JOIN  {node_comment_statistics} sta ON sta.nid=nd.nid
                    INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
                    INNER JOIN "._STRUCT_TABLE." dpt ON (st.parent=dpt.id OR (st.id=dpt.id AND st.parent=0))
                    LEFT JOIN (SELECT a.nid as nid2, n.title as title
                    			FROM "._BONDED_REQUESTS." a INNER JOIN {node} n ON n.nid=a.bnid  WHERE n.type='budget') a
                    			ON a.nid2=r.nid
                    WHERE ". $filter." ORDER BY st.description ");


    while ($links = db_fetch_object($result)) {
        if ($paged && $last_struct<>$links->struct) {
            $rows.="\n ".'"'.$links->struct.'"'."\n ";
            $rows.= $header."\n ";
            $last_struct=$links->struct;
        }
        $comments='';
/*    	if ($res=db_query("SELECT * FROM {} WHERE nid=%d",$links->nid)) {

			while ($data=db_fetch_object($res))
				$comments.=$data->comment;
    	}*/
        $row='';
        unserialize_data($links,$links->infos);
        unset($links->infos);

        //'"ID";
        $row.=''.$links->nid.';';
        $row.=''.$links->idARS.';';
        $row.=''.$links->idCSC.';';

        //'"CAT";
        $row.='"'.$links->cud.'";';

        //"PRI";
        $row.='"'.get_priority($links->priority).'";';

        //"SERVIZIO/UNITA\' OPERATIVA";
        if (!$paged) $row.='"'.$links->struct.'";';

        //"DESCRIZIONE ATTREZZATURA";
        $info='';
        $info=$links->title;
//        if ($links->type_acq==0) //apparecchiatura nuova
//            $info.= " -- ".$links->main['descrizione_apparecchiatura'];     
        $info=str_replace('"',"'", $info); //pulizia caratteri ambigui
        $info=str_replace(';',"-", $info);
        $row.='"'. strtoupper($info).'";';
        $row.= '"'.$links->main['descrizione_apparecchiatura'].'";';   
        //"NUM.";
        $row.=(is_numeric($links->qta)?$links->qta:'"'.$links->qta.'"').';';

        //"INVENTARIO PER SOST.";
        $row.='"'.$links->main['inventario'].'";';
        //"ANNO ACQ.";
        $row.='"'.$links->main['anno_acq'].'";';

        //"Valore IVATO";
        $row.=(is_numeric($links->value)?number_format((float)$links->value,0,',','.'):'"'.$links->value.'"').';';
        //"STATO";
        $row.='"'.request_status_message($links->rcq_status).'";';

        //"TIPO";
        switch ($links->type_acq) {
            case 0: $row.='"Innovazioni";';break;
            case 1: $row.='"Sostituzioni";';break;
            case 2: $row.='"Sicurezza";';break;
        }

        //"NOTE";
        $info=$links->note;
        $info=str_replace('"',"'", $info); //pulizia caratteri ambigui
        $info=str_replace(';',"-", $info);
        $row.='"'.$info.'";';
        //"COMMENTI";
        $qy="SELECT comment FROM {comments} WHERE nid=%n AND status=%n AND uid>1 ORDER BY timestamp DESC";
        $res=db_query($qy,$links->nid,COMMENT_PUBLISHED);
        $info='';
        while($rw=db_fetch_object($res)) {
            $info.=$rw->comment . " | ";
        }
        $row.='"'.$info.'";';
        //"LAVORI EDILIZI/IMPIANTI";
        $val=get_impianti($links->main['impianti']).($links->main['edili']?'Lavori edili':'');
        $row.='"'.$val.'";';
        //"RESIDUI ANNI PREC.";
        $row.='0;';
        //"INFORMATICA/ECONOMALI";
        $info=$links->main['informatica'].'/'.$links->main['mat_consumo'];
        $info=str_replace('"',"'", $info); //pulizia caratteri ambigui
        $info=str_replace(';',"-", $info);
        $row.='"'.$info.'";';
        //"PIANO STRAORDINARIO";
        $row.=';';
        //"Anno di riferimento";
        $row.=$links->year.';';

        //output rows
        $rows.= $row."\n ";

    }
    return $rows."\n ";
}



//TODO spostata in file request_filter.php.inc
/*******************************************************************************
 * filter functions 
 *******************************************************************************/
/********************************************************************************/
//form del filtro
function requests_filter(&$form_state) {
    global $user;
    drupal_add_css('request/request.css','module');
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
    $form['flt']['fields']['structII']['tree']=build_structure_form($struct,isset($_POST['struct_selection'])?$_POST['struct_selection']:NULL);
   
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

    );
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



function add_unselection(array $data) {
    $data[-1]='(seleziona)';
    return $data;
}

function requests_filter_submit($form, &$form_state) {
    $op=$form_state['values']['op']; 
    if (!isset($form_state['values']['struct_selection']) || $form_state['values']['struct_selection']!=$_POST['struct_selection']) 
        $form_state['values']['struct_selection']=$_POST['struct_selection'];
    $values=$form_state['values'];
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

function request_drop_filter() {
    array_pop($_SESSION[_SESSION_FILTER]);
}

function request_reset_filter() {
    unset($_SESSION[_SESSION_FILTER]);
}

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

/**/