<?php
/* install or update functions for gare_core modules
*/

define("_WITH_IVA",1.21);

define("_NO_IVA",0);
define("_IVA_22",22);
define("_IVA_21",21);
define("_IVA_20",20);
define("_IVA_10",10);
define("_IVA_4",4);



define ("_CUD_TABLE",'cnd');  	//define table name of CUD catalog
define ("_STRUCT_TABLE",'struttura'); 	//define orgainzation structure table
define ("_STATUS_TABLE",'node_status'); 	//define new node addon status table
define ("_STATUS_TABLE_LOG",'node_status_log'); 	//define log of status
define ("_AGGREGATOR_TABLE",'aggregazioni'); 	//define nede grouping
define ("_STRUCT_REFERRERS",'struct_referres'); //

/**
 *   Authorization profile
 */
define ('_QUERY','Query catlogs only'); 		//view only summary data
define ('_UDATE','Update catlogs'); 		//update core tables
define ('_MANAGE_SETTINGS','Manage sessings'); 		//Gestisce i settaggi
define ('_MANAGE_PROFILE','Manage profile'); 		//Gestisce i profili

/**
 *
 * @return string permissions 
 */
function core_permission() {  
   return array( _QUERY,
                  _UDATE,
                  _MANAGE_SETTINGS,
                  _MANAGE_PROFILE);
}


function get_iva($sel=NULL) {
    $data=array (
        _NO_IVA=>'Senza iva',
        _IVA_22=>'Compresa Iva al 22%',
        _IVA_21=>'Compresa Iva al 21%',
        _IVA_20=>'Compresa Iva al 20%',
        _IVA_10=>'Compresa Iva al 10%',
        _IVA_4=>'Compresa Iva al 4%');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}

function currencyFormat($num,$iva=NULL,$show_iva=FALSE) {
    $v_iva='';
    if (!is_numeric($num)) return '&euro; 0';
    $res=number_format($num,2,',','.');

    if ($iva!==NULL && $show_iva===FALSE) {
        if ($iva==0) $res.= ' ('.number_format($num*_WITH_IVA,2,',','.')." con iva ".((_WITH_IVA-1)*100)."%)";
        else $res.=' ('.number_format($num/(($iva/100)+1),2,',','.').' senza '.$iva.'% di iva)';
    }
    else {
        if ($show_iva && $iva!==NULL) {
            if ($iva===0) $res.=' senza Iva';
            else $res.=' con '.$iva.'% Iva';
        }
    }

    return '&euro; '.$res.$v_iva;
}
/**
 * genera un'entitÃ  html per i popup delle informazioni (teaser) del nodo
 * @param <node id> $nid
 * @return <html entity>
 */
function info_popup($nid){
    return '<span class="i_info" tipurl="'.'node/teaser/'.$nid.'">'.$nid.'</span>';
}

// node link: format  a link to node with superscript and underscript labelling info
function nl($nid) {
    drupal_add_css(drupal_get_path('module','gratcore').'/gratCore.css','module');
    $link=l($nid,'node/'.$nid);
    $qy="SELECT n.nid as nid, n.changed as changed, title, cid, comment_count, files FROM {node} n
    LEFT JOIN {node_comment_statistics} sc ON n.nid=sc.nid
    LEFT JOIN (SELECT COUNT(vid) as files, nid, vid FROM {upload} GROUP BY  vid) f ON f.nid=n.nid AND f.vid=n.vid
    LEFT JOIN {comments} c ON n.nid=c.nid WHERE n.nid=%d ORDER BY c.cid";
    $res=db_query($qy,$nid);
    $l = db_fetch_object($res);
    if ($l) {
        if ($l->comment_count)
            $comment_link='<sup class="notice">'.l($l->comment_count,'node/'.$nid,
                array(
                'fragment'=>'comment-'.$l->cid,
                'attributes'=>array(
            //        'title'=>'ci sono commenti al documento',
                    'tipurl'=>'node/comment/tips/'.$nid
                    ),
                )
                ).'</sup>';
        else $comment_link='';
        if ($l->files)
            $files='<sub>'.l($l->files,'node/'.$nid,
                array(
                'fragment'=>'attach-'.$l->cid,
                'attributes'=>array(
                    'title'=>'ci sono file allegati',
                   'class'=>'i_attach',
                ))
               ).'</sub>';
        else $files='';
        $link=l(truncate_string($l->title),'node/'.$nid, _nl_mark_theme(node_mark($nid, $l->changed))).$files.$comment_link;
    }
    return $link;

}
function _nl_mark_theme($type = MARK_NEW, $tipurl=NULL) {
    global $user;
    $ret['attributes']=array();
    //riferimento per l'eventuale popupesplicativo caricato via ajax
//    if (isset($tipurl)) $ret['attributes']['tipurl']=$tipurl;
    if ($user->uid) {
        if ($type == MARK_NEW) {
            $ret['attributes']['title']=t('new');
            $ret['attributes']['class'].=' i_new';
        }
        else if ($type == MARK_UPDATED) {
                $ret['attributes']['title']=t('updated');
                $ret['attributes']['class'].=' i_updated';
            }
            else 
                $ret['html'] = FALSE;
    }
    else $ret['html'] = FALSE;
    return $ret;
}



