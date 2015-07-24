<?php
// $Id$

include_once("admin_settings.php.inc");
//include_once(drupal_get_path('module','gratcore').'/gratCore.php.inc');

//define(_TABLE_COLLAUDI,'principale_di_collaudo');
//define(_ITEMS_COLLAUDI,'apparecchiature_per_ordine');
function tabelize($qy){
    while ($row=oci_fetch_array($qy)){
            if (!isset($header)){
            foreach ($row as $key => $value) {
                    if (!is_numeric($key)) $header[]=$key;
            }
                //$output.="<tr>$header</tr>";
        }
            foreach ($row as $key => $value) {
                if (!is_numeric($key))$rw[]=$value;
        }
            $output[]=$rw;
            unset($rw);
    }
     return theme('table',$header,$output);
}
function ascott_connect(){
//    return oci_connect(variable_get('ascott_usr', _ASCOTT_USR), variable_get('ascott_pwd', _ASCOTT_PWD), variable_get('ascott_connect', _ASCOTT_CONNECT));
    return false;
}

function check_order($year,$number){
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT  COUNT(*) AS orders FROM E14SEL.E_ORDINI o WHERE
//                        o.PROT_ORDINE=$number AND o.ANNO=$year");
//     oci_define_by_name($qy, 'ORDERS', $orders);
//     oci_execute($qy,OCI_DEFAULT);
//     oci_fetch($qy);
//     oci_close($conn);
     return $orders;
}

function ascott_order($year,$number){
//    $output='<table>';
//    //db_set_active('ascott');
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT * FROM E14SEL.E_ORDINI o WHERE
//                        o.PROT_ORDINE=$number AND o.ANNO=$year");
//    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
//    while ($row=oci_fetch_array($qy)){
//            $output.='<caption> Dati ordine </caption><tr><th width="20%">Voce</th><th>Contenuto</th></tr>';
//            foreach ($row as $key => $value) {
//                if (!is_numeric($key))$output.='<tr><td>'.$key.':</td><td><b>'.$value.'</b></td><tr>';
//            }
//     }
//     $output.='</table>';
//     oci_close($conn);
     return $output;
}

