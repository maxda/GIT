<?php
//include_once("gare_aux_function.php.inc");


function gare_list_filter(&$form_state,&$form){
	global $user;
	$w=0;
	$year[-1]='(seleziona)';
	$query="SELECT DISTINCT YEAR(FROM_UNIXTIME(created)) FROM {node}
			WHERE type='gare' ORDER BY YEAR(FROM_UNIXTIME(created)) DESC";
	$res=db_query($query);
	while($yy=db_result($res)) $year[]=$yy;

	$values=$form_state['storage']['values'];
	$form_state['rebuild']=TRUE;
	$form['filter']=array(
			'#type'=>'fieldset',
			'#title'=>'Filtro gare',
		//	'#description'=>'Filtro delle gare',
			'#collapsible'=>TRUE,
			'#collapsed'=>FALSE,
		);

	$form['filter']['status']=array(
		'#type'=>'checkboxes',
		'#suffix'=> '</td>',
		'#prefix'=> '<table><tr><td>',
		'#title'=>'Stato',
		'#description'=>'Seleziona le gare da mostrare',
		'#options'=>gare_status_message(),
		'#default_value'=> (isset($values['status'])?$values['status']:array(_ON_BUILD=>_ON_BUILD)),
		'#attributes'=>array('onclick'=>"this.form.submit();"),
		"#SQL_FIELD"=>'st.status',
		'#weight' => $w++,
	);

	$form['filter']['owner']=array(
			'#type'=>'radios',
//			'#suffix'=> '</td>',
			'#prefix'=> '<td>',
			'#title'=>'Gare in carico a',
			'#description'=>'permette di filtrare solo le gare a mio carico',
			'#options'=>array('Solo le mie','Tutte',),
			'#default_value'=>(isset($values['owner'])?$values['owner']:0),
			'#attributes'=>array('onclick'=>"this.form.submit();"),
			"#SQL_FIELD"=>array('st.uid='.$user->uid,''),
			'#weight' => $w++,
	);
	$form['filter']['year']=array(
			'#type'=>'select',
//			'#prefix'=> '<td>',
			'#suffix'=> '</td></tr></table>',
			'#title'=>'Anno',
			'#description'=>'anno di apertura gara',
			'#options'=>$year,
			'#default_value'=> (isset($values['year'])?$values['year']:-1),
			'#attributes'=>array('onchange'=>"this.form.submit();"),
			"#SQL_FIELD"=>'YEAR(FROM_UNIXTIME(n.created))',
			'#weight' => $w++,
	);
	$form['filter']['Aggiorna']=array(
			'#type'=>'submit',
			'#value'=>'Aggiorna',
			'#submit'=>array('gare_list_submit'),
			'#weight' => $w++,
	);
	return $form;
}

// funzion per la ricostruzione del filtro

