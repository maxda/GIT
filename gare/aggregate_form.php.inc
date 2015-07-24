<?php
include_once("gare_aux_function.php.inc");
/*  ---------------------- Aggregate form ----------------------------------------- */


function aggregate_form($form_state) {
//costruisce una lista di voci raggruppate per CND
    global $user;
    $form['selector']=array(
        '#type'=>'fieldset',
        '#description'=>'Aggregazione delle richieste basata sulla classificazione CND del ministero della salute. ',
        '#collapsible'=>FALSE,
    );
    $form['selector']['level']=array(
        '#type'=>'radios',
        '#prefix'=> '<table><tr><td>',
        '#suffix'=> '</td>',
        '#title'=>'Tipologia (livello)',
        '#description'=>'Imposta il livello di aggregazione secondo la classificazione CND',
        '#options'=>array(1=>'Categoria (1&#176; livello)',
        2=>'Gruppo (2&#176; livello)',
        3=>'3&#176; livello',
        4=>'4&#176; livello',
        5=>'5&#176; livello',
        6=>'6&#176; livello',
        7=>'7&#176; livello',),
        '#default_value'=> isset($_GET['level'])?$_GET['level']:5,
        '#weight' => 0,
    );
    $priority=get_priority();
    for($i=4; $i>0 ;$i--)$priority[$i]=$priority[$i-1];
    unset($priority[0]);
    $form['selector']['priority']=array(
        '#type'=>'checkboxes',
        '#prefix'=> '<td>',
        '#title'=>'Priorit&agrave;',
        '#options'=>$priority,
        '#default_value'=> isset($_GET['priority'])?$_GET['priority']:array(1=>0,2=>0,3=>0,4=>0),
        '#weight' => 1,
    );
    $form['selector']['classification']=array(
        '#type'=>'select',
        '#suffix'=> '</td>',
        '#title'=>'Classificazione',
        '#options'=>get_classification(),
        '#default_value'=> isset($_GET['classification'])?$_GET['classification']:1,
        '#weight' => 2,
    );

    $form['selector']['status']=array(
        '#type'=>'checkboxes',
        '#suffix'=> '</td></tr></table>',
        '#prefix'=> '<td>',
        '#title'=>'Stato',
        '#description'=>'Imposta il lo stato delle richieste da selezionare',
        '#options'=>request_status_message(),
        '#default_value'=> isset($_GET['status'])?$_GET['status']:array(_APPROVED=>_APPROVED),
        '#weight' => 3,
    );

    $form['selector']['aggregate']=array(
        '#type'=>'submit',
        '#value'=>'Aggrega',
        '#description'=>'Seleziona la categoria da aggregare CND',
        '#weight' => 4,
    );

    //aggiunge il pulsante di esecuzione
    $form['actions_top']=array(
        '#type'=>'fieldset',
        '#description'=>'Crea nuove gare dalle voci selezionate. Le gare saranno poste nello stato di "'.gare_status_message(_PROPOSED).'"
		Le voci selezionate verranno rimosse dalla lista e compariranno nelle gare attive.<br>',
        '#collapsible'=>FALSE,
        '#weight' => 5,
        '#prefix'=>'<div class="container-inline">',
        '#suffix'=>'</div>',
    );

    $form['actions_top']['execute']=array(
        '#type'=>'submit',
        '#value'=>'Pianifica Gare',
        '#weight' => 6,
    );
    $form['actions_top']['Assegna']=array(
        '#type'=>'submit',
        '#value'=>'Assegna a gara',
        '#weight' => 7,
    );
    $form['actions_top']['garaid']=array(
        '#type'=>'textfield',
        '#title'=> 'Id gara',
        '#autocomplete_path'=>'gare/idautocomplete',
        '#default_value'=>'',
        '#weight' => 8,
        '#size'=> 80,
    );
    $form['actions_bottom']=array(
        '#type'=>'fieldset',
        '#description'=>'Crea nuove gare dalle voci selezionate. Le gare saranno poste nello stato di "'.gare_status_message(_PROPOSED).'"
		Le voci selezionate verranno rimosse dalla lista ecompariranno nelle relative gare.<br>',
        '#collapsible'=>FALSE,
        '#prefix'=>'<div class="container-inline">',
        '#suffix'=>'</div>',

    );
    $form['actions_bottom']['execute']=array(
        '#type'=>'submit',
        '#value'=>'Pianifica Gare',
        '#weight' => 6,
    );

    foreach ($form['selector'] as $key => $val) //reimposta i valori di default del filtro se � stata rifatta l'aggragazione col pulsante
        if (isset($form['selector'][$key]['#default_value']) &&
            isset($form_state['values'][$key]))
            $form['selector'][$key]['#default_value']=$form_state['values'][$key];

    $elaboration=aggregate_request($form['selector']['level']['#default_value'],$form['selector']['status']['#default_value'],
        $form['selector']['priority']['#default_value'],$form['selector']['classification']['#default_value']);
    foreach($elaboration as $key =>$val ) $form[$key]=$val;
    return $form;
}