function check_beni_order($year,$number){
    $conn = ascott_connect();
    $qy=oci_parse($conn,"SELECT COUNT(*) AS beni FROM E14SEL.E_V_BEIN_DETT o WHERE
                        o.ORDI_PROT_ORDINE=$number AND o.ANNO_ORDI=$year");
     oci_define_by_name($qy, 'beni', $beni);
     oci_execute($qy,OCI_DEFAULT);
     oci_fetch($qy);
     oci_close($conn);
     return 1;//$beni;
}
function ascott_beni_order($year,$number){
    //$output='<table>';
    //db_set_active('ascott');
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT
//                        PROG_INTROITO,
//                        ANNO,
//                        NUMERO_INTROITO,
//"./*
//                        BARRA_INTROITO,
// */"
//                        MATRICOLA_BENE,
//                        DESC_CATALOGO,
//                        DESC_INTROITO,
//"./*                        ASSOCIATO,
//                        POSIZIONE,
//                        POSS_CODI_TITOPOSS,
//                        FLAG_PROPRIETA,
//                        STCO_CODI_CONSERVAZIONE,
//                        SUBC_CODI_SUBCENTRO,
//                        DESC_SUBCENTRO,
//                        QUAN_LOTTO,
// 
//                        COSTO_INIZIALE_IVATO,
//*/"                        COSTO_INIZIALE_IVATO_E
//"./*
//                        CAPA_CODI_CATE_PATRIM,
//                        SUPA_CODI_SCATE_PATRIM,
//                        CLGE_CODI_CLASS_GEST,
//                        CODI_CLASS_MERCEO,
//                        CODI_SCLASS_MERCEO,
//                        DISPONIBILE,
//                        DATA_INTROITO,
//                        DATA_ALIENAZIONE,
//                        PERC_AMMORTAMENTO,
//                        VALORE_AMMORTIZZABILE,
//                        VALORE_ACCESSORI,
//                        VALORE_ACCANTONATO,
//                        VALORE_AMMORTIZZABILE_E,
//                        VALORE_ACCESSORI_E,
//                        VALORE_ACCANTONATO_E,
//                        CIMO_CLS_MOD,
//                        CIMO_DIT_MOD,
//                        CIMO_MOD_MOD,
//                        COD_ENTE,
//                        ANNO_ORDI,
//                        ORDI_PROT_ORDINE,
//                        ESER_ESERCIZIO_DOCU,
//                        DOCU_NUME_INTERNO,
//                        ANNO_BOLL,
//                        BOLL_PROG_BOLLA,
//                        SOGG_PROG,
// 
//                        CODICE_ALIENAZIONE,
//                        COGNOME,
//                        INDIRIZZO,
//                        TELEFONO,
//                        FAX
//*/"
//                        FROM E14SEL.E_V_BEIN_DETT o WHERE
//                        o.ORDI_PROT_ORDINE=$number AND o.ANNO_ORDI=$year");
//    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
//    //$output=tabelize($qy);
//
//    while ($row=oci_fetch_array($qy)){
//            if (!isset($header)){
//                foreach ($row as $key => $value) {
//                        if (!is_numeric($key)) $header[]=$key;
//                }
//            }
//            foreach ($row as $key => $value) {
//                if (!is_numeric($key))
//                    if ($key=='PROG_INTROITO')$rw[]='<span class="i_info" tipurl="tested/plug-in/ascott_lnk/ascott_beni_dett/'.$value.'">'.$value.'</span>';
//                    else $rw[]=$value;
//            }
//            $output[]=$rw;
//            unset($rw);
//    }
//
//
//    oci_close($conn);
    return theme('table',$header,$output);;
}

function ascott_beni_dett($arg){
    //$output='<table>';
//    $output='<table  style="font-size:80%; padding-top: 1px; padding-right: 1px; padding-bottom: 1px; padding-left: 1px;">';
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT *
//                        FROM E14SEL.E_V_BEIN_DETT  WHERE
//                        PROG_INTROITO=$arg[0]");
//    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
//    while ($row=oci_fetch_array($qy)){
//            $output.='<tr><th width="20%">Voce</th><th>Contenuto</th></tr>';
//            foreach ($row as $key => $value) {
//                if (!is_numeric($key))$output.='<tr><td>'.$key.':</td><td><b>'.$value.'</b></td><tr>';
//            }
//     }
//    oci_close($conn);
//     $output.='</table>';
//    echo $output;
//    exit(0) ;
}

function ascott_inventario($year,$introito){
    //$output='<table>';
//    $output='<table  style="font-size:80%; padding-top: 1px; padding-right: 1px; padding-bottom: 1px; padding-left: 1px;">';
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT *
//                        FROM E14SEL.E_V_BEIN_DETT  WHERE
//                        NUMERO_INTROITO=$introito AND ANNO=$year");
//    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
//    while ($row=oci_fetch_array($qy)){
//            $output.='<tr><th width="20%">Voce</th><th>Contenuto</th></tr>';
//            foreach ($row as $key => $value) {
//                if (!is_numeric($key))$output.='<tr><td>'.$key.':</td><td><b>'.$value.'</b></td><tr>';
//            }
//     }
//    oci_close($conn);
//     $output.='</table>';
//    return $output;
}

function ascott_schede($year,$introito){

    $conn = ascott_connect();
    $qy=oci_parse($conn,"SELECT T.DATA_SCHEDA,T.DESC_SCHEDA,T.VALORE_SCHEDA_E
                        FROM E14.E_SCHEDE_TECNICHE T
                        INNER JOIN E14SEL.E_V_BEIN_DETT B ON B.PROG_INTROITO=T.BEIN_PROG_INTROITO
                        WHERE
                        B.NUMERO_INTROITO=$introito AND B.ANNO=$year");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
    unset($rows);
    while ($row=oci_fetch_object($qy)){
            $rows[]=(array)$row;
     }
    oci_close($conn);
    if (isset($rows)) {
        return theme('table',array('data','descrizione accessorio', 'valore'),$rows);
    }
    else {
        return FALSE;
    }   
}

function check_dett_order($year,$number){
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT COUNT(*) AS dett FROM E14SEL.E_V_ORDI_DETT o WHERE
//                        o.PROT_ORDINE=$number AND o.ANNO=$year");
//    oci_define_by_name($qy, 'dett', $dett);
//    oci_execute($qy,OCI_DEFAULT);
//    oci_fetch($qy);
//    oci_close($conn);
    return 1;//$dett;
}
function ascott_dett_order($year,$number){
    $output='<table>';
    //db_set_active('ascott');
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT
//"./*            ANNO,
//            PROT_ORDINE,
//            SOGG_PROG,
// */"
//             DESC_CATALOGO,
//"./*            RAPP_CODI_RAPPRESENTANTE,
//            SEAC_CODI_SEZIONE,
//            SETT_CODI_SETTORE,
//*/"
//            DATA_ORDINE,
//"./*            PROG_LINEA_ORDINE,
//            CAEN_PROG_CATALOGO,
//*/"
//            COGNOME as Fornit,
//            UNITA_MISURA,
//"./*            PREZZO_LINEA,
// 
//            PREZZO_LINEA_EURO AS Prezzo,
//            ALIQUOTA_IVA as iva,
//*/"            QUAN_ORDINATA_TOTALE AS QTA,
//"./*            VALORE_IVATO,
// 
// */"           VALORE_IVATO_E as Prezzo,
//"./*
//            BUDG_CODI_BUDGET,
//            MAGA_CODI_MAGAZZINO,
//            SUBC_CODI_SUBCENTRO,
//            PROG_ARTMAG,
//            DESC_ARTMAG,
//            CLASS_ECO1,
//            CLASS_ECO2,
//            CLASS_ECO3,
//            CLASS_ECO4,
//            CLASS_ECO5,
//            CLASS_MERCEO,
//            SCLASS_MERCEO,
//            CATE_PATRIM,
//            SCATE_PATRIM,
//            CLASS_GEST,
//*/"
//           CONCAT(DESC_ARTI_FORNIT,
//            CODI_ARTI_FORNIT) AS ART_FORNIT,
//            NOTA
//            FROM E14SEL.E_V_ORDI_DETT o WHERE
//                        o.PROT_ORDINE=$number AND o.ANNO=$year");
//     oci_execute($qy,OCI_DEFAULT); //db_query($qy);
//     $output=tabelize($qy);
//     oci_close($conn);
     return $output;
}


/**
 * verifica se ci sono nuovi acquisti inventariati
 * @param <boolean> $all "true" verifica su tutte le voci collaudate senza invetariazione
 * "false" non controlla quelle conl'inventario "0"
 * @return <array>  key=Node ID (nid) value= array
 *                     'year' anno ordine
                       'prot_ordine' protocollo ordine
                       'description' descrizione collaudo
                       'items_cheked' numero di beni in collaudo
                       'items_found'  numero di beni inventariati
 *
 */
function check_new_ascott_items($all=FALSE){
    // conta le voci mancanti per ogni ordine
//    $qy1="SELECT   p.n_order AS prot_ordine,
//                  YEAR(p.`data_ordine`) as year,
//                  count(p.numero_ordine) AS items_count,
//                  p.sistema_apparecchiatura as description, p.nid as nid
//         FROM principale_di_collaudo p
//         INNER JOIN apparecchiature_per_ordine a
//            ON p.numero_ordine=a.numero_ordine
//         WHERE (NOT p.`data_ordine` IS NULL)
//                AND (a.inventario_aziendale IS NULL ".($all===TRUE ?"OR a.inventario_aziendale=0":"").")
//                AND p.n_order <>0 AND NOT p.n_order IS NULL
//         GROUP BY a.numero_ordine";
//    $res=db_query($qy1);
//    $conn = ascott_connect();
//    $qy=oci_parse($conn,"SELECT
//                            COUNT(PROG_INTROITO) as items_count,
//                            ANNO_ORDI,
//                            ORDI_PROT_ORDINE
//                            FROM E14SEL.E_V_BEIN_DETT
//                            WHERE
//                            ORDI_PROT_ORDINE=:prot_ordine
//                                AND ANNO_ORDI=:year
//                            GROUP BY ORDI_PROT_ORDINE, ANNO_ORDI");
//    $new_items=array();
//    while ($row=db_fetch_object($res)){
//
//        oci_bind_by_name($qy,":prot_ordine",$row->prot_ordine);
//        oci_bind_by_name($qy,":year",$row->year);
//        if (is_null($row->prot_ordine) || is_null($row->year)) continue;
//        set_time_limit(30);// allunga il tempo di vita della funzione sotto che è lenta.
//        if (!oci_execute($qy,OCI_DEFAULT)) print $row->prot_ordine;
//        while ($rw=oci_fetch_array($qy)){
//               if ($rw['items_count']<=$row->items_count){
//                   $new_items[$row->nid]=array(
//                       'year'=>$row->year,
//                       'prot_ordine'=>$row->prot_ordine,
//                       'description'=>$row->description,
//                       'items_cheked'=>$row->items_count,
//                       'items_found'=>$rw['items_count']
//                   );
//               }
//        }
//    }
//    oci_close($conn);
    return $new_items;
}

/* recupera la tabella delle nuove  inventariazioni per il singolo ordine */
function ascott_get_new_items($year,$n_order,$exclude_items='') {
////    $conn = ascott_connect();
////    $new_items=array();
////    if (is_array($exclude_items) && ! isset($exclude_items))
////        $exclude_items =" AND NOT inventario IN (".implode($exclude_items,',').")";
////    else $exclude_items='';
////    $qy="SELECT
////            PROG_INTROITO,
////            ANNO, NUMERO_INTROITO,
////            MATRICOLA_BENE ,
////            DESC_CATALOGO  ,
////            DESC_INTROITO
////        FROM E14SEL.E_V_BEIN_DETT
////        WHERE ORDI_PROT_ORDINE=$n_order
////            AND ANNO_ORDI=$year ".$exclude_items;
//////    print $qy;
////    $qy=oci_parse($conn,$qy );
////    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
////    while ($rw=oci_fetch_array($qy)){
////               $new_items[$rw['PROG_INTROITO']]=array(
////                   'matricola'=>$rw['MATRICOLA_BENE'],
////                   'descrizione'=>$rw['DESC_CATALOGO'],
////                   'introito'=>$rw['DESC_INTROITO'],
////                   'inventario'=>$rw['ANNO'].'/'.$rw['NUMERO_INTROITO'],
////               );
////
////    }
//    oci_close($conn);
    return $new_items;
}



function update_items_form($node){
//    include_once 'risk-analyzer.php';
//    $qy="SELECT inventario_aziendale FROM "._ITEMS_COLLAUDI." WHERE numero_ordine=%d
//        AND NOT inventario_aziendale IS NULL";
//    $res=db_query($qy,$node->numero_ordine);
//    while($row=db_fetch_array($res)){
//        $excluded_items[]=$row->inventario_aziendale;
//    }
//    $new=ascott_get_new_items(date_format(date_create($node->data_ordine), 'Y'),$node->n_order,$excluded_items);
//    //composizione della laista per i select
//    $new_items[0]='(seleziona)';
//    foreach ($new as $item)
//        $new_items[$item['inventario']]='matr.:'.$item['matricola'].' inv.:'.$item['inventario'];
//
//    $qy="SELECT * FROM "._ITEMS_COLLAUDI." WHERE numero_ordine=%d";
//    $res=db_query($qy,$node->numero_ordine);
//    $first=TRUE;
//    while($row=db_fetch_array($res)){
//        $rw=array();
//        foreach ($row as $lab => $val){
//            if ( in_array($lab, array('ID','TIMESTAMP','numero_ordine','main_nid'))) continue;
//            if ($first) $header[]=$lab=='nid'?'richiesta':$lab;
//
//            if ($lab=='etichetta_manutentore'){
//                $rw[]=l($val,"tested/si3c/1004/$val");
//                if (isset($val) && $val!=NULL)  
//                    update_one_item($val,FALSE);//aggiorna la tabella dei rischi per una sola voce
//            }
//
//            else if ($lab=='nid')
//                $rw[]=nl($val);
//
//            else if ($lab=='inventario_aziendale') {
//                if ($val===NULL){
// //                   $select_new[$row['ID']]=array(
// //                       '#type'=>'select',
// //                       '#options'=>$new_items,
// //                       '#default_value'=>0,
// //                   );
// //                   $rw[]=drupal_render($select_new[$row['ID']]);
// //
//                    $rw[]=drupal_get_form('item_id_set_form',$row['ID'],$new_items);
//                }
//                else $rw[]=$val;
//            }
//            else $rw[]=$val;
//        }
//        $first=false;
//        $rows[]=$rw;
//    }
//    return theme('table',$header,$rows);
}


/*
// test function
function update_items_form($node){

    
    //composizione della laista per i select
    $new_items[0]='(seleziona)';
    $new_items['2011/1111002']='matr.:45ffr inv.:2011/1111002';
    $new_items['2011/3333433']='matr.:ASSSDE34 inv.:2011/3333433';
    $new_items['2011/5555555']='matr.:5555555 inv.:2011/5555555';
    $new_items['2011/5437890']='matr.:5437890 inv.:2011/5437890';

    $qy="SELECT * FROM "._ITEMS_COLLAUDI." WHERE numero_ordine=%d";
    $res=db_query($qy,$node->numero_ordine);
    $first=TRUE;
    while($row=db_fetch_array($res)){
        $rw=array();
        foreach ($row as $lab => $val){
            if ( in_array($lab, array('ID','TIMESTAMP','numero_ordine','main_nid'))) continue;
            if ($first) $header[]=$lab=='nid'?'richiesta':$lab;

            if ($lab=='etichetta_manutentore')
                $rw[]=l($val,"tested/si3c/1004/$val");

            else if ($lab=='nid')
                $rw[]=nl($val);

            else if ($lab=='inventario_aziendale') {
                if ($val===NULL){
 //                   $select_new[$row['ID']]=array(
 //                       '#type'=>'select',
 //                       '#options'=>$new_items,
 //                       '#default_value'=>0,
 //                   );
 //                   $rw[]=drupal_render($select_new[$row['ID']]);
 //
                    $rw[]=drupal_get_form('item_id_set_form',$row['ID'],$new_items);
                }
                else $rw[]=$val;
            }
            else $rw[]=$val;
        }
        $first=false;
        $rows[]=$rw;
    }
    return theme('table',$header,$rows);
}
*/


function item_id_set_form(&$form_state, $item, $id_list){
    $form['inline']=array('#prefix' => '<div class="container-inline">', '#suffix' => '</div>');

    $form['inline']['select_new']=array(
                '#type'=>'select',
                '#options'=>$id_list,
                '#default_value'=>0,
            );
    $form['inline']['hidden_id']=array(
        '#type'=>'hidden',
        '#value'=>$item
    );
    $form['inline']['button']=array(
        '#type'=>'submit',
        '#value'=>'OK',
        '#submit'=>array('item_id_set'),
        '#access'=>  user_access(_MANGE_TESTED),
    );
    return $form;
}

function item_id_set($form,&$form_state){
    $op=$form_state['values']['op'];
    $values=$form['#post'];
    if($op='OK' && $values['select_new']!=0){
        db_query("UPDATE "._ITEMS_COLLAUDI." SET inventario_aziendale='%s'
                  WHERE ID=%d", $values['select_new'],$values['hidden_id']);
    }
    return ;
}