function get_filter($form){
    $output='';
    if (!isset($form['filter'])) return $output;
       foreach ($form['filter'] as $element => $content){
        if (is_array($content)&&isset($content['#type'])){
            switch ($content['#type']){
                case'select':
                	 if($content['#default_value']<>-1) $output.= ($output<>''?' AND ':'').$content['#SQL_FIELD'].'='.$content['#options'][$content['#default_value']];
                	 break;
                case'checkboxes':
                    foreach($content['#default_value'] as $key=>$val) {
                        if ($val <>0) $res.=($res<>''?', ':'').$key; //compone la lista delle chiavi per la struttura IN ( )
                    }
                    if ($res<>'') $output.= ($output<>''?' AND ':'').$content['#SQL_FIELD'].' IN ('.$res.') ';
                break;
                case 'radios':
                    if (is_array($content['#SQL_FIELD'])) $output.= ($output<>''&&$content['#SQL_FIELD'][$content['#default_value']]<>''?' AND ':'').$content['#SQL_FIELD'][$content['#default_value']];
                    else $output.= ($output<>''?' AND ':'').$content['#SQL_FIELD'].'='.$content['#default_value'];
                break;
            }
        }
    }
    return $output;
}
//funzione per il recupero dei dati
function gare_list(&$form_state){
	$header=array( 'ID',array('data'=>'Gara',
                                  'field'=>'n.title'),
                                array('data'=>'creata',
                                      'field'=>'n.created'),
                      'Elementi','Quantit&agrave;','Valore', 'Stato'
	);

        gare_list_filter($form_state,$form);
        $filter=get_filter($form);
//print_r( $filter);
        $filter=($filter<>''? ' AND ':'').$filter;

	$query="SELECT *,st.status as status, st.uid, n.created as created FROM {node} n
				INNER JOIN ".query_status()." st ON st.nid=n.nid
				WHERE n.type='gare'".$filter." ".tablesort_sql($header);
//print $query;
	$query_count ="SELECT count(*) FROM {node} n
					INNER JOIN ".query_status()." st ON st.nid=n.nid
			WHERE n.type='gare' ".$filter;
	$form['id']=array('#tree'=> TRUE);
//	$qy=pager_query($query,100,0,$query_count);
	$qy=db_query($query);

	while ($fields=db_fetch_object($qy)){
			$g_val=get_gara_value($fields->nid);
			$g_count=get_gara_count($fields->nid);

			$form['id'][$fields->nid] = array(
				 '#type' => 'checkbox',
				 '#title' => info_popup($fields->nid),
			);
			$form['description'][$fields->nid]=array('#value'=>nl($fields->nid));
			$form['date'][$fields->nid]=array('#value'=>format_date($fields->created,'small'));
			$form['count'][$fields->nid]=array('#value'=>$g_count);
			$form['elements'][$fields->nid]=array('#value'=>get_gara_elements($fields->nid));
			$form['value'][$fields->nid]=array('#value'=>currencyFormat($g_val));
			$form['status'][$fields->nid]=array('#value'=>l(gare_status_message($fields->status),'node/statuslog/'.$fields->nid));
	}
	$w=4;
	$form['buttons']['on_build']=array(
		'#type'=>'submit',
		'#value'=>'Prendi in carico',
		'#submit'=>array('gare_list_submit'),
		'#description'=>'',
		'#attributes'=>array('onclick'=>'return confirm("vuoi prendere in carico le voci selezionate?");'),
		'#weight' => $w++,
	);
	$form['buttons']['release']=array(
		'#type'=>'submit',
		'#value'=>'Sospendi',
		'#submit'=>array('gare_list_submit'),
		'#description'=>'',
		'#attributes'=>array('onclick'=>'return confirm("vuoi sospendere le voci selezionate?");'),

		'#weight' => $w++,
	);
	$form['buttons']['in_progress']=array(
		'#type'=>'submit',
		'#value'=>'Gara in corso',
		'#submit'=>array('gare_list_submit'),
		'#description'=>'',
		'#attributes'=>array('onclick'=>'return confirm("vuoi mettere \"in corso\" le voci selezionate?");'),
		'#weight' => $w++,
	);
	$form['buttons']['terminate']=array(
		'#type'=>'submit',
		'#value'=>'Concludi',
		'#submit'=>array('gare_list_submit'),
		'#description'=>'',
		'#attributes'=>array('onclick'=>'return confirm("vuoi chiudere le voci selezionate?");'),
		'#weight' => $w++,
	);
	$form['tools']=array(
		'#type'=>'fieldset',
		'#title'=>'Tool di estrazione dati',
 		'#collapsible' => TRUE,
  		'#collapsed' => FALSE,
  		'#weight' => $w++,
  	);

	$form['tools']['esporta']=array(
		'#type'=>'button',
		'#executes_submit_callback' => TRUE,
		'#value'=>'Esporta',
		'#submit'=>array('build_export_gare_syntesi_file'),
		'#description'=>'Seleziona la categoria da aggregare CND',
		'#weight' => 4,
	);
	return $form;

}