function aggregate_form_submit($form, &$form_state) {
    $values=$form_state['values'];
    $op=$values['op'];
    global $user;
    switch ($op) {
        case 'Pianifica Gare':
        //print_r($form_state);
        case 'Assegna a gara':
            if (isset($values['selection'])) {
                foreach($values['selection'] as $key => $val) {//inserisce in nodi per le aggregazioni selezionate
                //$key contiene il gruppo CND val contiene l'array delle richieste associate
                    if ($val<>0) {

                        if ($op=='Assegna a gara' && isset($values['garaid']) && is_numeric($values['garaid']) && $values['garaid']<>0) {
                            foreach($values[$key] as $k2 =>$v2) //
                                if ($v2) //recupera le foglie selezionate e le associa alla gara gi� creata
                                    if (!aggregate_node($values['garaid'],$k2))
                                        drupal_set_message('c\'&egrave; stato un errore nell\'inserimento della voce '.$k2,'warning');
                                    else
                                        drupal_set_message('Inserito '.$k2);
                        }
                        else {
                            if ($op=='Pianifica Gare') if (!aggregate_new($key,$values[$key]))
                                    drupal_set_message('c\'&egrave; stato un errore nell\'inserimento di gruppo'.$key,'warning');
                        }

                    }
                }
            }
        case 'Aggrega':
            $form_state['rebuild']=TRUE;
            break;
    }

}

/*
funzione di aggregazione: restituisce tabella di aggregazioni da inseriere in una form
*/

function aggregate_request($level,$status,$priority=NULL,$classification=NULL) {
    $elements=array();
    $richieste=0;
    $gruppi=0;
    $goon=TRUE;
    $p='';

    if ($priority!==NULL) if (is_array($priority)) {

            foreach ($priority as $key => $val) $p.=$val?($p<>''?', ':'').($key-1):'';
            $p=$p<>''?'('.$p.')':'';
        }

    $filter.=($p!='')?' r.priority IN'.$p:'';
    $filter.=($filter<>''?' AND ':'').($classification!==NULL?'r.classification='.$classification:'');
    $filter.=($filter<>''?' AND ':'')."r.nid NOT IN ".query_aggregation('a.nid2','gare')." "; // esclusione di quelle gi� inserite
    $filter=($filter<>''?' WHERE  ':'').$filter;

    if (is_array($status)) foreach($status as $key =>$val) $goon=$goon && ($val<>0);
    //	if (!$goon) return array('#value'=>'Nessuna elaborazione');


    $query='SELECT c.id AS idCND, r.value as value, r.qta as qta, r.cud as cud, r.struct as struct, n.nid as nid, n.title as title
			FROM '._REQUESTS_TABLE.' r
			LEFT JOIN '._CUD_TABLE.' c ON LEFT(r.cud,'.($level*2-1).')=c.id
			INNER JOIN {node} n ON n.nid=r.nid AND n.vid=r.vid
			INNER JOIN '.query_status($status).' st ON st.nid=r.nid '.$filter;
    $count_rcq='SELECT count(*) as sum FROM ('.$query.') q'; // conteggio del totale delle richieste

    $main_query="SELECT SUM(q.qta) AS elements, COUNT(*) AS count, SUM(q.value) AS value , idCND
	            FROM (".$query.") q GROUP BY idCND ORDER BY count desc, value desc ";
    $count_sql='SELECT count(*) as count FROM ('.$main_query.' ) q'; // conteggio dei raggruppamenti
    //	print_r($main_query);

    $qy=pager_query($main_query,40,0,$count_sql);
    $richieste=0;
    $idCND='';
    while ($fields=db_fetch_object($qy)) {
        if ($fields->idCND===NULL)$fields->idCND='Z99';
        $idCND=$fields->idCND;
        $elements['selection'][$idCND]=array(
            '#type'=>'checkbox',
            '#default_value'=>0,
            '#parents'=>array('selection',$idCND),
        );
        $elements['description'][$idCND]=array(
            '#type'=>'fieldset',
            '#collapsible'=>TRUE,
            '#collapsed'=>TRUE,
            '#description'=>check_plain(get_cud_description($idCND)),
            '#title'=>strtolower(truncate_string(get_cud_description($idCND))).($idCND!==NULL?' ('.$idCND.')':''),
        );
        // conteggi
        $elements['count'][$idCND]=array('#value'=>$fields->count,);
        $elements['elements'][$idCND]=array('#value'=>$fields->elements,);
        $elements['value'][$idCND]=array('#value'=>currencyFormat($fields->value),);
        $elements['description'][$idCND]['group']=get_group($idCND, $query);
        $richieste+=$fields->count;
        $gruppi++;
    }
    $elements['count_requst']=array('#type'=>'hidden','#value'=>$richieste.'/'.db_result(db_query($count_rcq)));
    $elements['groups']=array('#type'=>'hidden','#value'=>$gruppi.'/'.db_result(db_query($count_sql)));

    return $elements;

}

