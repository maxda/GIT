<?php
/*
 * modulo per l'import dei collaudi attuali
 */


function load_tested(){
    global $user;
    $u=$user;
    $user=user_load(array('uid'=>12));
    update_table();
    /*
     * carica la tabella principale collaudi
     */
    $qy="SELECT sistema_apparecchiatura AS title,
        UNIX_TIMESTAMP(data_1a_installazione) AS created,
        data_collaudo as collaudo,
        ID as tid
        FROM "._TABLE_COLLAUDI." WHERE nid=0 AND (NOT numero_ordine IS NULL AND NOT sistema_apparecchiatura IS NULL) ";
    $res=db_query_range($qy,1,100);
    $count=0;
    while($row=db_fetch_object($res)){
        //* crea un nodo per ogni collaudo
        $nid=create_node($row->title,'non complialto',$row->created);
        //* aggiorna vid e nid della tabella dei collaudi
        db_query("UPDATE "._TABLE_COLLAUDI." SET nid=%d WHERE ID=%d",
                $nid,$row->tid);
        // traferisce solo i file dei collaudi effettuati
        if ($nid && !is_null($row->collaudo)) {
            $node=node_load($nid);
            file_transfert($node);
        }
        $count++;      
    }
    $user=$u;
    if ($count) {
        watchdog('tested', 'Importati '.$count.' collaudi',NULL,WATCHDOG_INFO);
        return 'imoprtati '.$count.' collaudi';
    }
}

function update_table(){
    if(variable_get('collaudi_update', FALSE)) return TRUE;
    variable_set('collaudi_update', TRUE);
    $ret=array();
    db_add_field($ret, _TABLE_COLLAUDI, 'nid',array('type'=>'int', 'not null'=>TRUE, 'default'=>'0'), array('indexes'=>array('nid'=>array('nid'))));
    db_add_field($ret, _TABLE_COLLAUDI, 'vid',array('type'=>'int', 'not null'=>TRUE, 'default'=>'0'), array('indexes'=>array('vid'=>array('vid'))));
    return $ret;
    /*db_query("ALTER TABLE "._TABLE_COLLAUDI."
        ADD COLUMN vid INT(10) NOT NULL DEFAULT 0  AFTER TIMESTAMP ,
        ADD COLUMN nid INT(10) NOT NULL DEFAULT 0  AFTER TIMESTAMP ,
        ADD INDEX nid (nid ASC),
        ADD INDEX vid (vid ASC) ;") ;
     *
     */
}

/* crea il nodo per il collaudo */
function create_node($title,$body='non compilato',$time=NULL){
    global $user;
   /* $node = array('type' => 'tested');
    $form_state['values']['title'] = $title;
    $form_state['values']['body'] = $body;
    $form_state['values']['op'] = t('Save');
    drupal_execute('node_form', $form_state, (object)$node);
    return $node; */
    $nid=0;
    $time= is_null($time)?time():$time;
    db_query("INSERT INTO {node_revisions} (uid,title,body,teaser,log,timestamp)
			  VALUES (%d,'%s','%s','%s','Aggregazione automatica',%d)",$user->uid,$title,$body,$body,time());
    $vid=db_last_insert_id('{node_revisions}','vid');

    db_query("INSERT INTO {node} (vid,type,language,title,uid,
		 created,changed,comment,promote)
		VALUES (%d,'tested','it','%s',%d ,
		%d,%d,2,1)",$vid,$title,$user->uid,$time,time());
    $nid=db_last_insert_id('{node}','nid');

    db_query("UPDATE {node_revisions} SET nid=".$nid." WHERE vid=".$vid);
    return $nid;
}


function file_transfert(&$node){
    global $user;
 //   $src_path="\\\\Svr-aoudf1\\Tecnico\\Medicali\\Collaudi SOC Ingegneria Clinica\\".$node->numero_ordine;
    $src_path="sites/default/files/tested/".$node->numero_ordine;
    $path=file_create_path('sites/default/files/');
    $path=$path.$node->nid;
    file_check_directory($path, FILE_CREATE_DIRECTORY);

    $filelist=file_scan_directory($src_path,'.+');
    foreach($filelist as $ff){
        if (TRUE) {//(file_copy($ff->filename,$path,FILE_EXISTS_REPLACE)){
            $file->uid=$user->uid;
            $file->status=FILE_STATUS_PERMANENT;
            $file->timestamp=time();
            $file->filename=$ff->basename;
            $file->filepath=$path.'/'.$ff->basename;
            $file->filemime=file_get_mimetype($file->filepath);
            $file->filesize=filesize($file->filepath);
            $ty=preg_replace('/.+_([A-z]{2}[0-2]{0,1}).[p|P][d|D][f|F]$/','$1',$file->filename);
            switch ($ty) {
                case 'CC': $file->description='Comunicazione Collaudo'; break;
                case 'CO' : 
                case 'CO1':
                case 'CO2':
                    $file->description='Comunicazione'; break;
                case 'DF': $file->description='Detenzione fascicolo'; break;    
                case 'EV': $file->description='Esecuzione verifiche'; break;
                case 'OP': $file->description='Nulla osta pagamento'; break;
                case 'RV': $file->description='Richiesta verifiche'; break;
                case 'VA': $file->description='Verbale accettazione'; break;
                case 'VF': $file->description='Verbale formazione'; break;
                case 'VT': $file->description='Verifiche tecniche'; break;
                default: $file->description=$ff->nome; break;
            }
            $file->new=TRUE;
            $file->list=TRUE;
            drupal_write_record('files', $file);
            $node->files[$file->fid]=$file;
            watchdog('tested', 'Importato file '.$file->filename.' su numero_ordine'.$node->numero_ordine.' (id '.$node->nid.')',NULL,WATCHDOG_INFO);
            unset($file);
        }
    }
    upload_save($node);
}