function gare_list_submit($form, &$form_state){
        global $user;
	$values=$form_state['values'];
    $form_state['storage']['values']=$values; //ricaricamento dei valori impostati
	$op=$values['op'];
//	print_r ($form_state);
	if(isset($values['id']))foreach ($values['id'] as $nid =>$set) {
		$status=get_status($nid);
		if ($set<>0 && isset($form_state['clicked_button']['#value']))
		  switch ($form_state['clicked_button']['#value']) {
			case 'Prendi in carico':
				if ($status==_PROPOSED || $status==_RELEASED )	set_status($nid,_ON_BUILD);
				else $msg .= "La gara ".$nid." &egrave; gi&agrave; in carico a qualcuno o terminata<br>";
			break;
			case 'Sospendi':
				if ($status==_ON_BUILD || $status==_IN_PROGRESS ) {
                                    if (get_status_uid($nid)<>$user->uid) $msg .= "La gara  ".$nid." &egrave in carico ad un altro utente";
                                    else  set_status($nid,_RELEASED);
                                }
				else $msg .= "La gara  ".$nid." non &egrave; in carico o &egrave; terminata e non pu&ograve; essere rilasciata<br>";
			break;
			case 'Gara in corso' :
				if ($status==_ON_BUILD || $status==_RELEASED )	{
                                    if (get_status_uid($nid)<>$user->uid && $status==_ON_BUILD) $msg .= "La gara  ".$nid." &egrave in carico ad un altro utente";
                                    else  set_status($nid,_IN_PROGRESS);
                               }
				else $msg .= "La gara ".$nid." non &egrave; pronta o &egrave; terminata<br>";
			break;
			case 'Concludi' :
				if ($status==_IN_PROGRESS || $status==_RELEASED )	set_status($nid,_EXPIRED);
				else $msg .= "La gara ".$nid." non &egrave; in corso e non pu&ograve; essere conlusa<br>";
			break;
		}
	}
	if ($msg<>'') drupal_set_message("$msg",'warning',TRUE);
        return;
}

function theme_gare_list($form){
        $output.=drupal_render($form['filter']);
	foreach (element_children($form['description']) as $key){
		$rows[]=array(
					drupal_render($form['id'][$key]),
					drupal_render($form['description'][$key]),
					drupal_render($form['date'][$key]),
					drupal_render($form['count'][$key]),
					drupal_render($form['elements'][$key]),
					drupal_render($form['value'][$key]),
					drupal_render($form['status'][$key]),
			);
	}
	$header=array( theme_table_select_header_cell(),
					array('data'=>'Gara',
								 'field'=>'n.title'),
						   array('data'=>'creata',
						   	     'field'=>'n.created'),
						   'Elementi','Quantit&agrave;','Valore', 'Stato'
	);
//	$output.=theme('pager', NULL, 100, 0);
	$output.=theme('table',$header,$rows);
//	$output.=theme('pager', NULL, 100, 0);
	$output.=drupal_render($form);
	return $output;
}






//recupera tutti i nodi raggruppati
function get_group($nid){

		$query="SELECT n.nid as nid, n.title as title FROM ".query_aggregation($nid)." a
			  INNER JOIN {node} n ON a.nid2=n.nid
			  WHERE n.type='gare'";
	$qy=db_query($query,$nid);
	while ($fields=db_fetch_object($qy)){
		$elements[$fields->nid]=array(
			'#type'=>'item',
			'#title'=>l($fields->title,'node/'.$fields->nid),
/*			'#description'=>get_cud_description($fields->cud),
			'#default_value'=>0,
			'#parents' => array($group,$fields->nid), // per mantenre l'array dei checkbos in $form_state[$group]
*/
		);
	}

	return $elements;
}