function theme_aggregate_form($form) {

    $output=drupal_render($form['selector']);
    $output.=isset($form['actions_top'])?drupal_render($form['actions_top']):'';
    $output .=theme('pager', NULL, 20, 0);

/*	$header=array(
			array('data'=>'Descrizione','field'=>'descCND'),
			array('data'=>'Richieste','field'=>'sum'),
			array('data'=>'Valore','field'=>'count'));
*/
    $header=array('',
        'Descrizione <small>(raggruppamenti: '.$form['groups']['#value'].')</small>','Elemnti',
        'Richieste <small>('.$form['count_requst']['#value'].')</small>'
        ,'Valore',);
    if (isset($form['description'])) {
        foreach(element_children($form['description']) as $key) {
            $rows[]=array(
                drupal_render($form['selection'][$key]),
                drupal_render($form['description'][$key]),
                drupal_render($form['elements'][$key]),
                drupal_render($form['count'][$key]),
                drupal_render($form['value'][$key]),
            );
        }
        $output.=theme('table',$header, $rows);
        $output .=theme('pager', NULL, 20, 0);

    }
    $output.=isset($form['actions_bottom'])?drupal_render($form['actions_bottom']):'';
    $output.=drupal_render($form);
    return $output;
}
//recupera tutti i nodi raggruppati
function get_group($group, $query) {
    $qy=db_query("SELECT * FROM (".$query .") q WHERE ".($group=='Z99'?'idCND IS NULL': "idCND= '".$group."'"));
    while ($fields=db_fetch_object($qy)) {
        $elements[$fields->nid][]=array(
            '#type'=>'checkbox',
            '#title'=>l($fields->title,'node/'.$fields->nid),
            '#description'=>get_cud_description($fields->cud),
            '#default_value'=>1,
            '#parents' => array($group,$fields->nid), // per mantenre l'array dei checkbos in $form_state[$group]
        );
        $elements[$fields->nid][]=array('#value'=>'Quantit&agrave;:<b>'.$fields->qta.'<b>','#prefix'=>'<small><small><table><tr><td>','#suffix'=>'</td>');
        $elements[$fields->nid][]=array('#value'=>'Valore:<b>'.currencyFormat($fields->value).'<b>','#prefix'=>'<td>','#suffix'=>'</td>',);
        $elements[$fields->nid][]=array('#value'=>'Struttura:<b>'.get_structure($fields->struct).'<b>','#prefix'=>'<td>','#suffix'=>'</td></tr></table></small></small>',);
    }

    return $elements;
}


//funzione per creare una gara con le  richieste
function aggregate_new($group_CND,$nid_list) {
    global $user;
    if(!is_array($nid_list)) return FALSE;
    //create node
    $nid=0;
    $time=time();
    $desc=get_cud_description($group_CND);
    db_query("INSERT INTO {node_revisions} (uid,title,body,teaser,log,timestamp)
			  VALUES (%d,'%s','%s','%s','Aggregazione automatica',%d)",$user->uid,$desc,$desc,$desc,time());
    $vid=db_last_insert_id('{node_revisions}','vid');

    db_query("INSERT INTO {node} (vid,type,language,title,uid,
		 created,changed,comment,promote)
		VALUES (%d,'gare','it','%s',%d ,
		%d,%d,2,1)",$vid,$desc,$user->uid,$time,$time);
    $nid=db_last_insert_id('{node}','nid');

    db_query("UPDATE {node_revisions} SET nid=".$nid." WHERE vid=".$vid);
    set_status($nid,_PROPOSED);
    $res=$nid;
    foreach($nid_list as $key => $val) {
        if ($val) $res =(aggregate_node($nid,$key)?$res:FALSE);
    }
    $value=db_fetch_object(db_query("SELECT SUM(r.value) as value, SUM(r.qta) as qta FROM "._REQUESTS_TABLE." r
			 INNER JOIN ".query_aggregation($nid)." ag ON ag.nid2=r.nid
			 INNER JOIN {node} n ON n.nid=r.nid AND n.vid=r.vid "));
    db_query("INSERT INTO "._GARE_TABLE." (nid,vid,CND,value,qta)
			  VALUES (%d,%d,'%s',%d,%d)",$nid,$vid,$group_CND,$value->value,$value->qta);
    return $res;
}

