<?php
/* 
 * Modulo per la gestione della struttura aziendale
 */


include_once ('gratcore.php.inc');
define("_CYCLE_BOUNDARY",5000);

//function struct_manage ($mode='list',$id=NULL){
//    global $user;
//    switch ($mode){
//        case 'list':
//            return drupal_get_form('struct_list');
//        break;
//        case 'edit':
//            return drupal_get_form('struct_admin_form',$id);
//        break;
//        case 'add':
//            return drupal_get_form('struct_admin_form','new');
//        break;
//    }
//}

//TODO: implementare tutti i livelli di estrazione
function struct_list(){
    $header=array('descrizione','info','riferito a','sostituisce','creato','');
    
    $qy='SELECT *  FROM '._STRUCT_TABLE.'
        WHERE replaced=0 AND parent=%d ORDER BY description';
    $res=db_query($qy,0);
    while ($dpt=db_fetch_object($res)){
        
        $rst=db_query($qy,$dpt->id);
        $rws[]=get_row($dpt);
        while($st=db_fetch_object($rst))  $rows[]=get_row($st);
        $frm=array(
            '#type'=>'fieldset',
            '#title'=>$dpt->description,
            '#collapsible'=>TRUE,
            '#collapsed'=>TRUE,
            '#value'=>theme('table',$header,$rows),
        );
        unset($rows);
        $rws[]=array(array('data'=>drupal_render($frm),'colspan'=>7));
        
    }
    $output.=theme('table',$header,$rws);
    $form['add']=array('#value'=>l('Aggiungi','admin/settings/structure/add'));
    $form['table']=array('#value'=>$output);
    $breadcrumbs[]=l(t('Home'),'<front>');
    $breadcrumbs[]=l(t('Administer'),'admin/');
    drupal_set_breadcrumb($breadcrumbs);
    return $form;
}

function get_row($rs){
    $row[]='<span tipurl="gratCore/userSearch/'.$rs->id.'">'.$rs->short.' '.$rs->description.'</span>';
    $row[]=$rs->info;
    $parent=($rs->parent?get_structure($rs->parent):array(''));
    $row[]=$parent[0];
    $rpl=($rs->replaced?get_structure($rs->replaced):array(''));
    $row[]=$rpl[0];
    $row[]=format_date($rs->created,'short');
    $row[]=l('Modifica','admin/settings/structure/'.$rs->id.'/edit');
    return $row;
}

function get_struct_users($struct){
    $qy="SELECT u.uid as uid, u.name as name FROM {users} u
        INNER JOIN "._STRUCT_REFERRERS." s ON s.uid=u.uid 
        WHERE s.struct=%d ";
    $rs=db_query($qy,$struct);
    $res=array();
    while ($rw=  db_fetch_object($rs)) {
        $res[$rw->uid]=$rw->name;
    }
    return $res;
}