function get_gara_value($nid){
// recupra il valore stimato della gara
	return db_result(db_query( "SELECT sum(r.value) FROM "._REQUESTS_TABLE." r
				      INNER JOIN ".query_aggregation()." a ON a.nid2=r.nid
				      INNER JOIN {node} n ON n.nid=r.nid AND n.vid=r.vid
				      WHERE a.nid=%d GROUP BY a.nid",$nid));

}

function get_gara_count($nid){
// recupra il numero di richieste
	return db_result(db_query( "SELECT count(nid) FROM ".query_aggregation()."
				      WHERE nid=%d ",$nid));

}

function get_gara_elements($nid){
// recupra il numero totali di elemnti richiesti
	return db_result( db_query("SELECT sum(r.qta) FROM "._REQUESTS_TABLE." r
				      INNER JOIN ".query_aggregation()." a ON a.nid2=r.nid
				      INNER JOIN {node} n ON n.nid=r.nid AND n.vid=r.vid
				      WHERE a.nid=%d GROUP BY a.nid",$nid));

}

//cancella l'associazione fra gara e richiesta. Usato nel menu
function delete_aggregate_link($nid,$nid2){
	if(!delete_aggregation($nid,$nid2))
		drupal_set_message('la richiesta non � stata tolta!','warning');
//	drupal_goto('node/'.$nid);
//	drupal_set_header('Location: '.url('node/'.$nid));
	return l('Ritorna','node/'.$nid);
}

function build_export_gare_syntesi_file($form, $form_state) {
	$filename='gare-proposte-'.format_date(time(),'small').'.csv';
	$filename=str_replace(':',"-", $filename);//clean filename for IE
	$filename=str_replace(' ',"_", $filename);
	$filename=str_replace('/',"_", $filename);
	/*$file=tempnam(realpath(file_directory_temp()),$filename);
	if (!$fb=fopen($file,'wb')) return 0;
	fwrite($fb,build_export());
	fclose($fb);
	file_transfer($file,array('Content-Type: text/x-comma-seperated-values','Content-Disposition: attachment; filename="'.$filename.'"'));
	*/
	drupal_set_header('Content-Type: text/x-comma-seperated-values');
	drupal_set_header('Content-Disposition: attachment; filename="'.$filename.'"');
	print build_export($form_state);
        exit();
}


function build_export($form_state){
global $user;

	$header.='"ID-gara";"Descrizione";"quantit�";"Stima economica (iva esclusa)";"Destinatario";"Anno";"Pianificazione";';
        $form=gare_list_filter($form_state);
        $filter=get_filter($form);
        $filter=($filter<>''? ' AND ':'').$filter;

	$query="SELECT *,st.status as status, st.uid, n.created as created FROM {node} n
				INNER JOIN ".query_status()." st ON st.nid=n.nid
				WHERE n.type='gare'".$filter." ORDER BY n.title" ;
	$result=db_query($query);
    while ($links = db_fetch_object($result)) {
			$list=db_query("SELECT *, YEAR(FROM_UNIXTIME(n.created)) as year FROM "._REQUESTS_TABLE." r
				      INNER JOIN ".query_aggregation($links->nid,'gare')." a ON a.nid2=r.nid
				      INNER JOIN {node} n ON n.nid=r.nid AND n.vid=r.vid
				      INNER JOIN ".query_status()." s ON s.nid=n.nid");
			// lista contenuti

			$rows.= $header."\n ";
			$rows.='"'.$links->nid.'";'; //ID
			//descrizione
			$info=$links->title;
			$info=str_replace('"',"'", $info); //pulizia caratteri ambigui
			$info=str_replace(';',"-", $info);
			$rows.='"'. strtoupper($info).'";';

			$rows.=';';

			$rows.=';';

			$rows.=';';
			$rows.= $links->year.';';
			$rows.=";\n ";



			$value=0;
			$qta=0;
			$plan=0;
			$c_plan=0;
			while($l=db_fetch_object($list)){
				//ID
				$rows.='"'.$l->nid.'";'; //ID
				//descrizione
				$info=$l->title;
				$info=str_replace('"',"'", $info); //pulizia caratteri ambigui
				$info=str_replace(';',"-", $info);
				$rows.='"'. strtolower($info).'";';

				$rows.= $l->qta.';';
				$qta+= $l->qta;
				$rows.=$l->value.';';
				$value+=$l->value;

				$info=get_structure($l->struct);
				$info=str_replace('"',"'", $info); //pulizia caratteri ambigui
				$info=str_replace(';',"-", $info);
				$rows.='"'.$info.'";';
				$rows.= $l->year.';';
				$rows.='"'.request_status_message($l->status).'";';
				$rows.='"'.($l->idCSC?'CSC:'.$l->idCSC:'').($l->idARS?' ARS:'.$l->idARS:'').'";'."\n ";
				if ($l->planning>0 && $l->planning<5) {
					$plan+=$l->planning;
					$c_plan++;
				}

			}
			$rows.=';'; //ID

			$rows.='" TOTALE ";';

			$rows.= $qta.';';
			$rows.=$value.';';

			$rows.=';;"'.get_planning($c_plan?round($plan/$c_plan):0).' ('.gare_status_message($links->status).')";';
			$rows.="\n \n";

     }
     return $rows."\n ";
}


?>