/*---------------------------------- funzioni gestione stato  --------------------------------*/
/**
 *  imposta lo stato del nodo
 * @global <object> $user
 * @param <int> $nid
 *      ID nodo 
 * @param <int> $status 
 *      ID nuovo stato
 */
function set_status($nid,$status) {
    global $user;
    //trasferimetnto ultimo stato
    if (db_query("INSERT INTO "._STATUS_TABLE_LOG." (nid, uid,  status, changed)
	         SELECT nid, uid,  status, changed FROM "._STATUS_TABLE." WHERE nid=".$nid)) {
    //aggiormamento stato
        db_query("DELETE FROM "._STATUS_TABLE." WHERE nid=%d",$nid);
        db_query("INSERT INTO "._STATUS_TABLE." (nid, uid,  status, changed) VALUES (%d, %d, %d, %d)", $nid, $user->uid, $status, time());
    }// II tentativo per correzione sovrapposizione di indici 
    else if (db_query("INSERT INTO "._STATUS_TABLE_LOG." (nid, uid,  status, changed) 
	         SELECT nid, uid,  status, changed +3 FROM "._STATUS_TABLE." WHERE nid=".$nid)){
        drupal_get_messages('error');//remove warning
        db_query("DELETE FROM "._STATUS_TABLE." WHERE nid=%d",$nid);
        db_query("INSERT INTO "._STATUS_TABLE." (nid, uid,  status, changed) VALUES (%d, %d, %d, %d)", $nid, $user->uid, $status, time()); 
    }

}
/**
 *  cancella tutti gli stati del nodo (solo per servizio)
 * @param <int> $nid
 *   ID nodo
 */
function delete_status($nid) {
    db_query("DELETE FROM "._STATUS_TABLE." WHERE nid=%d ", $nid);
    db_query("DELETE FROM "._STATUS_TABLE_LOG." WHERE nid=%d ", $nid);
}
/**
 *  legge lo stato del nodo
 * @global <object> $user
 * @param <int> $nid
 * @return <int> stato o falso se non trvato
 */
function get_status($nid) {
    $sql=db_query("SELECT nid, uid, status, changed
			   FROM "._STATUS_TABLE." WHERE nid=%d",$nid);
    $res = db_fetch_object($sql);
    if ($res)
        return $res->status;
    else
        return FALSE;
}
/**
 *  recupera l'utente che ha impostato l'ultimo stato
 * @param <type> $nid
 * @return <type>
 */
function get_status_uid($nid) {
    $sql=db_query("SELECT nid, uid, status, changed
			   FROM "._STATUS_TABLE." WHERE nid=%d",$nid);
    if ($res=db_fetch_object($sql))
        return $res->uid;
    else
        return FALSE;
}

function query_status($status_list=NULL) {
    global $user;
    $filter='';
	/*$sql="SELECT nid,  uid,  status,  changed
		  FROM "._STATUS_TABLE;*/
    if($status_list===NULL || trim($status_list)=='' || !isset($status_list)) return _STATUS_TABLE;
    if(is_array($status_list))
        foreach($status_list as $key) $filter.=($status_list[$key])?(($filter<>'')?', ':'').$key:'';
    else
        $filter=$status_list;
    if ($filter=='') return _STATUS_TABLE;
    return ' (SELECT * FROM '._STATUS_TABLE.' WHERE status IN ('.$filter.') ) ';
}
/*---------------------------------- funzioni di modulo --------------------------------*/
function get_core_modules() {
    $core='gratcore';
    $qy=db_query("SELECT info FROM {system} WHERE type = 'module' AND name <> '%s'", $core);
    while ($res=db_fetch_object($qy)) {
        $info=unserialize($res->info);
        if (is_array($file->info['dependencies']))
            if (array_search($core,$file->info['dependencies'])!==FALSE)
                $modules[]=$file->info['name'];

    }

    return $modules;
}
/*---------------------------------- funzioni di aggregazione --------------------------------*/
// associa due nodi fra loro
/**
 *  funzione per aggregare i nodi fra loro 
 * 
 * @global <object> $user
 *      utente che effettua l'operazione
 * @param <int> $nid
 *        ID  nodo associato principale
 * @param <type> $nid2
 *      ID nodo secondario
 * @param <type> $vid2
 *      Version ID nodo secondario
 * @return <int> 
 *      ritorna l'id dell'aggregazione o falso se non può farla 
 */
function aggregate_node($nid,$nid2,$vid2=0) {
    global $user;

    // verifica esistenza dei nodi
    if (db_result(db_query("SELECT count(nid) FROM {node} WHERE nid=%d OR nid=%d",$nid,$nid2))!=2) {
        return NULL;
    }
    db_query("INSERT INTO "._AGGREGATOR_TABLE." (nid,nid2,vid2,uid,created)
                  VALUES (%d,%d,%d,%d,%d)",$nid,$nid2,$vid2,$user->uid,time());
    return db_last_insert_id(_AGGREGATOR_TABLE,'aid');
}


/**
 *  interroga le aggregazioni
 *
 * @param <int>|<string> $nid
 *      id del nodo da interrogare oppure nome del campo da interrogare (se stringa)
 * @param <string> $type
 *      tipo di nodo da selezionare
 * @return <string>
 *      ritorna la query o la stringa vuota in caso di errori
 */
function query_aggregation($nid=NULL,$type=NULL) {
  /* se $nid � numerico seleziona il gruppo se � stringa seleziona il campo da restituire, se non c'� tutta la tabella*/
    if (!$nid && !$type) return _AGGREGATOR_TABLE;
    if(is_numeric($nid)) return "( SELECT a.nid as nid , a.nid2 as nid2, n.title as title, n.type as type FROM "._AGGREGATOR_TABLE." a INNER JOIN {node} n ON n.nid=a.nid  WHERE a.nid=".$nid." AND n.type='".$type."' )";
    if(is_string($nid)) return "( SELECT ".$nid." FROM "._AGGREGATOR_TABLE." a INNER JOIN {node} n ON n.nid=a.nid  WHERE n.type='".$type."' )";
    return ''; // any other case

}

/**
 *  restituisce la lista dei link dei nodi associati, vengono rappresentate tutte le aggregazioni associate al nodo in qualsiasi direzione
 * @param <type> $nid
 *   ID nodo
 * @return <array>
 *   array  con la lista degli  hyper-link
 
function get_aggregation_links($nid) {
    $link=NULL;
    $res=db_query("SELECT a.nid as nid , a.nid2 as nid2, n.title as title, n.type as type FROM "._AGGREGATOR_TABLE." a
				   INNER JOIN {node} n ON  n.nid=a.nid
				   WHERE a.nid=%d OR a.nid2=%d",$nid,$nid); // ricerca tutte le aggregazioni
    while($l=db_fetch_object($res)) {
        $links[]='<span>'.t($l->type).': </span>'.l($l->title,'node/'.($nid==$l->nid?$l->nid2:$l->nid));// compone un array di link di aggregazioni
    }
    return $links;
}*/

function insert_aggregation(&$node_content,$nid,$teaser=FALSE){
    if (!isset($node_content['aggregations'])){
        $node_content['aggregations'] = array(
            '#type'=>'fieldset',
            '#collapsible'=> TRUE,
            '#collapsed'=>FALSE,
            '#title'=>'Argomenti associati',
            '#weight' => 1,
        );
    }
    if (!is_array($nid)) $nid=array($nid);
    foreach ($nid as $n) {
        $nd=node_load($n);
        $type=node_get_types('name',$nd);
        $node_content['aggregations'][$nd->nid]=array(
            '#value' =>'<span>'.$type.': </span>'.l($nd->title,'node/'.$nd->nid).'<br>',
        );
        
        /********** inserisce i file aggregati ******/
        $tmp=isset($node_content['files']['#weight'])?$node_content['files']['#weight'] : 50;
        static $f_weigh = 1;
         
        if (!$teaser && user_access('view uploaded files')) {
            if (count($nd->files)) {
                // crea il contenitore se non esiste
              if (!isset($node_content[$nd->type])) 
                  $node_content[$nd->type]=array(
                   '#type'=>'fieldset',
                   '#collapsed'=>TRUE,
                   '#collapsible'=>TRUE,
                   '#title'=>'File associati di '.$type.'',
                   '#weight' => $tmp + $f_weight++,
              );
              $node_content[$nd->type][$n]= array(
                '#title'=>$nd->title,
                '#type'=>'item',
                '#value' => theme('upload_attachments', $nd->files),
              );
            }
          }            
        /******** fine inserimento file ******/
        
    }
}

/**
 *  recupera il nodo associato a $from_nid
 * @param <int> $from_nid
 *      id nodo padre
 * @param <string> $type
 *      id tipo di nodo 
 * @return <int> 
 *      Version id del nodo associato
 */
function get_aggregations($from_nid,$type) {
    $qy=db_query("SELECT a.nid2 as nid ,a.vid2 as vid FROM "._AGGREGATOR_TABLE." a
                  INNER JOIN {node} n ON n.nid=a.nid WHERE a.nid=%d AND n.type='%s'",$from_nid,$type);
    // recuper il nodo e, se non ha il vid recupera l'ultimo
    if ($res = db_fetch_object($qy))
        if ($res->vid==0) $res->vid=db_result(db_query("SELECT vid FROM {node} WHERE nid=%d",$res->nid));
    return $res;
}


/**
 *      recupera il nodo che associa  $to_nid
 * @param <int> $to_nid
 *      ID nodo associato
 * @param <string> $type
 *      tipo di nodo associato
 * @return <int>
 *      ID nodo associante
 */
function get_aggregator($to_nid,$type) {
    return db_result(db_query("SELECT a.nid as nid FROM "._AGGREGATOR_TABLE." a
                               INNER JOIN {node} n ON n.nid=a.nid WHERE a.nid2=%d AND n.type='%s'",$to_nid,$type));
}

/**
 *  cancella l'aggregazione
 * @param <int> $nid
 *  ID nodo aggregante
 * @param <int> $nid2
 *  ID nodo aggregato
 * @return <bool>
 *  VERO se è tutto ok
 */
function delete_aggregation($nid,$nid2) {
    return db_query("DELETE FROM "._AGGREGATOR_TABLE." WHERE nid=%d AND nid2=%d",$nid,$nid2);
}


// accorcia le descrizioni
function truncate_string($string,$size=60) {
    $l=$size/2-1;
    $string=trim($string);
    if (strlen($string) < $size) return check_plain($string);
    return check_plain(
    substr($string,0,$l).'...'.substr($string,strlen($string)-$l,$l)
    );
}

function validate_integer ($element,&$form_state) {
// check if the element is a number
//print 'elemnt';
//print_r($element['#name']);
//	if (!is_numeric($element['#value'])) form_error($element,'Il campo '.$element['#title'].' deve essere un numero intero!');
    $val=preg_replace('/\./','',$element['#value']);
    $val=preg_replace('/,/','.',$val);
    if(is_numeric($val) ) {
        $val=preg_replace('/\./',',',$val);
        $form_state['values'][$element['#name']]=$val;
        if($element['#name']=='value' && $val==0 ) form_error($element,'Il campo '.$element['#title'].' non pu&ograve; essere zero');
    }
    else {
        form_error($element,'Il campo '.$element['#title'].' deve essere un numero intero senza punti o virgole!');
    }
}


/**
 * converte il formato timestamp in array per il campo date
 * @param int $time orario in timestamp
 * @return array
 */
function timeToDateArray($time = 0) {
    $date = explode(',', date('Y,m,d', $time));
    return array('year' => (int) $date[0], 'month' => (int) $date[1], 'day' => (int) $date[2]);
}

/**
 * converte il formato array proveniente dal campo date in timestamp
 * @param array $date
 * @return int timestamp
 */
function dateArrayToTime($date = array()) {
    if (empty($date))
        return 0;
    $time = new DateTime($date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' 00:00:01 ');
    return (int) $time->format('U');
}