function struct_admin_form(&$form_state,$id){
    $qy='SELECT * FROM '._STRUCT_TABLE.'
    WHERE replaced=0 AND id=%d';
    $res=db_query($qy,$id);
    if (($row=db_fetch_object($res)) || $id==='new'){
        $form['id']=array(
            '#type'=>'hidden',
            '#value'=>$id,     
        );
        $form['short']=array(
            '#default_value'=>$row->short,
            '#type'=>'textfield',
            '#title'=>'prefisso',
            '#description'=>'SOS, SOC, DPT, UO ecc.',
            '#size'=>6,
            
        );
        $form['description']=array(
            '#default_value'=>$row->description,
            '#type'=>'textfield',
            '#title'=>'Nome struttura',
            '#required'=>TRUE,
            );

        if ($id<>'new')
            $form['refer']=array(
                '#default_value'=>isset($row->uid)?$row->uid:0,
                '#type'=>'select',
                '#options'=>get_struct_users($id),
                '#title'=>'Responsabile struttura'
             );


        $form['info']=array(
            '#default_value'=>$row->info,
            '#title'=>'Informazioni descrittive',
            '#type'=>'textfield',
            
            
        );
        $form['parent']=array(
            '#type'=>'select',
            '#title'=>'Struttura Padre',
            '#default_value'=> (int) $row->parent,
            '#options'=>get_structure(NULL,40),
        );

        
        if ($id!=='new'){
            $form['users']=get_users($id);
            $form['del']=array(
                '#type'=>'fieldset',
                '#collapsed'=>TRUE,
                '#collapsible'=>TRUE,
                '#title'=>'Elimina voce',
                '#description'=>'Attenzione con questa operzione viene eliminata la voce della struttura tutti i riferimenti ad essa verranno spostati.
                                 Questa operazione potrebbe far perdere alcuni riferimenti.',
                '#access'=>user_access(_MANAGE_SETTINGS),
            );
            $form['del']['destination']=array(
                '#type'=>'select',
                '#title'=>'Struttura di destinazione',
                '#default_value'=> 0,
                '#options'=>get_structure(NULL,40),
            );
            $form['del']['delete']=array(
                '#type' => 'button',
                '#value'=> 'Cancella',
                '#submit'=>array('struct_admin_delete'),
                '#executes_submit_callback' => TRUE,
                '#attributes'=>array('onclick'=>'return confirm("Vuoi eliminare DEFINITIVAMENTE la struttura?");'),
            );
        }
        $form['submit']=array(
            '#type' => 'button',
            '#value'=> 'Aggiorna',
            '#submit'=>array('struct_admin_submit'),
            '#executes_submit_callback' => TRUE,
            
        );
        

    }
    $form['#redirect'][]='admin/settings/structure';
    $breadcrumbs[]=l(t('Home'),'<front>');
    $breadcrumbs[]=l(t('Administer'),'admin/');
    $breadcrumbs[]=l('G.I.T. struttura aziendale','admin/settings/structure');
    drupal_set_breadcrumb($breadcrumbs);
    return $form;
}

function struct_admin_submit($form, &$form_state){
    $value=$form_state['values'];
 
    if ($value['id']) {
         if ($value['id']=='new'){
             db_query("INSERT INTO "._STRUCT_TABLE." (description,short,info,parent,created) VALUES ('%s','%s','%s',%d,%d)" ,
                     $value['description'],$value['short'],$value['info'],$value['parent'],time() );
         }
         else {
             db_query("UPDATE "._STRUCT_TABLE." SET description='%s',short='%s',info='%s',parent=%d,created=%d,uid=%d
                      WHERE id=%d",
                     $value['description'],$value['short'],$value['info'],$value['parent'],time(),$value['refer'],$value['id']);
         }
    }
}

function struct_admin_delete($form, &$form_state){
    $values=$form_state['values'];
    if ($values['id']=='new' ||
        $values['id']==0 ||
        $values['destination']==0 ||
        $values['id']==$values['destination']) {
        drupal_set_message('Per cancellare una struttura si deve indicare la struttura di destinazione','warning');
        return;
     }
    //richiama tutte le procedure per lo spostamento dei riferimenti
    module_invoke_all('structure_move',$values['id'],$values['destination']);
    // sposta i figli sul nuovo padre
    db_query("UPDATE "._STRUCT_TABLE." SET parent='%d' WHERE parent=%d ",$values['destination']
                     ,$values['id']);
    //cancella il riferimento
    db_query('DELETE FROM '._STRUCT_TABLE.' WHERE id=%d',$values['id']);
    $form_state['redirect'][]='admin/settings/structure';
    return TRUE;
}

