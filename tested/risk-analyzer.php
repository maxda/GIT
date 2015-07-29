<?php

/*
 * libreria per il calcolo del rischio
 * 
 */

define('_RISK_LAST_UPDATE','risk_last_update');// nome contatore ultimo aggiornamento
define('_RISK_MAX_UPDATE','risk_max_update');  // variabile con il numero massimo di record da aggiornare
define('_RISK_UPDATE_DEFAULT',1000);
define('_LAST_COMPLETE_UPDATE','last_complete_update'); //data dell'ultimo aggiornametno completo
define('_LAST_UPDATED_ITEM_COUNT','last_updated_item_count'); // contatore aggiornato all'ultimo update completo
define('_RISK_SET','risk_management_set'); // flag inizializzazione dati 
ini_set('max_execution_time', 300);


/**
 * funzione per caricare le tabelle ausiliarie di calcolo e memorizzazione degli indici di rischio
 */
function schemaLoader() {
$schema['items_risk'] = array(
        'description' => 'tabella che raccoglie gli indici di rischio calcolati',
        'fields' => array(
            'id' => array('description' => 'chiave di riferimento', 'type' => 'serial', 'not null' => TRUE,),
            'description' => array('description' => 'descrizione item', 'type' => 'varchar', 'length' => 255,'not null' => FALSE,'default'=>'noname'),
            'inventario' => array('description' => 'inventario', 'type' => 'varchar', 'length' => 100,'not null' => FALSE,),
            'service_id' => array('description' => 'inventario manutentore', 'type' => 'varchar','length' => 100, 'not null' => FALSE),
            'service_label' => array('description' => 'ettichetta', 'type' => 'varchar','length' => 100, 'not null' => FALSE, 'default'=>'no label'),
            'maintenance_count' =>array('description' => t('number of maintenances'), 'type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => 0),
            'MTTR' =>array('description' => t('MTTR'), 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'stdev_MTTR' =>array('description' => t('standard deviation maintenance duration'), 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'MTBF' =>array('description' => 'M.T.B.F.', 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'MTTF' =>array('description' => 'M.T.T.F.', 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'stdev_MTBF' =>array('description' => 'Standar deviation M.T.B.F.', 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'stdev_MTTF' =>array('description' => 'Standar deviation M.T.T.F.', 'type' => 'float', 'not null' => TRUE, 'default' => 0),
            'data'=>array('description' => 'rilevation data', 'type' => 'text', 'size'=>'normal', 'default' => ''),
            'system_count' => array('description' => t('number of system parts'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'redundancy' => array('description' => t('number of same models'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'age' => array('description' => t('year of installation'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'class_id' => array('description' => 'id classe civab', 'type' => 'varchar', 'length' => 10,  'not null' => TRUE ),
            'struct_id' => array('description' => 'id struttura, corrisponde alla tabella delle strutture', 'type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => 0),
            'room_id' => array('description' => 'id room', 'type' => 'varchar', 'length' => 20,  'not null' => TRUE ),
            'hosp_id' => array('description' => 'id hospital', 'type' => 'varchar', 'length' => 20,  'not null' => TRUE ),
            'nid' => array('description' => t('node id referece to other node'), 'type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => 0),
            'risk' => array('description' => t('risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'updated' => array('description' => t('last update'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'TIMESTAMP' => array('description' => t('campo per compatibilità access'), 'type' => 'datetime', 'not null' => TRUE,'default'=>0),
        ),
        'primary key' => array('ID'),
        'indexes' => array(
            'inventario' => array('inventario'),
            'class_idx' => array('class_id'),
            'struct_idx' => array('struct_id'),
            'service_idx' => array('service_id'),
            'service_lbl' => array('service_label'),
            'room_idx' => array('room_id','hosp_id'),
            'nid' => array('nid'),
            'risk' => array('risk'),
            'updated' => array('updated'),
        ),
    );
$schema['struct_risk'] = array(
        'description' => 'tabella con indicatori di rischio di reparto',
        'fields' => array(
            'struct_id' => array('description' => 'id struttura, corrisponde alla tabella delle strutture', 'type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => 0),
            'risk' => array('description' => t('risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'updated' => array('description' => t('last update'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'TIMESTAMP' => array('description' => t('campo per compatibilità access'), 'type' => 'datetime', 'not null' => TRUE, 'default'=>0),
        ),
        'primary key' => array('struct_id'),
        'indexes' => array(     
            'risk' => array('risk'),
            'updated' => array('updated'),
        ),
    );
$schema['class_risk'] = array(
        'description' => 'tabella con indicatori di rischio per classe',
        'fields' => array(
            'class_id' => array('description' => 'id classe civab', 'type' => 'varchar', 'length' => 10,  'not null' => TRUE ),
            'patient_risk' => array('description' => t('patient risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'function_risk' => array('description' => t('function risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'user_risk' => array('description' => t('user risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'updated' => array('description' => t('last update'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'TIMESTAMP' => array('description' => t('campo per compatibilità access'), 'type' => 'datetime', 'not null' => TRUE, 'default'=>0),
        ),
        'primary key' => array('class_id'),
        'indexes' => array(     
            'patient_risk' => array('patient_risk'),
            'function_risk' => array('function_risk'),
            'user_risk' => array('user_risk'),
            'updated' => array('updated'),
        ),
    );

$schema['room_risk'] = array(
        'description' => 'tabella con indicatori di rischio per i locali',
        'fields' => array(
            'room_id' => array('description' => 'id room', 'type' => 'varchar', 'length' => 20,  'not null' => TRUE ),
            'hosp_id' => array('description' => 'id hospital', 'type' => 'varchar', 'length' => 20,  'not null' => TRUE ),
            'name' => array('description' => t('room name'), 'type' => 'varchar', 'length' => 255,  'not null' => TRUE, 'default' =>'no name' ),
            'coordinates'=> array('description' => t('map reference'), 'type' => 'varchar', 'length' => 255, 'default' =>'' ),
            'risk' => array('description' => t('room risk index'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'el_group' => array('description' => t('room group class'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'updated' => array('description' => t('last update'), 'type' => 'int', 'not null' => FALSE, 'default'=>0),
            'TIMESTAMP' => array('description' => t('campo per compatibilità access'), 'type' => 'datetime', 'not null' => TRUE, 'default'=>0),
        ),
        'primary key' => array('room_id','hosp_id'),
        'indexes' => array(     
            'risk' => array('risk'),
            'el_group' => array('el_group'),
            'updated' => array('updated'),
            'coordinates'=>array('coordinates')
        ),
    );
    return $schema;
}

/**
 * inizializzazione le tabelle di rischio
 * 
 */
function reset_struct_risk($delete_all=FALSE){
    //valori di default
    $default=array(
        1 => 1 ,
        100 => 0 ,
        101 => 0 ,
        17 => 1 ,
        102 => 1 ,
        6 => 0 ,
        601 => 0 ,
        600 => 0 ,
        603 => 1 ,
        602 => 1 ,
        1301 => 0 ,
        8 => 0 ,
        801 => 0 ,
        800 => 0 ,
        1302 => 0 ,
        802 => 1 ,
        9 => 0 ,
        900 => 0 ,
        901 => 1 ,
        18 => 1 ,
        26 => 1 ,
        1500 => 1 ,
        1501 => 1 ,
        902 => 0 ,
        903 => 0 ,
        904 => 0 ,
        2 => 0 ,
        200 => 0 ,
        201 => 0 ,
        19 => 1 ,
        20 => 1 ,
        25 => 1 ,
        28 => 1 ,
        203 => 0 ,
        204 => 0 ,
        10 => 0 ,
        1004 => 0 ,
        1000 => 0 ,
        1003 => 0 ,
        1001 => 0 ,
        1002 => 0 ,
        38 => 0 ,
        1686 => 0 ,
        13 => 0 ,
        27 => 0 ,
        29 => 0 ,
        1300 => 0 ,
        5 => 0 ,
        24 => 1 ,
        30 => 1 ,
        502 => 1 ,
        35 => 0 ,
        503 => 0 ,
        504 => 0 ,
        505 => 1 ,
        509 => 1 ,
        506 => 0 ,
        508 => 0 ,
        3 => 1 ,
        300 => 0 ,
        60 => 0 ,
        34 => 0 ,
        36 => 0 ,
        33 => 0 ,
        301 => 1 ,
        302 => 1 ,
        303 => 1 ,
        304 => 1 ,
        4 => 0 ,
        21 => 1 ,
        23 => 1 ,
        31 => 1 ,
        22 => 0 ,
        500 => 1 ,
        507 => 0 ,
        16 => 0 ,
        202 => 0 ,
        1601 => 0 ,
        205 => 0 ,
        1600 => 0 ,
        14 => 0 ,
        1400 => 0 ,
        1005 => 0 ,
        1401 => 0 ,
        1101 => 0 ,
        1688 => 0 ,
        1100 => 1 ,
        1687 => 0 ,
        1102 => 1 ,
        37 => 0 ,
        1692 => 0 ,
        1694 => 0 ,
        1693 => 0 ,
        11 => 0 ,
        1695 => 0 ,
        1700 => 0 ,
        1690 => 0 ,
        1699 => 0 ,
        40 => 0 ,
        1698 => 0 ,
        40 => 0 ,
        1701 => 0 ,
        1697 => 0 ,
        1702 => 0 ,
        1691 => 0 ,
    );
    if($delete_all) db_query("DELETE FROM struct_risk");
    $qy="SELECT ID FROM "._STRUCT_TABLE." WHERE replaced=0";
    $qy=db_query($qy);
    while ($rw=  db_fetch_object($qy)){
        if(key_exists($rw->ID, $default)){
            db_query("INSERT INTO struct_risk (struct_id, risk, updated) VALUES ($rw->ID, ". $default[$rw->ID].", ". time() .")" );
        }
        else {
            db_query("INSERT INTO struct_risk (struct_id, risk, updated) VALUES ($rw->ID, 0, ". time() .")" );
        }
    }
}
function reset_class_risk($delete_all=FALSE){
        if($delete_all) db_query("DELETE FROM class_risk");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4AT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ABA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ATA', 1, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ALI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1AC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('7AC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ARR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ADM', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AER', 1, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AFL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AGS', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AGM', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ALA', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AIM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ACZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AFT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AMA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AMO', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6MA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SNA', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1AP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ADH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AND', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ANM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASF', 1, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AAS', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AAC', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AAE', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AIC', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAG', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('APM', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SNN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AMD', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ADP', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AEN', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AFC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AGC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AGR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AIE', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AMR', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AME', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('NEM', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AOS', 3, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AOD', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AEC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASD', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASU', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AUR', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AVP', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AEO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ANS', 5, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ADG', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ANG', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASN', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ACA', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RAD', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('081', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ADI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ARE', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ASC', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AFU', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ACH', 4, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ABS', 3, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AIB', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AEG', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AUM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AUT', 5, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AUB', 5, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ATT', 5, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BUL', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BTE', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LV', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('QBA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5BB', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5BL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('7BP', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BAE', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5BN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BPN', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5BA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BPS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BPR', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BTL', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BIM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BIF', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BIO', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AUL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BLT', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4BO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BRR', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BRS', 2, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAM', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CMU', 1, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ACM', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAS', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CIR', 3, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CBI', 3, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CSF', 4, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PCE', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CTF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CA9', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6CA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5CR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4CL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAF', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5RC', 1, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CTO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5CC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FSE', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CTV', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CEF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CMO', 5, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CEN', 1, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CRE', 1, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CEM', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CIN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CEC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CIC', 2, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CFM', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1CM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CLM', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('COM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CTE', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('COS', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CLS', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAT', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CPS', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CRA', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('7CO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('COU', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CMI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('COO', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CON', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4XX', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CLA', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CDG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CTA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CDA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAV', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CAR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1CC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TCR', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CGA', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CGD', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CGS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CPB', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CRG', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CCE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CCR', 1, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CPA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CPU', 5, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CRC', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CRI', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AED', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CFL', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DEF', 5, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DTM', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DEO', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DEP', 2, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ALF', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DAC', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DEE', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DIA', 2, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DMA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DPR', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DOF', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DIC', 3, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DIL', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MVA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DOS', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DSI', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DAZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DUS', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ECO', 3, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ECT', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ECL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ESF', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EBI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ELA', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ELB', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ECG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('3ER', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EEG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EAU', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EFC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ELF', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EME', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EMG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ENG', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ESK', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ELT', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EMD', 5, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EMO', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EGA', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EGP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EAC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EMM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EOM', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EVM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('CIS', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EGS', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ESS', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EAT', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ST8', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EAN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ESC', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EAG', 4, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EVA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FAC', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BT5', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FAS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5FC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FIB', 5, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FAZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FAN', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FLM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FLS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FGA', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FON', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FLU', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FTR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4FM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FGR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FOC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FTC', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FLA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AFO', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FOM', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FFI', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FOS', 2, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FAH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FOT', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FRE', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FBI', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4FD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('FRF', 1, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4FL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GCC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GMO', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GCA', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GCG', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GFL', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GUT', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GAF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GLI', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('AGL', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6AM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6GC', 5, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('GRD', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ABD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IDG', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IMP', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IMM', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IID', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IAP', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('INC', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IAC', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('INN', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('INT', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IAG', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IRM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IGA', 4, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IIM', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1IN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IOF', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ITM', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IRA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IRR', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IAL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ISS', 1, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('IST', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LPE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LFE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAF', 1, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5TE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAI', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LDB', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LFR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VLS', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LUV', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LIR', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LUI', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LSC', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAS', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LRS', 1, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LSS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LCH', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LTE', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BT7', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LDX', 4, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LMD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LGR', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SLP', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LP', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LCC', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LFS', 4, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('2LV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAV', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LBD', 3, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LA', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5LD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5EL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LTT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LRI', 4, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LTR', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1CD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LMB', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('3CD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LHO', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LIC', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LIO', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LIE', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LIT', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MAT', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MAG', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5MA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MMM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MPR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MAC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MST', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MET', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MLL', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1MR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PDI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MBD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MEL', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MOP', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MOL', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MSS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MCT', 1, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MAM', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MI1', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MLS', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MSA', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LEP', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MPE', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MPC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MGC', 5, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MDC', 5, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MCD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MIX', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MDE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MHP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ERA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MON', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MFE', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MFC', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MOA', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1SM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('7MV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MVN', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MTV', 2, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MTR', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ARI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MOV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MUF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('NUL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('NUP', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OAD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OFM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OFS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OMO', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OPM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ORG', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OSM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OTS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OZO', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAH', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PFT', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAB', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PNM', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PSO', 3, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PSC', 3, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PEI', 3, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1PM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PHM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PRT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PRP', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PST', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PSV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PIP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6PP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PLG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PCO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PTG', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PDS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PEH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('POM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('POG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('POS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('POO', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PPT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5PL', 4, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PSI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BT3', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PIN', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PEP', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PHP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PPE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PSA', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('4PL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PRA', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PRD', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('APE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAD', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAP', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PTP', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PRR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PLE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PDG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAU', 4, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('OOR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('REN', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RAT', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BT1', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RHP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RHO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RHE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RHG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RCA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('REG', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PPC', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RMA', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RTS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RCL', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LHP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RAH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RMM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1RC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RPR', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIL', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RSA', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RAL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RAF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RRN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RDE', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIU', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RIO', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RDB', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1RO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('RXD', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SSH', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('BT2', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCS', 3, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SDH', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCN', 3, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1SN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SHL', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAI', 1, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SOR', 3, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SPR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCE', 4, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SEG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SMM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SRT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SNT', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('LAD', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('EC9', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SDN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SET', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SPD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SRD', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SBC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SMG', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('STE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('STM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SM9', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAH', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SFM', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SFU', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SSA', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAA', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SMA', 1, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SPM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SSE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SPS', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1SC', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SVE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SCL', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SPA', 4, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAG', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('MRP', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('STC', 4, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAS', 3, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SEZ', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SOE', 1, 0, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('STT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAU', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SEC', 2, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SEI', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SFA', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SMN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SNE', 2, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SNM', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAP', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SAE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SES', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('SVP', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TAR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOP', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TAA', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('5TR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TPA', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TRI', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TTE', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TTG', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TVC', 3, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TLC', 3, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('UTC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('UTE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TRG', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1TV', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOS', 4, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DMO', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DOC', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('DUL', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TGR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TME', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TEM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('6TE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TCP', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TMS', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TCO', 2, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TLA', 1, 0, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TTO', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOG', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TRM', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TAC', 2, 3, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOM', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TCC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TIL', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TCN', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ACI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TDE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOR', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TOT', 4, 4, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TNE', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TAM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TSI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAX', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('PAT', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TRO', 3, 2, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TEG', 3, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('TUM', 1, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('ULC', 1, 1, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('UMI', 3, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1UC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1UD', 2, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1US', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('INF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('UNS', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('URS', 4, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('URD', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('URF', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VCT', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VFV', 2, 1, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VAP', 3, 4, 2, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VES', 2, 1, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VPO', 5, 4, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VCG', 3, 2, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VBM', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VBR', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VCL', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VAC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VDU', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VGF', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1VR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1VI', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VIR', 1, 0, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('1VT', 2, 3, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VVC', 2, 3, 0, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('VTC', 4, 2, 1, ".time().");");
        db_query("INSERT INTO  class_risk ( class_id,patient_risk,function_risk,user_risk,updated) VALUES ('WSD', 2, 3, 0, ".time().");");    
}

function reset_room_risk($delete_all=FALSE) {
    
        if($delete_all) db_query("DELETE FROM room_risk;");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GERVBIO', 'POLU', 0, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TOSSICO', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD2', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD10', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD3', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD1', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD4', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD5', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD6', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD7', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD8', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TRASGE', 'GMNA', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TRASPALM', 'PLMV', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TRASDAN', 'SDFR', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TRASLAT', 'LTSN', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TRASTOL', 'TLMZ', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SIC', 'GEMO', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PT-KOLB', 'KOLB', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_RIMM', 'KOLB', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('UROLOG.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DERMAT.A', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTOIAT.F', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ELIAMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_MED', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_PETR', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_SCRO', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_PENS', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANAT', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIOL', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEUR', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MIOG', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FARGIA', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDINT', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ENDO', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SALEGIN', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DAYEMA', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('EMATOL', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('EMA_LAB', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GIN_AMB', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PART', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMB_OSTE', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIMAX', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FARMACI', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTORI', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PDIATR', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OCUL', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORTOP', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SO', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SO3', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SO4', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('SO5', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RIANIM', 'POLU', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIR', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MAXDEG', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DAYINF', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('LAB', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_PENN', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PIASTRAM', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FARLAB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PENSP1', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_AOR', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_AUR', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FISIOT', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEURAD', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADSAN', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('UROLOG.W', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FRIGO', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('INCEND', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CLIREUM', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('REUMA', 'KOLB', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CLIINF', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('EMATO', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('B118', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD15', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DERMA', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('LAB_AN', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEON', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORT_GESS', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PENSIO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FISIO_1P', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GENETIC', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIRAMBU', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TAC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MAGAZ', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORLSOP', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIRSERV', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RAD1ECO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMB_CMAX', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMB_OTOR', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CORPOD1', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CORPOD3', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FARDISTR', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('LABANPS', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEURO.F', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DAYSURS', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANAPMAGL', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PIASANES', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_DEUR', 'GEMO', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_DERM', 'GEMO', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_NEUG', 'GEMO', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_AP_INC', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_OS_SP2', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_SODE', 'GEMO', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_RADI', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_RA_DI3', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_RA_DI1', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_RA_DI2', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_CMAX', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_MX_AMB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_IGIE', 'IGIE', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_GENE1', 'KOLB', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_GENET', 'KOLB', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DIABET.C', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CARDIO.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIPLA.A', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIPLA.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIVA.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DERMAT.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FISRES.S', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDICAMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDNUC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDURG.1', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEUROC.M', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEUSPIN', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORTOPE.M', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTOIAT.M', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTOIAT.W', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PROSOC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIOL2', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('STOMAT', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('EMERG', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PRSO', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OCUL', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIO', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GINE', 'PLMV', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GROP', 'PLMV', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDE', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GIPAR', 'PLMV', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CARD', 'PLMV', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANEST', 'LTSN', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GROP', 'LTSN', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GISP', 'LTSN', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIO', 'LTSN', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('EMZA', 'LTSN', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMBRIA', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEON_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PED_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMB3PT', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ENDOCR.D', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GAST_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('LABMIC', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD9', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD4POL', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEURO', 'POLU', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CARDEMOD', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PADCSL', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PALL', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANAL2?PI', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_MICROB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_ANAPAT', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PADCSL_P', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_ANAT', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_FARGIA', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('C_EMATOL', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_FARMA', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_LABEL', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_MALRAR', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DIRSAN', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_GENET', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_TRASF', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MICROB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_IPAT', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('VIROL', 'IGIE', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PAD_16', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_RADURG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_MEDLAB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CORPOP1', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIOTER', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_ISTGEN', 'POLU', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_GROP2', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('N_GROP1', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CORPOP3', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CORPOP5', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO1', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO2', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO3', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO4', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO5', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO6', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO7', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('S_ENDO8', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIOL1', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RNM', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MEDURG.2', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIOT', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_PS', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_DH', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_LAB', 'CIVD', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_CTR', 'CIVD', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RAD', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RMA', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RD1', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RD2', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RD3', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_RD4', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_REC', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_MED', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_CAR', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_GO', 'CIVD', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_SE', 'CIVD', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CIV_ACH', 'CIVD', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMBZ', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANAT_PAT', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANES1', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ANES2', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CARD_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CARD_EMO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CCH_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CCH_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CCHCRPO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHI1_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHI1_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHI2_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CMAX_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CMAX_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CMAX_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CPLA_UST', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CVAS_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DIABET', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DIAL', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('DIETI', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ELIS', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FARMA', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FIS_RES', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FIS_SAN', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('GAST', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('IMM_LAB', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('IMM_PREL', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MED_DAY', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MED_NUC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MED_URG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MED1_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MED2_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('MTS', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NCHI_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEFRO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEU_DH', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEU_RAD', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEU_SK', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NFPT', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NIDO_CO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OCU_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OCU_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ONC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ONC_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ONC_DH', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORT_AMB', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORT_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORT_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTO_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTO_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PNE_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('PSOC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RAD1', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RAD2', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADIO_IS', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RADTER', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('RMN', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('STE_CEN', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('URO_DEG', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('URO_SO', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('UTIC', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('IMMSOTTE', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('P_NEUR', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIRU1W', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIRUR2W', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('CHIVA.A', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('NEUROC.W', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('ORTOPE.A', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('OTOIAT.A', 'SMMM', 3, 2, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('LABSIC', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AUDIO', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('AMBGIN', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('FORMAZIO', 'SMMM', 1, 0, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('TER_ANT', 'SMMM', 2, 1, ".time().");");
//        db_query("INSERT INTO  room_risk( room_id, hosp_id,risk,el_group,updated) VALUES ('IMM_AEM', 'SMMM', 1, 0, ".time().");");
  
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('POLU','GERVBIO','ASS4 GERVASUTTA PALESTRA BIOMECCANICA',0,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TOSSICO','SMMM','LABORATORIO ANALISI TOSSICOLOGIA - PIANO SOTTERRANEO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD2','SMMM','PADIGLIONE 2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD10','SMMM','PADIGLIONE 10',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD3','SMMM','PADIGLIONE 3',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD1','SMMM','PADIGLIONE 1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD4','SMMM','PADIGLIONE 4',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD5','SMMM','PADIGLIONE 5',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD6','SMMM','PADIGLIONE 6',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD7','SMMM','PADIGLIONE 7',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD8','SMMM','PADIGLIONE 8',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TRASGE','GMNA','IMMUNOTRASFUSIONALE AREA VASTA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TRASPALM','PLMV','IMMUNOTRASFUSIONALE AREA VASTA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TRASDAN','SDFR','IMMUNOTRASFUSIONALE AREA VASTA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TRASLAT','LTSN','IMMUNOTRASFUSIONALE AREA VASTA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TRASTOL','TLMZ','IMMUNOTRASFUSIONALE AREA VASTA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SIC','GEMO','SERVIZIO INGEGNERIA CLINICA ITAL TBS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PT-KOLB','KOLB','PIANO TERRA EDIFICIO DELLA PATOLOGIA GENERALE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_RIMM','KOLB','PATOLOGIA GENERALE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('UROLOG.D','SMMM','UROLOGIA AMBULATORIO/DAY HOSPITAL - P5',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DERMAT.A','SMMM','PIASTRA AMBULATORIO N? 28 DERMATOLOGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTOIAT.F','SMMM','PIASTRA AMBULATORIO N? 30 CHIRURGIA VASCOLARE',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ELIAMB','SMMM','PIASTRA AMBULATORIO N?31 CHIRURGIA VASCOLARE',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_MED','POLU','PADIGLIONE N? 8',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_PETR','POLU','PADIGLIONE N? 7',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_SCRO','POLU','PADIGLIONE N? 9',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_PENS','POLU','PADIGLIONE N? 6',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANAT','POLU','ANATOMIA PATOLOGICA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIOL','POLU','RADIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEUR','POLU','NEUROLOGIA AMBULATORIO - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MIOG','POLU','ELETTROMIOGRAFIA AMBULATORIO - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FARGIA','POLU','FARMACOLOGIA - PRIMO PIANO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDINT','POLU','MEDICINA INTERNA - PRIMO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ENDO','POLU','ENDOSCOPIA DIGESTIVA - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SALEGIN','POLU','OSTETRICIA/GINECOLOGIA SALE OPERAT. - OTTAVO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DAYEMA','POLU','EMATOLOGIA DAY HOSPITAL - QUARTO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('EMATOL','POLU','EMATOLOGIA DEGENZE- QUINTO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('EMA_LAB','POLU','EMATOLOGIA LABORATORIO - QUARTO PIANO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GIN_AMB','POLU','GINECOLOGIA AMBULATORI / DEGENZE - SESTO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PART','POLU','SALE PARTO - SETTIMO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMB_OSTE','POLU','OSTETRICIA AMBULATORI / DEGENZE - SETTIMO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIMAX','POLU','CHIRURGIA MAXILLO FACCIALE / DENTISTI - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FARMACI','POLU','FARMACIA - PIANO TERRA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTORI','POLU','OTORINOLARINGOIATRIA AMBULATORI - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PDIATR','POLU','PEDIATRIA - PRIMO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OCUL','POLU','OCULISTICA AMBULATORI - SECONDO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORTOP','POLU','ORTOPEDIA AMBULATORI -  PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SO','POLU','GRUPPO OPERATORIO - SECONDO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SO3','POLU','PADIGLIONE N? 13',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SO4','POLU','GRUPPO OPERATORIO LABORATORIO DI CHIRURGIA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('SO5','POLU','CHIRURGIA LABORATORIO PAD. SCROSOPPI - PRIMO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RIANIM','POLU','RIANIMAZIONE - SECONDO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIR','POLU','CHIRURGIA DEGENZE / AMBULATORI - TERZO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MAXDEG','POLU','MAXILLO / OCULISTICA / ORTOPEDIA DEGENZE - TERZO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DAYINF','POLU','PADIGLIONE N? 1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('LAB','POLU','LABORATORIO ANALISI SCROSOPPI - PIANO INTERRATO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_PENN','POLU','PADIGLIONE N? 3',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PIASTRAM','SMMM','IMMUNOTRASFUSIONALE AMBULATORIO N? 39 TERAPIE INFUSIONALI - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FARLAB','SMMM','FARMACIA INTERRATO - PI',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PENSP1','SMMM','FARMACIA VIA BIELLA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_AOR','CIVD','CIVIDALE PIASTRA AMBULATORIALE AMB. N? 2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_AUR','CIVD','CIVIDALE PIASTRA AMBULATORIALE AMB. N? 3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FISIOT','SMMM','ASS 4 MEDIO FRIULI FISIOTERAPIA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEURAD','SMMM','NEURORADIOLOGIA PRIMO PIANO - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADSAN','SMMM','RADIOLOGIA MEDICHE - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('UROLOG.W','SMMM','UROLOGIA STANZA LITOTRITORE - P6',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FRIGO','POLU','STANZA FRIGO PIANO INTERRATO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('INCEND','POLU','AMBULATORI PIANO TERRA  PADIGLIONE SCROSOPPI',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CLIREUM','POLU','CLINICA REUMATOLOGICA - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('REUMA','KOLB','LABORATORIO DI REUMATOLOGIA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CLIINF','POLU','CLINICA MALATTIE INFETTIVE - SECONDO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('EMATO','POLU','EMATOLOGIA SCROSOPPI - PRIMO PIANO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('B118','SMMM','PADIGLIONE 15',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD15','SMMM','PADIGLIONE 12',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DERMA','SMMM','DERMATOLOGIA/ENDOCRINOLOGIA/DIABETOLOGIA/NUTRIZIONE CLINICA/ AMBULATORI E DAY HOSPITAL - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('LAB_AN','SMMM','LABORATORIO ANALISI - PIANO SOTTERRANEO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEON','SMMM','NEONATOLOGIA - P1',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORT_GESS','SMMM','MEDICINA D\'URGENZA AMBULATORIO N? 1 - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PENSIO','SMMM','PENSIONANTI DEGENZE - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FISIO_1P','SMMM','ASS 4 MEDIO FRIULI RIABILITAZIONE INTENSIVA PRECOCE - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GENETIC','SMMM','GENETICA CHIOSTRO PIAZZALE KOLBE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIRAMBU','POLU','CHIRURGIA AMBULATORIO N?6 PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TAC','SMMM','CENTRO TAC - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MAGAZ','SMMM','MAGAZZINO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORLSOP','SMMM','OTORINO SALA OPERATORIA - PS',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIRSERV','SMMM','CHIRURGIA SALE OP. - STANZA PIANO SOTTERRANEO STANZA CAPPE FORMALDEIDE/GLUTARALDEIDE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RAD1ECO','SMMM','RADIOLOGIA 1 ELEZIONE AMB. ECOGRAFIA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMB_CMAX','SMMM','AMBULATORI CHIRURGIA MAXILLO FACCIALE - PR',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMB_OTOR','SMMM','AMBULATORI OTORINOLARINGOIATRIA - PR',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CORPOD1','SMMM','CORPO D1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CORPOD3','SMMM','CORPO D3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FARDISTR','SMMM','FARMACIA DISTRIBUZIONE DIRETTA FARMACI - PT',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('LABANPS','SMMM','LABORATORIO ANALISI (EX SCROSOPPI) - PIANO SOTTERRANEO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEURO.F','SMMM','NEUROCHIRURGIA DEGENZE SEZIONE FEMMINILE - PR',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DAYSURS','SMMM','DAY SURGERY SPECIALISTICO - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANAPMAGL','SMMM','ANATOMIA PATOLOGICA LAB. ESTERNO DR.SA DE MAGLIO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PIASANES','SMMM','PIASTRA AMBULATORIO N? 25 ANESTESIA / NEUROCHIRURGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_DEUR','GEMO','AMBULATORIO DERMOCHIRURGIA PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_DERM','GEMO','CLINICA DERMATOLOGICA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_NEUG','GEMO','CLINICA NEUROLOGICA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_AP_INC','SMMM','OBITORIO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_OS_SP2','SMMM','MALATTIE RARE - P2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_SODE','GEMO','SALE OPERATORIE CHIRURGIA PLASTICA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_RADI','SMMM','EMODINAMICA RADIOLOGIA 2  URGENZE - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_RA_DI3','SMMM','PIASTRA AMBULATORIO N? 29 DIETISTE / NUTRIZIONE CLINICA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_RA_DI1','SMMM','PIASTRA AMBULATORIO N? 8 CHIRURGIA PLASTICA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_RA_DI2','SMMM','POLO ANGIOGRAFICO - PS',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_CMAX','SMMM','PIASTRA AMBULATORIO N? 9 CHIRURGIA PLASTICA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_MX_AMB','SMMM','PIASTRA AMBULATORIO N?21 CAPOSALA - P2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_IGIE','IGIE','IGIENE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_GENE1','KOLB','GENETICA CASETTA RADIOIMMUNOLOGIA PIAZZALE KOLBE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_GENET','KOLB','GENETICA CASETTA PIAZZALE KOLBE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DIABET.C','SMMM','PIASTRA AMBULATORIO N? 3 CHIRURGIA PLASTICA / NEUROLOGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CARDIO.D','SMMM','IMMUNOTRASFUSIONALE AMBULATORIO N?37 MALATTIE EMORRAGICHE - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIPLA.A','SMMM','PIASTRA AMBULATORIO N? 13 AUDIOLOGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIPLA.D','SMMM','PIASTRA AMBULATORIO N? 26 EPILUMINESCENZA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIVA.D','SMMM','CHIRURGIA VASCOLARE AMBULATORI - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DERMAT.D','SMMM','PIASTRA AMBULATORIO N? 27 DERMATOLOGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FISRES.S','SMMM','FISIOPATOLOGIA RESPIRATORIA - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDICAMB','SMMM','PIASTRA AMBULATORIO N? 15 OTORINOLARINGOIATRIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDNUC','SMMM','SCOMPENSO CARDIACO - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDURG.1','SMMM','PIASTRA AMBULATORIO N? 16 INALAZIONI - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEUROC.M','SMMM','NEUROCHIRURGIA DEGENZE SEZIONE MASCHILE - PR',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEUSPIN','SMMM','UNITA\' SPINALE DEGENZE - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORTOPE.M','SMMM','PIASTRA AMBULATORIO N?34 CHIRURGIA 2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTOIAT.M','SMMM','PIASTRA AMBULATORIO N?33 CHIRURGIA 2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTOIAT.W','SMMM','OTORINOLARINGOIATRIA AUDIOLOGIA/VESTIBOLOGIA - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PROSOC','SMMM','PIASTRA AMBULATORIO N? 2 UROLOGIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIOL2','SMMM','RADIOLOGIA ORTOPEDIA - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('STOMAT','SMMM','PIASTRA AMBULATORIO N? 11 OCULISTICA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('EMERG','PLMV','EMERGENZA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PRSO','PLMV','PRONTO SOCCORSO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OCUL','PLMV','AMB. OCULISTICA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIO','PLMV','RADIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GINE','PLMV','GINECOLOGIA SALAOPERATORIA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GROP','PLMV','GRUPPO OPERATORIO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDE','PLMV','MEDICINA DEGENZA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GIPAR','PLMV','GINECOLOGIA SALA PARTO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CARD','PLMV','AMB. CARDIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANEST','LTSN','ANESTESIA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GROP','LTSN','GRUPPO OPERATORIO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GISP','LTSN','GINECOLOGIA SALA PARTO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIO','LTSN','RADIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('EMZA','LTSN','EMERGENZA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMBRIA','SMMM','AMBULATORIO RIANIMAZIONE N?5 - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEON_AMB','SMMM','NEONATOLOGIA  AMBULATORI/DAY HOSPITAL - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PED_AMB','SMMM','PEDIATRIA AMBULATORI - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMB3PT','POLU','AMBULATORIO N? 3 - PIANO TERRA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ENDOCR.D','SMMM','ORTOPEDIA PRONTO SOCCORSO - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GAST_DEG','SMMM','CHIRURGIA PLASTICA / OCULISTICA DEGENZE - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('LABMIC','SMMM','SIEROLOGIA - PT',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD9','SMMM','PADIGLIONE 9',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD4POL','POLU','PADIGLIONE 4',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEURO','POLU','NEUROLOGIA AMBULATORIO - P5',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CARDEMOD','SMMM','NUOVA CARDIOLOGIA EMODINAMICA - PS',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PADCSL','SMMM','PADIGLIONE CSL',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PALL','SMMM','PALLONE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANAL2?PI','SMMM','LABORATORIO ANALISI (EX SCROSOPPI) - SECONDO PIANO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_MICROB','SMMM','NUOVA MICROBIOLOGIA - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_ANAPAT','SMMM','NUOVA ANATOMIA PATOLOGICA - L2/L3',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PADCSL_P','POLU','PADIGLIONE CSL',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_ANAT','POLU','IST. ANATOMIA PATOLOGICA - L1/L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_FARGIA','POLU','FARMACOLOGIA - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('C_EMATOL','POLU','CLINICA EMATOLOGICA - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_FARMA','SMMM','NUOVA FARMACIA - L1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_LABEL','SMMM','NUOVO LABORATORIO ANALISI D\'ELEZIONE - L1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_MALRAR','SMMM','NUOVA MALATTIE RARE - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DIRSAN','POLU','DIREZIONE PROFESSIONI SANITARIE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_GENET','SMMM','NUOVO ISTITUTO DI GENETICA MEDICA - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_TRASF','SMMM','NUOVA MEDICINA TRASFUSIONALE - L1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MICROB','SMMM','MICROBIOLOGIA - PS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_IPAT','POLU','ISTITUTO DI PATOLOGIA CLINICA - L1/L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('VIROL','IGIE','VIROLOGIA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PAD_16','POLU','PADIGLIONE N? 16',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_RADURG','SMMM','NUOVA RADIOLOGIA D\'URGENZA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_MEDLAB','SMMM','NUOVA MEDICINA DI LABORATORIO - L2',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CORPOP1','SMMM','CORPO P1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIOTER','SMMM','NUOVA RADIOTERAPIA - PIANO INTERRATO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_ISTGEN','POLU','NUOVO ISTITUTO DI IGIENE ED EPIDEMIOLOGIA CLINICA',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_GROP2','SMMM','NUOVO GRUPPO OPERATORIO - SECONDO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('N_GROP1','SMMM','NUOVO GRUPPO OPERATORIO - PRIMO PIANO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CORPOP3','SMMM','CORPO P3',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CORPOP5','SMMM','CORPO P5',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO1','SMMM','SALA ENDOSCOPICA 1 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO2','SMMM','SALA ENDOSCOPICA 2 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO3','SMMM','SALA ENDOSCOPICA 3 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO4','SMMM','SALA ENDOSCOPICA 4 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO5','SMMM','SALA ENDOSCOPICA 5 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO6','SMMM','SALA ENDOSCOPICA 6 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO7','SMMM','SALA ENDOSCOPICA 7 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('S_ENDO8','SMMM','SALA ENDOSCOPICA 8 - PIANO TERRA',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIOL1','SMMM','RADIOLOGIA 1 ELEZIONE MAMMOGRAFIA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RNM','SMMM','CHIRURGIA PLASTICA/OCULISTICA SALA OP. IN OCULISTICA AMBULATORI - P2',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MEDURG.2','SMMM','MEDICHE POST ACUTI - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIOT','SMMM','OFFICINA ELETTRONICI',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_PS','CIVD','PRONTO SOCCORSO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_DH','CIVD','DAY HOSPITAL',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_LAB','CIVD','LABORATORIO ANALISI CIVIDALE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_CTR','CIVD','IMMUNOTRASFUSIONALE CIVIDALE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RAD','CIVD','RADIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RMA','CIVD','RAD. MAMMOGRAFIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RD1','CIVD','RAD. DIAGNOSTICA 1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RD2','CIVD','RAD. DIAGNOSTICA 2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RD3','CIVD','RAD. DIAGNOSTICA 3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_RD4','CIVD','RAD. DIAGNOSTICA 4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_REC','CIVD','RAD. ECOGRAFIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_MED','CIVD','MEDICINA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_CAR','CIVD','CARDIOLOGIA',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_GO','CIVD','GRUPPO OPERATORIO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_SE','CIVD','SALA ENDOSCOPICA GRUPPO OPERATORIO',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CIV_ACH','CIVD','AMBULATORI GRUPPO OPERATORIO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMBZ','SMMM','AMBULANZE - CENTRALE OPERATIVA 118',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANAT_PAT','SMMM','ANATOMIA PATOLOGICA - PS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANES1','SMMM','ANESTESIA RIANIMAZIONE 1 - P4',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ANES2','SMMM','ANESTESIA RIANIMAZIONE 2 - P4',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CARD_AMB','SMMM','CARDIOLOGIA AMBULATORI - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CARD_EMO','SMMM','CARDIOLOGIA EMODINAMICA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CCH_DEG','SMMM','CARDIOCHIRURGIA DEGENZE - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CCH_SO','SMMM','CARDIOCHIRURGIA SALA OPERATORIA   - P1',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CCHCRPO','SMMM','CARDIOCHIRURGIA C.R.P.O. - P1',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHI1_DEG','SMMM','CHIRURGIA / CHIRURGIA VASCOLARE DEGENZE - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHI1_SO','SMMM','CHIRURGIA 1 SALA OPERATORIA  - P2',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHI2_SO','SMMM','CHIRURGIA 2 SALA OPERATORIA  - P3',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CMAX_AMB','SMMM','CHIRURGIA MAXILLO-FACCIALE AMBULATORIO - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CMAX_DEG','SMMM','CHIRURGIA MAXILLO-FACCIALE DEGENZE - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CMAX_SO','SMMM','CHIRURGIA MAXILLO-FACCIALE SALA OPERATORIA - PS',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CPLA_UST','SMMM','CHIRURGIA PLASTICA-CENTRO USTIONI SALA OPERATORIA - P2',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CVAS_SO','SMMM','CHIRURGIA VASCOLARE SALA OPERATORIA - P6',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DIABET','SMMM','DIABETOLOGIA - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DIAL','SMMM','EMODIALISI - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('DIETI','SMMM','DIETISTE/CLINICA NUTRIZIONALE - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ELIS','SMMM','ELISOCCORSO 118',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FARMA','SMMM','FARMACIA LABORATORIO - PS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FIS_RES','SMMM','BRONCOSCOPIA - PT',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FIS_SAN','SMMM','FISICA SANITARIA - PI',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('GAST','SMMM','GASTROENTEROLOGIA - P1',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('IMM_LAB','SMMM','IMMUNOTRASFUSIONALE LABORATORIO - P1',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('IMM_PREL','SMMM','IMMUNOTRASFUSIONALE PRELIEVI - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MED_DAY','SMMM','MEDICHE DAY HOSPITAL - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MED_NUC','SMMM','MEDICINA NUCLEARE - PI',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MED_URG','SMMM','MEDICINA D\'URGENZA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MED1_DEG','SMMM','MEDICINA INTERNA 1 DEGENZE - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MED2_DEG','SMMM','MEDICINA INTERNA 2 DEGENZE - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('MTS','SMMM','RADIOTERAPIA - PIANO INTERRATO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NCHI_SO','SMMM','NEUROCHIRURGIA SALA OPERATORIA - PR',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEFRO','SMMM','NEFROLOGIA - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEU_DH','SMMM','NEUROLOGIA DAY HOSPITAL - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEU_RAD','SMMM','NEURORADIOLOGIA  PIANO SOTTERRANEO - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEU_SK','SMMM','NEUROLOGIA-STROKE UNIT - P5',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NFPT','SMMM','NEUROFISIOPATOLOGIA AMBULATORI - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NIDO_CO','SMMM','NEONATOLOGIA NIDO/CLINICA DI OSTETRICIA - P7',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OCU_AMB','SMMM','OCULISTICA AMBULATORI  - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OCU_SO','SMMM','OCULISTICA SALA OPERATORIA  - PR',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ONC','SMMM','ONCOLOGIA  AMBULATORI - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ONC_DEG','SMMM','ONCOLOGIA DEGENZE - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ONC_DH','SMMM','ONCOLOGIA DAY HOSPITAL - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORT_AMB','SMMM','PIASTRA AMBULATORIO ORTOPEDIA - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORT_DEG','SMMM','ORTOPEDIA DEGENZE - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORT_SO','SMMM','ORTOPEDIA SALA OPERATORIA - PT',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTO_DEG','SMMM','OTORINO-MAXILLO FACCIALE DEGENZE - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTO_SO','SMMM','OTORINOLARINGOIATRIA SALA URGENZE  - P4',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PNE_DEG','SMMM','PNEUMOLOGIA DEGENZE - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('PSOC','SMMM','PRONTO SOCCORSO - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RAD1','SMMM','RADIOLOGIA 1 ELEZIONE - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RAD2','SMMM','RADIOLOGIA 2 URGENZE - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADIO_IS','SMMM','RADIOTERAPIA LABORATORIO OFFICINA - PIANO SOTTERRANEO',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RADTER','SMMM','RADIOTERAPIA - PIANO SOTTERRANEO',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('RMN','SMMM','RISONANZA MAGNETICA - PS',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('STE_CEN','SMMM','CENTRALE STERILIZZAZIONE - PS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('URO_DEG','SMMM','UROLOGIA DEGENZE - P5',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('URO_SO','SMMM','UROLOGIA SALA OPERATORIA  - P5',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('UTIC','SMMM','CARDIOLOGIA / UNITA\' CORONARICA - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('IMMSOTTE','SMMM','IMMUNOTRASFUSIONALE STANZA P - PS',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('P_NEUR','SMMM','NEUROLOGIA DEGENZE - P5',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIRU1W','SMMM','CHIRURGIA DAY SURGERY - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIRUR2W','SMMM','CHIRURGIA DEGENZE - P3',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('CHIVA.A','SMMM','PIASTRA AMBULATORIO N? 14 OTORINOLARINGOIATRIA - P2',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('NEUROC.W','SMMM','UNITA\' SPINALE AMBULATORI - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('ORTOPE.A','SMMM','ANATOMIA PATOLOGICA SALA OP. CHIRURGIA - P3',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('OTOIAT.A','SMMM','PIASTRA AMBULATORIALE SALA OPERATORIA  - P2',3,2, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('LABSIC','SMMM','LABORATORIO ITALTBS',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AUDIO','SMMM','OTORINO/NEONATOLOGIA AUDIOLOGIA E FONETICA - PT',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('AMBGIN','SMMM','GINECOLOGIA AMBULATORI - P1',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('FORMAZIO','SMMM','PALAZZINA ESTERNA FORMAZIONE',1,0, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('TER_ANT','SMMM','TERAPIA ANTALGICA AMBULATORI - P4',2,1, ".time().");");
db_query("INSERT INTO  room_risk( room_id, hosp_id,name,risk,el_group,updated) VALUES ('IMM_AEM','SMMM','IMMUNOTRASFUSIONALE - AUTOEMOTECA',1,0, ".time().");");
        
        
//TODO: inserimento del rischio per le stanze restanti 

}

/**
 * carica la tabella dei rischi delle apparecchiature a blocchi
 * @param int $items_count
 * @param int $start_from
 * @return updated records
 */
function load_items_table($items_count=_RISK_UPDATE_DEFAULT, $start_from=0) {
    $conn = si3c_connect(); 
// la funzione limit non è contemplata in oracle, le soluzioni sono un po più complesse
//    $sub_qy="SELECT * FROM SI3C.T_APPA a INNER JOIN SI3C.T_CLAP c ON c.CLAP_CODI=a.CLAP_CODI 
//                        WHERE APPA_CANC = 'N' ORDER BY APPA_CODI";
//    $qy="SELECT * FROM ($sub_qy) WHERE rownum <= ". ($start_from+$items_count)."
//        MINUS 
//        SELECT * FROM ($sub_qy) WHERE rownum <= ".$start_from;
//    
    $qy="SELECT * FROM ( SELECT SIST_CODI, MOAP_CODI, UBIC_CODI, OSPE_CODI, APPA_CODI, APPA_ETIC_CODI,
                                APPA_CODI_INVE, 
                                CLAP_CODI,
                                APPA_DATA_RILE,
                                APPA_DATA_ACQU,
                                APPA_DATA_COLL,
                                APPA_DATA_FABB,
                        ROW_NUMBER() OVER (ORDER BY APPA_CODI) R FROM SI3C.T_APPA 
                        WHERE APPA_CANC = 'N' AND STAP_CODI <> 'FU' ) a
                        INNER JOIN SI3C.T_CLAP c ON c.CLAP_CODI=a.CLAP_CODI 
                        WHERE a.R BETWEEN $start_from AND ". ($start_from+$items_count);
    $qy=oci_parse($conn,$qy);
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    $update_counter=0;
    while ($row=oci_fetch_array($qy) ) {     
        $update_counter+=(_update_item($row)?1:0);//inserimento e aggiornamento contatore
    }
    return $update_counter;
}
/**
 * funzione per l'update di una singola voce 
 * @param type $item_code
 * @param type $is_APPACODI
 * @return appacodi o etic codi in base a cosa è stato inviato
 */
function update_one_item($item_code, $is_APPACODI=TRUE) {
    $conn = si3c_connect(); 
    $qy="SELECT SIST_CODI, 
                MOAP_CODI, 
                UBIC_CODI, 
                OSPE_CODI, 
                APPA_CODI, 
                c.CLAP_CODI AS CLAP_CODI,
                APPA_ETIC_CODI,
                APPA_CODI_INVE, 
                APPA_DATA_RILE,
                APPA_DATA_ACQU,
                APPA_DATA_COLL,
                APPA_DATA_FABB
        FROM SI3C.T_APPA  a
        INNER JOIN SI3C.T_CLAP c ON c.CLAP_CODI=a.CLAP_CODI 
        WHERE APPA_CANC = 'N' AND ". ($is_APPACODI?"APPA_CODI = ":"APPA_ETIC_CODI = "). $item_code;
    $qy=oci_parse($conn,$qy);
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    if ($row=oci_fetch_array($qy) ) {     
        if( _update_item($row)){
            if (($is_APPACODI)) 
                return $row['APPA_ETIC_CODI'];
            else
                return  $row['APPA_CODI'];
        }       
        else return FALSE;
    }
    else return FALSE;
}

/**
 * funzione di servizio carica una singola voce della tabella dei rischi
 * @param type $item_array
 * @return string query di inserimento
 */
function _update_item($item_array){
    $system_count=system_count($item_array['SIST_CODI']);
    $redundancy=redundancy($item_array['MOAP_CODI']);
    $class_id=$item_array['CLAP_CODI']; 
    $room_id=$item_array['UBIC_CODI'];
    $hosp_id=$item_array['OSPE_CODI'];
    $stat = MT_stats($item_array['APPA_CODI']);
    $link = search_nid_struct($item_array['APPA_ETIC_CODI']);
    //ricerca se l'item è già presente 
    $q1="SELECT ID FROM items_risk WHERE service_id= '".$item_array['APPA_CODI']."'";
    if($ID=db_result(db_query($q1))){
        $qy="UPDATE items_risk SET
        description = '%s',
        age="._get_age($item_array).",
        service_label = '".$item_array['APPA_ETIC_CODI']."',
        inventario = '".$item_array['APPA_CODI_INVE']."',
        maintenance_count=".$stat['count'].",
        MTTR = ".round($stat['MTTR'],2).", 
        stdev_MTTR = ".round($stat['devMTTR'],2).",
        MTBF = ".round($stat['MTBF'],2).", 
        stdev_MTBF = ".round($stat['devMTBF'],2).",
        MTTF = ".round($stat['MTTF'],2).", 
        stdev_MTTF = ".round($stat['devMTTF'],2).",
        system_count = ".$system_count.",
        redundancy = ".$redundancy.",
        class_id = '".$class_id."',
        nid = ".$link['nid'].",   
        struct_id = ".$link['struct'].",
        room_id = '".$room_id."',
        hosp_id = '".$hosp_id."',
        data = '".$stat['data']."',
        risk = ".item_risk ($room_id,$hosp_id,$class_id,$system_count,$redundancy).",
        updated = ".time()." WHERE ID = ".$ID;
    }
    else {
        $qy="INSERT INTO items_risk (service_id, age, service_label, description, inventario, 
            maintenance_count, MTTR, stdev_MTTR,MTBF,stdev_MTBF,MTTF,stdev_MTTF,
             system_count,redundancy,class_id,nid,struct_id,room_id,hosp_id,risk,updated,data,TIMESTAMP) 
                VALUES ('".$item_array['APPA_CODI']."',"._get_age($item_array).",'".$item_array['APPA_ETIC_CODI']."','%s','".$item_array['APPA_CODI_INVE']."',".
                $stat['count'].",".$stat['MTTR'].",".$stat['devMTTR'].",".$stat['MTBF'].",".$stat['devMTBF'].",".$stat['MTTF'].",".$stat['devMTTF'].",
                    ".$system_count.",
                    ".$redundancy.",'".$class_id."',".$link['nid'].",".$link['struct'].",
                    '".$room_id."', '".$hosp_id."',
                    ".item_risk ($room_id,$hosp_id,$class_id,$system_count,$redundancy).",".time().",'".$stat['data']."',CURRENT_TIMESTAMP)";          
    }   
    return db_query($qy,$item_array['CLAP_NOME']);;
}

/**
 * ricerca l'anno di installazione fra vari possibili campi
 * @param object $row
 * @return int
 */
function _get_age($row){
    $year=1900;
    if (isset($row['APPA_CODI_INVE'])) {
        $inventario=explode('/',$row['APPA_CODI_INVE']);
        if (strlen($inventario[0])>4) $inventario[0]=substr($inventario[0], 0, 4);
         
     /* questo non serve a nulla e produce errori di ricerca con numeri di inventario errati
      * verificare se possibile utilizzare solo nintroito
      *    if($ascott=ascott_connect())
            if ($qy=oci_parse($ascott,"SELECT ANNO  FROM E14SEL.E_V_BEIN_DETT  WHERE
                        NUMERO_INTROITO=$inventario[1] AND ANNO=$inventario[0]"))
                if(oci_define_by_name($qy, 'ANNO', $year)) // assegna variabile a campo 
                    if(oci_execute($qy,OCI_DEFAULT))
                        if(oci_fetch($qy)) return (integer)$year;
      */
    }
    if(isset($row['APPA_CODI_ACQU'])) return (integer)date_format($row['APPA_CODI_ACQU'],'Y'); //anno di acquisto
    if(isset($row['APPA_CODI_COLL'])) return (integer)date_format($row['APPA_CODI_COLL'],'Y'); //anno di collaudo
    if(isset($row['APPA_CODI_FABB'])) return (integer)date_format($row['APPA_CODI_FABB'],'Y'); //anno di fabbricazione
 //   if(isset($row['APPA_CODI_RILE'])) return (integer)date_format($row['APPA_CODI_RILE'],'Y'); //anno di rilevazione
    if(isset($inventario[0])&& is_numeric($inventario[0])) 
        if ($inventario[0]>$year && $inventario[0]<format_date(time(),'custom','Y'))  return $inventario[0];
    return (integer)$year;
}
function item_count(){
    $conn = si3c_connect(); 
    $qy=oci_parse($conn,"SELECT COUNT(*) AS C FROM SI3C.T_APPA a WHERE APPA_CANC = 'N' AND STAP_CODI <> 'FU'");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    if ($count=oci_fetch_array($qy) ) 
        return $count['C'];
    else
        return 0;
}
/**
 * conta gli item associati al sistema
 * @param type $system_id
 * @return int
 */
function system_count($system_id){
    $conn = si3c_connect(); 
    $qy=oci_parse($conn,"SELECT COUNT(*) AS C FROM SI3C.T_APPA a WHERE APPA_CANC = 'N' AND SIST_CODI = '".$system_id."'");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    if ($count=oci_fetch_array($qy)) 
        return (int) $count['C']; 
    else  
        return 0;   
}

/**
 * calcolo del rischio del singolo item
 * @param type $room_id
 * @param type $hosp_id
 * @param type $class_id
 * @param type $system_count
 * @param type $struct_id
 * @return int
 */
function item_risk ($room_id,$hosp_id,$class_id,$system_count=0,$redundancy=1,$struct_id=0){
    $risk=0;//contatore del rischio
    $room = get_room_risk($room_id, $hosp_id);
    $class = get_class_risk($class_id);
    $struct_risk = get_struct_risk($struct_id);
    /*
     * rischio calcolato come somma di :
     *  - rischio locale + rischio classe apparechiatura (somma dei singoli rischi i calsse)
     *  - +1 se appartiene a sistema (system_count>0)
     *  - +1 se non ridondata
     *  - rischio legato al clima della struttura
     */
    $risk += $room['risk']*($class['patient_risk']+
                          $class['function_risk']+
                          $class['user_risk']);
    $risk += $system_count>0?1:0;
    $risk += $redundancy>1?0:1;
    $risk += $struct_risk;
    return $risk;
}

/**
 * esprime la ridondanza di apparecchiature di quel modello
 * @param string $model_code codice modello
 * @return int
 */
function redundancy($model_code){
    if ($model_code=='--') return 1; // modello sconosciuto
    $conn = si3c_connect(); 
    $qy="SELECT COUNT(*) AS C FROM SI3C.T_APPA a WHERE APPA_CANC = 'N' AND MOAP_CODI = '".$model_code."'";
    $qy=oci_parse($conn,$qy);
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    if ($count=oci_fetch_array($qy)) 
        return (int) $count['C']; 
    else  
        return 1;
}

/**
 * recupera il rischio e il gruppo associato alla stanza
 * @param type $room_id
 * @param type $hosp_id
 */
function get_room_risk($room_id, $hosp_id){
    $qy="SELECT risk, el_group FROM room_risk WHERE room_id ='%s' and hosp_id='%s'";
    $qy=  db_query($qy,$room_id,$hosp_id);
    if($rs=  db_fetch_array($qy)) {
        return $rs;
    }
    else {
        return array('risk'=>1,'el_group'=>0);
    }
}

function get_class_risk($class_id){
    $qy="SELECT patient_risk, function_risk, user_risk FROM class_risk WHERE class_id ='%s'";
    $qy=  db_query($qy,$class_id);
    if($rs=  db_fetch_array($qy)) {
        return $rs;
    }
    else {
        return array('patient_risk'=>0, 'function_risk'=>0, 'user_risk'=>0);
    }
}

function get_struct_risk($struct_id){
    $qy="SELECT risk FROM struct_risk WHERE struct_id ='%s'";
    if($rs=db_result(db_query($qy,$struct_id))) {
        return $rs;
    }
    else {
        return 0;
    }
}

function inizialize_module(){
   $ret = array();
   $schema=  schemaLoader();
   foreach ($schema as $name => $table) {
        db_create_table($ret, $name, $table);
    }
    reset_class_risk(TRUE);
    reset_room_risk(TRUE);
    reset_struct_risk(TRUE);  
    variable_set(_RISK_SET, TRUE);
}

function destroy_module(){
    variable_del(_RISK_LAST_UPDATE);
    variable_del(_RISK_MAX_UPDATE);
    variable_del(_LAST_COMPLETE_UPDATE);
    variable_del(_LAST_UPDATED_ITEM_COUNT);
    $schema=  schemaLoader();
    foreach ($schema as $name => $table) {
        db_drop_table($ret, $name);
    }
    variable_del(_RISK_SET);
}

function cron_risk(){
    $start=variable_get(_RISK_LAST_UPDATE,0);
    $max_count=  variable_get(_RISK_MAX_UPDATE, _RISK_UPDATE_DEFAULT);
    $total_items= variable_get(_LAST_UPDATED_ITEM_COUNT, item_count()) ;
    $current_count= load_items_table($max_count, $start);
    if (($start+$current_count)>=$total_items) {
        variable_set(_RISK_LAST_UPDATE,0);
        variable_set(_LAST_COMPLETE_UPDATE,time());
        variable_set(_LAST_UPDATED_ITEM_COUNT,  item_count());
    }
    else {
        variable_set(_RISK_LAST_UPDATE, $start+$current_count);
    }
}

function risk_admin_form(&$form_state,$test=NULL){
    
    $form['status']=array(
        '#type'=>'item',
        '#value'=> 'aggiornati '.variable_get(_RISK_LAST_UPDATE,0).' record di '.item_count().'<br/>
         ultimo aggiornamento completo '. format_date(variable_get(_LAST_COMPLETE_UPDATE,0))
        ,
    );
    if (!variable_get(_RISK_SET,FALSE)){
        $form['inizialize']=array(
            '#type'=>'button',
            '#value'=>'inizialize',
            '#title'=>'Inizializza',
            '#executes_submit_callback'=>TRUE,
            '#access'=>  user_access(_MANGE_TESTED)
        );
    }
    else {
        $form['destroy']=array(
            '#type'=>'button',
            '#value'=>'destroy',
            '#title'=>'Cancella tutto',
            '#executes_submit_callback'=>TRUE,
            '#access'=>  user_access(_MANGE_TESTED)
        );
    
       $form['load']=array(
           '#type'=>'button',
           '#value'=>'load',
           '#executes_submit_callback'=>TRUE,
           '#access'=>  user_access(_MANGE_TESTED)
       );
       $form['export']=array(
           '#type'=>'button',
           '#value'=>'esporta',
           '#executes_submit_callback'=>TRUE,
           '#access'=>  user_access(_MANGE_TESTED)
       );
       $form['result']=array(
           '#type'=>'fieldset',
           '#title'=>'risultati',
           '#value'=>  risk_table($form_state,$test),
           '#collapsible'=> FALSE,
           '#collapsed'=>FALSE
       );
    }
    return $form;
}

function risk_admin_form_submit($form,&$form_state){
    $op=$form_state['values']['op'];
    $values=$form_state['values'];
    switch($op){
        case 'inizialize': inizialize_module(); break;
        case 'destroy': destroy_module(); break;
        case 'load': cron_risk(); break;
        case 'esporta': export_risk(); break;    
    }
}


/**
 * interventi di manutenzione correttiva 
 * @param type $APPA_CODI
 * @return int
 */
function maintenance_stats($APPA_CODI){
    $conn = si3c_connect(); 
    $qy=oci_parse($conn,"SELECT COUNT(*) AS C,
                          AVG(INTE_DATA_FINE-INTE_DATA_INIZ) AS M,
                          STDDEV(INTE_DATA_FINE-INTE_DATA_INIZ) AS D FROM SI3C.T_INTE a 
                          WHERE INTE_CANC = 'N' AND APPA_CODI = ".$APPA_CODI.
                          " GROUP BY APPA_CODI");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy); 
    if ($stat=oci_fetch_array($qy) ) {
        if (is_null($stat['C']))  $stat['C']=0;  
        if (is_null($stat['M']))  $stat['M']=0;  
        if (is_null($stat['D']))  $stat['D']=0;  
        return $stat;
    }
    else
        return array('C'=>0,'M'=>0,'D'=>0);
    
}

/**
 * calcola i parametri di affidabilità dell'apparecchiatura in ore
 * @param type $APPA_CODI
 * @return array contenitore delle statistiche
 */
function MT_stats($APPA_CODI){
    $conn = si3c_connect(); 
    $qy=oci_parse($conn,"SELECT 
                            (INTE_DATA_FINE - TO_DATE('19700101','yyyymmdd'))*24 -
                             -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS UPTIME, 
                            (INTE_DATA_INIZ -TO_DATE('19700101','yyyymmdd'))*24 -
                             -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS DOWNTIME , 
                             (INTE_DATA_UAGG -TO_DATE('19700101','yyyymmdd'))*24 -
                             -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS UAGG ,
                            INTE_STAT AS S
                         FROM SI3C.T_INTE a 
                         INNER JOIN SI3C.T_RIIN r ON r.RIIN_CODI= a.RIIN_CODI 
                         WHERE a.INTE_CANC = 'N' AND r.APPA_CODI = ".$APPA_CODI.
                          " ORDER BY INTE_DATA_INIZ ASC");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
    //inizializza con una data di installazione o rilevazione
    $prev_uptime=round(MT_get_start_days($APPA_CODI));
    $MTBF=$MTTF=$MTTR=$count=0;
//TODO: verificare che i formati time() e oracle corrispondano eventualmene usare sysdate
    $now=time()/3600; //espresso in giorni 
    $alert=-1; //contatore delle situazioni anomale in particolare +di un intervento aperto (se 1 il contatore è a zero)
    while($r=oci_fetch_array($qy) ){
        $r['DOWNTIME']=round($r['DOWNTIME']);
        if (isset($r['UPTIME'])) 
            $r['UPTIME']=round($r['UPTIME']);
        else if ($r['S']=='C' && !isset($r['UPTIME']) && isset($r['UAGG']) && $r['UAGG']> $r['DOWNTIME']) 
           $r['UPTIME']=round($r['UAGG']);//correzione dati incongruenti, intervento chiuso ma manca la data di fine
        else 
            $r['UPTIME']=0;
        
        if ($prev_uptime>0 && $prev_uptime<$r['DOWNTIME'] ) { //salta una rilevazione se non identifica una data di partenza sensata          
            $count++;
            $data['timings'][$prev_uptime]='UP';
            $data['timings'][$r['DOWNTIME']]='DOWN';
            $data['timings'][$r['UPTIME']]='UP';
//TODO:verificare valori null 
            $MTBF=$MTBF + ( $r['DOWNTIME']- $prev_uptime);//accumulo   per il time to failure
            $sMTBF=$sMTBF + pow( $r['DOWNTIME']- $prev_uptime,2);//accumulo   per il time to failure stdDEV
            $data['curves']['MTBF'][$r['DOWNTIME']]=$MTBF/$count;//inserisce  dati grafico  MTBF
            if($r['S']=='C') {
            //l'intervento è chiuso 
                $MTTR=$MTTR+$r['UPTIME']-$r['DOWNTIME']; //accumulo differenze durata interventi
                $data['curves']['MTTR'][$r['UPTIME']]=$MTTR/$count;//inserisce  dati grafico  MTTR
                $data['curves']['A'][$r['UPTIME']]=round($MTBF/($MTBF+$MTTR),2);//inserisce  dati grafico  disponibilità
                $sMTTR=$sMTTR+pow($r['UPTIME']-$r['DOWNTIME'],2); //accumulo differenze durata interventi stdDEV
                $MTTF=$MTTF + ( $r['UPTIME']- $prev_uptime); //accumulo per il time between failure
                $data['curves']['MTTF'][$r['UPTIME']]=$MTTF/$count;//inserisce  dati grafico  MTTF
                $sMTTF=$sMTTF + pow( $r['UPTIME']- $prev_uptime,2); //accumulo per il time between failure stdDEV
            } else {
            // l'intervento è aperto  
                $MTTR=$MTTR+$now-$r['DOWNTIME']; //accumulo differenze durata interventi
                $data['curves']['MTTR'][$now]=$MTTR/$count;//inserisce  dati grafico  MTTR
                $data['curves']['A'][$now]=round($MTBF/($MTBF+$MTTR),2);//inserisce  dati grafico  disponibilità
                $sMTTR=$sMTTR+pow($now-$r['DOWNTIME'],2); //accumulo differenze durata interventi stdDEV
                $MTTF=$MTTF + ( $now - $prev_uptime); //accumulo per il time between failure
                $data['curves']['MTTF'][$now]=$MTTF/$count;//inserisce  dati grafico  MTTF
                $sMTTF=$sMTTF + pow( $now - $prev_uptime,2); //accumulo per il time between failure stdDEV
                $alert++;// attiva flag
            }
           
        }
        $prev_uptime=$r['UPTIME'];  
    }
    $data['alert']=$alert;//inserisce alert nella lista dei dati 
    if($count){
        $stat['MTTR']=round($MTTR/$count);
        $stat['MTTF']=round($MTTF/$count);
        $stat['MTBF']=round($MTBF/$count);
        $stat['devMTTR']=round(sqrt(abs($sMTTR/$count-pow($stat['MTTR'],2))));
        $stat['devMTTF']=round(sqrt(abs($sMTTF/$count-pow($stat['MTTF'],2))));
        $stat['devMTBF']=round(sqrt(abs($sMTBF/$count-pow($stat['MTBF'],2))));
        $stat['count']=$count;
        $stat['data']=serialize($data);
    }
    else {
        $stat['MTTR']=$stat['MTTF']=$stat['MTBF']=$stat['devMTTR']=$stat['devMTTF']=$stat['devMTBF']=$stat['count']=0;
    }
    return $stat; 
}

/**
 * ricerca una data di installazione fra quelle disponibili.
 * Priorità: Clollaudo, acquisto, inserimento, rilevazione
 * @param type $APPA_CODI identificativo apparecchaitura
 * @return int giorno iniziale installazione 
 */
function MT_get_start_days($APPA_CODI){
    $conn = si3c_connect(); 
    $qy=oci_parse($conn,"SELECT 
                (APPA_DATA_RILE - TO_DATE('19700101','yyyymmdd'))*24 -
                  -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS D,
                (APPA_DATA_ACQU - TO_DATE('19700101','yyyymmdd'))*24 -
                  -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS B,
                (APPA_DATA_COLL - TO_DATE('19700101','yyyymmdd'))*24 -
                  -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS A,
                (APPA_DATA_INSE- TO_DATE('19700101','yyyymmdd'))*24 -
                  -   TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3)) AS C
        FROM SI3C.T_APPA  
        WHERE APPA_CANC = 'N' AND APPA_CODI = $APPA_CODI");
    oci_execute($qy,OCI_DEFAULT); //db_query($qy);
    if ($r=oci_fetch_array($qy)) {
        if (!is_null($r['A'])) return $r['A'];
        if (!is_null($r['B'])) return $r['B'];
        if (!is_null($r['C'])) return $r['C'];
        if (!is_null($r['D'])) return $r['D'];
    }
    return 0;
}

/**
 * ricerca collaudi associati e struttura relativa
 * @param type $APPA_ETIC_CODI
 * @return type
 */
function search_nid_struct($APPA_ETIC_CODI){
    $qy="SELECT main_nid AS nid, reparto AS struct FROM `apparecchiature per ordine` WHERE `etichetta italtbs`= %d";
    $qy=db_query($qy,$APPA_ETIC_CODI);
    if ($res=  db_fetch_array($qy))
        return $res;
    else 
        return array('nid'=>0,'struct'=>0);
    
}

function risk_table(&$form_state,$test=NULL){
    $graph=array(
        '#type'=>'fieldset',
        '#title'=>'grafici',
        '#collapsible'=> false,
        '#collapsed'=>TRUE,
        '#value'=>'<div id="overall-graph"></div>'
    );
    grafico_distribuzioni('overall-graph');
    $output=drupal_render($graph);
    $output.=_risk_view($test,NULL,FALSE);
    return $output;
}

function item_risk_table($APPACODI,$isAPPACODI=FALSE){
    return _risk_view(NULL,$APPACODI,$isAPPACODI);
}
function _risk_view($test,$appacodi,$isAPPACODI){

    $header=array(//'ID',
        'attrezzatura',
        array('data'=> 'rischio','field'=>'i.risk','style'=>'text-align: right;'),
        array('data'=> 'età','field'=>'i.age','style'=>'text-align: right;'),
        array('data'=>'Interventi','field'=>'i.maintenance_count','style'=>'text-align: right;'),
        array('data'=>'MTTR (gg)','field'=>'i.MTTR','style'=>'text-align: right;'),
        array('data'=>'MTBF (gg)','field'=>'i.MTBF','style'=>'text-align: right;'),
        array('data'=>'MTTF (gg)','field'=>'i.MTTF','style'=>'text-align: right;'),
        array('data'=>'Disponiblit&agrave;','field'=>'A','style'=>'text-align: right;'),
        
        array('data'=>'componenti sistema','field'=>'i.system_count','style'=>'text-align: right;'),
        array('data'=>'ridondanza','field'=>'i.redundancy','style'=>'text-align: right;'),
        array('data'=>'DPT\SOC di rifeimento','field'=>'s.description'),
        array('data'=>'aggiornato','field'=>'i.updated'),
        '',
    );
    $filter='';
    if ($test != NULL && $test !=0) {
        $q= "SELECT `etichetta italtbs` FROM `apparecchiature per ordine` WHERE `numero ordine` = %d"; 
        $q=db_query($q,$test);
        unset($IDs);
        while ($id=db_result($q)){ if ($id!=0) $IDs[]=$id;} //carica le ettichette non vuote 
        if (isset($IDs)) {  
            $filter=" WHERE i.service_label IN (".implode(',', $IDs).")";
        }
        else {
            return "Non ci sono dati per valutare.";
        }
    } elseif ($appacodi!=NULL && $appacodi!=0 ) {
        if($isAPPACODI)
            $filter=" WHERE i.service_id = '$appacodi' ";
        else
            $filter=" WHERE i.service_label = '$appacodi' ";
    }
    
    $qy="SELECT ". 
//         i.id AS id,
       "i.description AS description, 
       i.inventario AS inventario, 
       i.service_id AS service_id,
       i.risk as risk,
       i.age as age,
       i.maintenance_count AS maintenance_count,
       i.MTTR/24 AS MTTR,
       i.MTBF/24 AS MTBF,i.MTTF/24 AS MTTF,
       (i.MTBF/(i.MTBF+i.MTTR))*100 AS A,
       i.system_count AS system_count,
       i.redundancy AS redundancy,".
//       i.class_id ,
//       i.struct_id,
//       i.room_id,
//       i.hosp_id,
       " s.description as struttura,       
       i.updated,
       i.nid
       FROM items_risk i
       LEFT JOIN "._STRUCT_TABLE." s ON s.id=i.struct_id 
           $filter  ". tablesort_sql($header) ; 
//    INNER JOIN class_risk c ON c.class_id=i.class_id
//    LEFT JOIN room_risk r ON r.hosp_id=i.hosp_id AND r.room_id=i.room_id
//    LEFT JOIN struct_risk s ON s.struct_id=i.struct_id";
    
    $qy = pager_query($qy,100);
    while($rw = db_fetch_array($qy)){
        foreach ($rw as $key=>$field){
            switch ($key ) {
                case 'nid' : $row[]=($field!=0)?l('collaudo','node/'.$field):''; break;
                case 'service_id': $row[]='<i>'.l($rw['description']. ' '.$rw['inventario'],'tested/si3c/APPA_CODI/'.$field).'</i>'; break;
                case 'updated': $row[]=  '<small>'.format_date($field,'small').'</small>'; break;
                case 'risk': $row[]='<span style="font-weight: bold;color: '.($field<10?'green':($field>=25?'red':'orange')).'">'.$field.'</big>';break;
                case 'A': $row[]=array('data'=>(string)round($field,4).'%','style'=>'text-align: right;');break;
                case 'age': $row[]=array('data'=>(format_date(time(),'custom','Y')- $field),'style'=>'text-align: right;'); break;
                case 'description':    
                case 'class_id':
                case 'inventario':
                    break;
                default: $row[]=array('data'=>is_numeric($field)?round($field,2):$field,'style'=>'text-align: right;'); break;
            }
        }
        $rows[]=$row;
        unset($row);
    }
    return theme_pager().' '.theme('table',$header,$rows);
}

/**
 * mostra una singola voce di inventario
 * @param type $item_code
 * @return string form
 */
function single_item_show($item_code){
    $out=" nessun'attrezzatura trovata con codice $item_code";
    $qy="SELECT * FROM risk_items i 
        LEFT JOIN room_risk r ON r.hosp_id=i.hosp_id AND r.room_id=i.room_id
        LEFT JOIN class_risk c ON c.class_id=i.class_id
        LEFT JOIN struct_risk a ON s.struct_id=i.struct_id
        LEFT JOIN "._STRUCT_TABLE." st ON st.id=i.struct_id
        WHERE i.service_id= '%s'";
    $qy=db_query($qy,$item_code);
    if ($item=  db_fetch_array($qy)){
//TODO: all form        
        $out='<table><tr>';
        $out.='<td></td>';
        $out='</tr></table>';

    }
    return $out;
}

function export_risk(){
    drupal_set_header('Content-Type: text/x-comma-seperated-values');
    drupal_set_header('Content-Disposition: attachment; filename="result-'.format_date(time(),'small').'.csv"');
    //stampa intestazione
    $rows= '"attrezzatura";"inventario";"etichetta";"rischio";"età";N.Manutenzioni";"MTTR(gg)";"deviazione standard MTTR(gg)";"MTTF(gg)";"deviazione standard MTTF(gg)";"MTBF(gg)";"deviazione standard MTBF(gg)";"componenti sistema";"ridondanza";"DPT\SOC di rifeimento";"aggiornato"'."\n";
    $qy="SELECT ". 
//         i.id AS id,
       "i.description AS description, 
       i.inventario AS inventario, 
       i.service_id AS service_id,
       i.risk as risk,
       i.age as age,
       i.maintenance_count AS maintenance_count,
       i.MTTR AS MTTR,
       i.stdev_MTTR AS stdev_MTTR,
       i.MTTF AS MTTF,
       i.stdev_MTTF AS stdev_MTTF,
       i.MTBF AS MTBF,
       i.stdev_MTBF AS stdev_MTBF,
       i.system_count AS system_count,
       i.redundancy AS redundancy,".
//       i.class_id ,
//       i.struct_id,
//       i.room_id,
//       i.hosp_id,
       " s.description as struttura,       
       i.updated ".
//       i.nid
       "FROM items_risk i
       LEFT JOIN "._STRUCT_TABLE." s ON s.id=i.struct_id";
    $qy = db_query($qy);
    
    while ($rw = db_fetch_array($qy)) {
         $rw['updated']=format_date($rw['updated']);
         $rows.= implode(';', $rw)."\n";
    }
    print $rows;
    exit();
}

/**
 * 
 * @param array $data contiene le matrici dei dati:
 * timing : matrice degli eventi UP e DOWN, la chiave sono le ore timestamp (*3600 e si hanno i secondi timestamp
 * curves : contiene i valori  MTTR, MTTF e MTBF durante i vari eventi. Le chiavi sono gli eventi come ore timestamp
 * @return none.
 */

function grafico_Mean_Times($graph){
    
    if(!isset($graph['curves'])) return FALSE;
    foreach($graph['curves'] as $type=>$curve){ //carica i dati 
        ksort($curve);
        foreach ($curve as $day=>$value){
            $d=format_date($day*3600,'custom','Y-m-d');
            $dt[]=array($d,(float)($type=='A'?$value*100:$value/24));
        }
        $data['data'][]=$dt;
        unset($dt);
        if ($type=='A') 
            $series[]=array('label'=>"Disponibilità",
                            'yaxis'=>'y2axis',
                            'color'=>'#77d46c'
            );
        else
            $series[]=array('label'=>$type,'yaxis'=>'yaxis');
                
    }
 //javascript configuration object
  $data['options'] = array(
        'title' => "Analisi disponibilità",
        'seriesColors' => array('#6c83d4','#d46c6c','#d2d46c', '#c1c2d0',  ),
        'seriesDefaults'=>Array(
//            'linePattern'=> 'dashed',
            'showMarker'=> false,
            'shadow'=> false,
            'rendererOptions'=>array('smooth'=> true)
        ),
        'series'=>$series,
        'axes' => array(
            'xaxis' => array(
                'renderer' => '$jq.jqplot.DateAxisRenderer',
                'autoscale'=>true,
//                'numberTicks'=> 5,
                'tickOptions' => array(
                    array('formatString' => '%b %#d')
                ),
            ),
                'yaxis' => array(
                    'tickOptions' => array(
                        'formatString' => '%d gg'
                      ),
//                    'min'=>-1,
//                    'max'=>$max +2,
//                    'autoscale'=>true,
//                    'label'=>'scala giorni intervento'
                ),
                'y2axis' => array(
                    'min'=>85,
                    'max'=> 100,
                    'tickOptions' => array( 
                        'formatString' => '%d %'
                      ),                    
//                    'label'=>'Disponibilit&agrave;'
                ),         
           ),
           'highlighter'=> array(
              'show'=> true,
              'sizeAdjust'=>7.5
           ),
//           'series'=>array('lineWidth'=>4, 'markerOptions'=>array( 'style'=>'square')),
           'cursor'=> array(
//              'show'=> false,
              'zoom'=>true,
              'looseZoom'=> true
           ),
           'legend' => array('show' => true, 'location' => 's', 'placement'=>'outsideGrid')
    );
    return $data;
  
}

function grafico_distribuzioni($HTMLid){
    //distribuzione della MTBF
    $q="SELECT count(*) as c_MTBF, MTBF FROM items_risk group by MTBF";
    $q=db_query($q);
    while ($r=db_fetch_array($q)) {
        $d[]=array((integer)round($r['c_MTBF']),(float)$r['MTBF']);
    }
    $dt[]=$d;
    unset($d);
    $series[]=array('label'=>'MTBF');
    //distribuzione della MTTR
    $q="SELECT count(*) as c_MTTR, MTTR FROM items_risk group by MTTR";
     $q=db_query($q);
    while ($r=db_fetch_array($q)) {
        $d[]=array((integer)round($r['c_MTTR']),(integer)round($r['MTTR']));
    }
    $dt[]=$d;unset($d);
    $series[]=array('label'=>'MTTR');
    //distribuzione delle età
    $q="SELECT count(*) as c_age, age FROM items_risk group by age";
    $q=db_query($q);
    $Y=format_date(time(),'custom','Y');
    while ($r=db_fetch_array($q)) {
        $d[]=array((integer)round($r['c_age']),(integer)round($Y-$r['age']));
    }
    $dt[]=$d;unset($d);
    $series[]=array('label'=>'età','yaxis'=>'y2axis');
    // distribuzione numero riparazioni
    $q="SELECT count(*) as c_maintenance, maintenance_count FROM items_risk group by maintenance_count";
     $q=db_query($q);
    while ($r=db_fetch_array($q)) {
        $d[]=array((integer)round($r['c_maintenance']),(integer)round($r['maintenance_count']));
    }
    $dt[]=$d;unset($d);
    $series[]=array('label'=>'riparazioni','yaxis'=>'y2axis');
    
//    //distribuzione media MTBF sulle calssi             
//    $q="SELECT count(*) as c_class, class_id FROM items_risk group by class_id";
//    $q=db_query($q);
//    while ($r=db_fetch_array($q)) {
//        $d[]=array((integer)round($r['c_MTBF']),(float)round($r['avg']),(float)round($r['stdv']),$r['class_id']);
//    }
    $data['plots'][$HTMLid]['data'] = $dt;
    $data['plots'][$HTMLid]['settings'] = array(
        'title' => 'Distribuzioni',
        'series'=>$series,
        'seriesDefaults'=>Array(
//            'linePattern'=> 'dashed',
//            'showMarker'=> false,
            'shadow'=> false,
            'rendererOptions'=>array('smooth'=> true)
        ),
//        'seriesDefaults'=>array(
//            'renderer'=> '$jq.jqplot.BubbleRenderer',
//            'rendererOptions'=> array(
//                'bubbleAlpha'=> 0.6,
//                'highlightAlpha'=> 0.8
//            ),
//            'shadow'=> true,
//            'shadowAlpha'=> 0.05
//        )
        'axes'=>array(
            'y2axis' => array(
                    'min'=>0,
                    'max'=> 200,
//                    'tickOptions' => array( 
//                        'formatString' => '%d&nb'
//                      ),                    
                    'label'=>'Et&agrave;<br/>Riparazioni'
                ), 
            'yaxis' => array(
                'tickOptions' => array( 
                        'formatString' => '%d&nbsp;gg'
                      ),
            ),
        ),
        'cursor'=> array(
//              'show'=> false,
              'zoom'=>true,
              'looseZoom'=> true
        ),
        'legend' => array('show' => true, 'location' => 'e', 'placement'=>'outsideGrid')
    );
    drupal_add_js($data,'setting');
}