function get_users($s_id){
    $qy='SELECT * FROM {users} WHERE uid<>0 AND uid<>1 ORDER BY name';
    $res=db_query($qy);
    while ($u=db_fetch_object($res)){
        if ($u->uid==0 || $u->status==0) continue;
        $user=user_load($u->uid);
        if(!is_array($user->struct))$st=array($user->struct); else $st=$user->struct;
        if (in_array(0,$st))$users['admin'].=l($user->name,'user/'.$u->uid).'<br>';
        else
            if (in_array($s_id,$st)||in_array(0,$st))$users['users'].=l($user->name,'user/'.$u->uid).'<br>';
    }
    $form=array(
        '#type'=>'fieldset',
        '#title'=>'Utenti associati',
        '#value'=>'<table><tr><th>Utenti</th><th>Amministratori</th></tr>
                  <tr><td>'.$users['users'].'</td><td>'.$users['admin'].'</td></tr></table>',
    );
    return $form;
}

function output_users($id){
    $form=get_users($id);
    print drupal_render($form);
    exit (0);
}


function theme_tree_view_check($form){
//    foreach(element_children($form) as $key)
    $output.=drupal_render($form['struct_selection']);
    $output.='<div id="modal"><a id="tree-message" dialogModal="Struttura aziendale" href="#"> Selezione struttura</a>';
    $output.='<div class="treeview"  style="display: none;"><ul class="treeview"><li id="ALL">';
//    $output.=drupal_render($form['ALL']);
    $output.=recursive_render($form['ALL']);
    $output.='</li></ul></div></div>'.drupal_render($form);
    return $output;
}

/**
 *riproduce ricorsivamente la struttura ad albero in forma elanchi puntati gerarchici
 * @param array $value contenitore dei check box
 * @return elenco puntato gerarchico
 */
function recursive_render(&$value){
    $out='';
    foreach (element_children($value) as $key){
        $o=recursive_render($value[$key]);
        if (isset($o)) $out.='<li class="closed" id="'.$key.'">'.$o.'</li>';  //id=$key per le selezioni da javascript
        unset($o);
    }
    if($out!=='') $out='<ul>'.$out.'</ul>';
    $out=drupal_render($value).$out;
    return $out;
}

function build_structure_tree($selection, $all_label){
    $boundary=0; //controllo di overflow dell'albero
    $struct=array();    //radice albero della struttura
    $reference[0]=&$struct; // matrice dei riferimenti alle sottostrutture
    $tmplist=array();
    if ($selection==0) {$struct[0]=$all_label; $selection=0;} //normalizza i null ecc a 0
    $start_list=(is_array($selection)?$selection:explode(',',$selection));
    foreach($start_list as $id=>$val)
          has_child($id,$tmplist);

    // TODO: controllo che non ci sia lo 0 nella selezione
    while (!empty($tmplist) && $boundary< _CYCLE_BOUNDARY){
        $d=array_pop($tmplist);
        $id=$d['id'];
        $description=$d['short'].' '.$d['description'];
        $p=(int)$d['parent'];
        if (has_child($id,$tmplist)){
            $reference[$p][$description]=array($id=>$description);
            $reference[$id]=&$reference[$p][$description]; //salva il riferimento
        }
        else {
            $reference[$p][$id]=$description;
        }
        $boundary++;
    }
    return $struct;
}


function has_child($id,&$addlist){
    $has=FALSE;
    $table='{'._STRUCT_TABLE.'}';
    $res=db_query("SELECT id,description,parent,short FROM $table 
            WHERE parent=%d AND replaced=0 ORDER BY description DESC", $id);
    while ($row=db_fetch_array($res)) {
        array_push($addlist,$row);
        $has=TRUE;
    }
    return $has;
}

/**
 * costruisce l'albero di selezione con dei checkbox
 * @param array $selection contiene un sottoinsieme delle strutture, quelle abilitate
 * @param array $default_value contiene l'elenco delle impostazioni di default dei checkbox, l'array non è strutturato 
 * @return string 
 */

function build_structure_form($selection, $default_value=NULL){
    $boundary=0; //controllo di overflow dell'albero
    $struct=array();    //radice albero della struttura
    $reference[0]=&$struct; // matrice dei riferimenti alle sottostrutture
    $tmplist=array();
    $default_value=(is_array($default_value))?$default_value:explode(',',$default_value);
    foreach($default_value as $key) $default[$key]=$key;
    $start_list=(is_array($selection)?$selection:explode(',',$selection));
    foreach($start_list as $id=>$val)
          has_child($id,$tmplist);

    // TODO: controllo che non ci sia lo 0 nella selezione
    while (!empty($tmplist) && $boundary< _CYCLE_BOUNDARY){
        $d=array_pop($tmplist);
        $id=$d['id'];
        $description=$d['short'].' '.$d['description'];
        $p=(int)$d['parent'];
        $reference[$p][$id]=array(
            '#title'=>$description,
            '#type'=>'checkbox',
            '#return_value'=>$id,
            '#default_value'=>(isset($default[$id])?$id:0),
//            '#parents'=>array('ALL',$id),
        );
        if (is_array($default_value)&& isset($default_value[$id]))
            $reference[$p][$id]['#default_value']=$default_value[$id];
        if (has_child($id,$tmplist)){
            $reference[$id]=&$reference[$p][$id]; //salva il riferimento
        }
        
        $boundary++;
    }
    unset($struct[0]);
    $s['ALL']=$struct;
    $s['ALL']['#title']=t('All');
    $s['ALL']['#type']='checkbox';
    $s['ALL']['#return_value']='ALL';
    $s['struct_selection']=array('#type'=>'hidden','#value'=>'');
 //   $struct['#parents']=array('TREE','ALL');
    $s['#theme']='tree_view_check';
  //  $struct['#type']='treeview';
    
    return $s;
}

/**
 * recupeta l'orgnigramma ad albero per una select
 * @param <int> $stID id della struttura di riferimento, se NULL seleziona tutte le strutture disponibili per l'utente
 * @param <int> $size dimensione delle stringhe di descrizione
 * @param <int> $selectSTRUCT id struttura di riferimento padre (tutte se NULL)
 * @param <string> $all_LABEL ettichetta da attirbuire alla selezione 0
 */
function get_structure($stID=NULL,$size=NULL,$selectSTRUCT=NULL,$all_LABEL="(selezionare)") {

/* retun list of structures or structure description */
    $table='{'._STRUCT_TABLE.'}';
    if (is_array($selectSTRUCT)) $selectSTRUCT=implode(',',$selectSTRUCT);
    if ($selectSTRUCT==0) $struct[0]=$all_LABEL;
    

    if ($stID===NULL) {
        return build_structure_tree($selectSTRUCT,$all_LABEL);
    }
    else {
        if($stID===0) return array($all_LABEL);
        if (is_array($stID)) $stID=implode(',',$stID);
        $result=db_query("SELECT description,short,id FROM $table WHERE replaced = 0 AND id IN (".$stID.")");
        while ($rs = db_fetch_object($result)) {
/* ricerca dei vecchi nomi della struttura*/
            $result2=db_query("SELECT description FROM $table WHERE replaced = %d ",$rs->id);
            if($rs2 = db_fetch_object($result2))
                $res[]=$rs->short.' <b>'.$rs->description.'</b><small>(ex '.$rs2->description.')</small>';
            else
                $res[]=$rs->short.' <b>'.$rs->description.'</b>';
        }
        if (!isset($res))
            return array($all_LABEL); // quando sono selezionatie tutte le strutture $res è vuoto
        else
            return $res;
    }

}

function queryStruct($stID) {
    if (!is_array($stID)) $stID=array($stID);
    $res=$stID;
    $stID=implode(',',$stID);
    $result = db_query("SELECT id FROM "._STRUCT_TABLE." WHERE  replaced=0 ".($stID?"AND parent IN (".$stID.")":''));
    while ($rs = db_fetch_object($result)) $res[]=$rs->id ;
    if (in_array(0,$res)) // 0 implica tutta la lista 
        return FALSE;
    else
        return implode(',',$res);
}


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
