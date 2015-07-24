<?php

// $Id$
include_once("admin_settings.php.inc");

function loadschemaCanone() {
    $schema['fees'] = array(
        'description' => t('Canoni manutenzione'),
        'fields' => array(
            'feeid' => array('type' => 'serial', 'not null' => TRUE,),
//            'feevid' => array('type' => 'serial', 'not null' => TRUE,),
            'APPA_CODI' => array('description' => 'id manutentore', 'type' => 'int', 'size' => 'big', 'not null' => FALSE, 'default' => 0),
            'ANNO' => array('description' => 'anno ascott', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'NUMERO_INTROITO' => array('description' => 'Introito Ascott', 'type' => 'int', 'size' => 'big', 'not null' => TRUE, 'default' => 0),
            'value' => array('description' => 'Prezzo di acquisto', 'type' => 'numeric', 'not null' => TRUE, 'precision' => '19', 'scale' => '2', 'default' => 0),
            'fee' => array('description' => 'Canone', 'type' => 'numeric', 'not null' => TRUE, 'precision' => '19', 'scale' => '2', 'default' => 0),
            'S_N' => array('description' => 'Serial Number', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE,),
            'class' => array('description' => 'CIVAB CLASS', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE,),
            'idcnd' => array('description' => 'Codice CND', 'type' => 'varchar', 'length' => '50', 'not null' => TRUE, 'default' => "XXXXXXXXXXXXXX"),
            'typeid' => array('description' => 'Tipo di canone', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'billed_begin' => array('description' => 'Inizio fatturazione', 'type' => 'int', 'not null' => FALSE),
            'billed_end' => array('description' => 'fine fatturazione', 'type' => 'int', 'not null' => FALSE),
            'created' => array('description' => 'ultimo aggiornamento', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'uid' => array('description' => 'utente', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
        ),
        'primary key' => array('feeid'),
        'unique keys' => array(
            'APPA_CODI_UK' => array('APPA_CODI'),
//            'inventairio'=>array('ANNO','NUMERO_INTROITO')
        ),
        'indexes' => array(
            'NUMERO_INTROITO' => array('ANNO', 'NUMERO_INTROITO'),
            'APPA_CODI' => array('APPA_CODI'),
            'S_N' => array('S_N'),
            'created' => array('created'),
        ),
    );
    $schema['fee_type'] = array(
        'description' => t('tipologia canone'),
        'fields' => array(
            'typeid' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
            'description' => array('description' => 'tipo di canone', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE,),
        ),
        'primary key' => array('typeid'),
    );
    $schema['systems'] = array(
        'description' => t('configurazione sistema'),
        'fields' => array(
            'systemid' => array('type' => 'serial', 'not null' => TRUE,),
            // 
            'parentid' => array('description' => 'riferimento al padre', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'feeid' => array('description' => 'riferimento al canone', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'level' => array('description' => 'riferimento al canone', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'SIST_CODI' => array('description' => 'Serial Number', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE,),
            'APPA_CODI' => array('description' => 'id manutentore', 'type' => 'int', 'size' => 'big', 'not null' => FALSE, 'default' => 0),
            'ANNO' => array('description' => 'anno ascott', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'NUMERO_INTROITO' => array('description' => 'Introito Ascott', 'type' => 'int', 'size' => 'big', 'not null' => TRUE, 'default' => 0),
            'S_N' => array('description' => 'Serial Number', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE,),
            'garantee' => array('description' => 'scadenza garanzia', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'created' => array('description' => 'ultimo aggiornamento', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'uid' => array('description' => 'utente', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
        ),
        'primary key' => array('systemid'),
        'unique keys' => array(
            'APPA_CODI_UK' => array('APPA_CODI'),
        ),
        'indexes' => array(
            'NUMERO_INTROITO' => array('ANNO', 'NUMERO_INTROITO'),
            'APPA_CODI' => array('APPA_CODI'),
            'S_N' => array('S_N'),
            'SIST_CODI' => array('SIST_CODI'),
            'created' => array('created'),
        ),
    );
    return $schema;
}

function init_data() {
    $return = array();
    $schema = loadschemaCanone();
    foreach ($schema as $name => $table) {
        if (!db_table_exists($name))
            db_create_table($return, $name, $table);
    }
    cron_update_list();
//    db_query("INSERT INTO {fee_type} ( typeid, description) VALUES ( 0, 'Non assegnato')");
//    db_query("INSERT INTO {fee_type} ( typeid, description) VALUES ( 1, 'Manutenzione diretta (MNADIR)')");
//    db_query("INSERT INTO {fee_type} ( typeid, description) VALUES ( 2, 'Gestione chiamata (MANCOST)')");

    return $return;
}

function destroy_data() {
    $return = array();
    $schema = loadschemaCanone();
    foreach ($schema as $name => $data) {
        db_drop_table($return, $name);
    }
}

//entry poin per la generazione delle form dei singoli canoni
function fee_edit_form(&$form_state, $fee, $only_one = TRUE) {
    $form = fee_form($fee);
    $form['#submit'][] = 'fee_edit_form_submit';
    $form['salva'] = array(
        '#title' => 'salva',
        '#type' => 'submit',
        '#value' => 'salva',
    );
    $form['annulla'] = array(
        '#title' => 'salva',
        '#value' => l('annulla', 'tested/canone'),
    );
    $form['#redirect'] = 'tested/canone';
    return $form;
}

//form per il singolo canone
function fee_form($fee) {
    $fee = get_last_fee($fee); // recupera l'ultimo canone
    $form['value'] = array(
        '#title' => 'Prezzo di acquisto (&euro;)',
        '#type' => 'textfield',
        '#default_value' => isset($fee['value']) ? $fee['value'] : 0,
        '#attributes' => array('class' => 'number'),
        '#element_validate' => array('value_validate'),
    );
    $form['fee'] = array(
        '#title' => 'Canone (&euro;)',
        '#type' => 'textfield',
        '#default_value' => isset($fee['fee']) ? $fee['fee'] : 0,
        '#attributes' => array('class' => 'number'),
        '#element_validate' => array('value_validate', 'coerence_validation'),
        '#description' => 'Impostare a 0 se si lo si vuole calcolato con la percentuale'
//        '#suffix'=>'&euro;'
    );
//TODO eventuale calcolo automatico della percentuale
    $form['fee_percent'] = array(
        '#title' => 'percentuale del valore',
        '#type' => 'textfield',
        '#default_value' => 0,
        '#attributes' => array('class' => 'number'),
        '#element_validate' => array('value_validate', 'coerence_validation'),
        '#description' => 'Percentuale attualmente calcolata '
        . '<span id=fee-' . $fee['feeid'] . '>' . 100 * $fee['fee'] / $fee['value'] . '</span>%'
        . '. Impostare a 0 se si vuole lasciare un canone impostato a mano.',
    );
    $form['billed_begin'] = array(
        '#title' => 'Inizio canone',
        '#type' => 'date',
        '#default_value' => timeToDateArray((isset($fee['billed_begin']) ) ? $fee['billed_begin'] : 0),
    );
    $form['billed_end'] = array(
        '#title' => 'fine canone',
        '#type' => 'date',
        '#default_value' => timeToDateArray((isset($fee['billed_end']) ) ? $fee['billed_end'] : 0),
    );
    $form['APPA_CODI'] = array(
        '#type' => 'hidden',
        '#value' => (isset($fee['APPA_CODI']) ? $fee['APPA_CODI'] : 0),
    );
    $form['feeid'] = array(
        '#type' => 'hidden',
        '#value' => (isset($fee['feeid']) ? $fee['feeid'] : 0),
    );
    $form['ANNO'] = array(
        '#type' => 'hidden',
        '#value' => (isset($fee['ANNO']) ? $fee['ANNO'] : format_date(time(), 'custom', 'Y')),
    );
    $form['NUMERO_INTROITO'] = array(
        '#type' => 'hidden',
        '#value' => (isset($fee['NUMERO_INTROITO']) ? $fee['NUMERO_INTROITO'] : 0),
    );

    $form['typeid'] = array(
        '#title' => 'Tipo di canone',
        '#type' => 'select',
        '#options' => get_typeid(),
        '#default_value' => isset($fee['typeid']) ? $fee['typeid'] : 0
    );


//    if (user_access('_ADMIN_MAINTENANCE')) {
//        /*
//          TODO: edit dei campi di amministrazione
//          'class' => array('description' => 'CIVAB CLASS', 'type' => 'varchar', 'length' => '50', 'not null' => FALSE, ),
//          'idcnd' => array('description' => 'Codice CND', 'type' => 'varchar', 'length' => '50', 'not null' => TRUE, 'default' => 'XXXXXXXXXXXXXX'),
//          'typeid' => array('description' => 'Tipo di canone', 'type' => 'int', 'not null' => FALSE, 'default' => 0),
//         *
//         */
//    }
    return $form;
}

function get_typeid($id = NULL) {

    $q = db_query("SELECT * FROM {fee_type}");
    while ($r = db_fetch_array($q)) {
        $ret[$r['typeid']] = $r['description'];
    }
    if (isset($ret)) {
        if ($id === NULL)
            return $ret;
        return $ret[$id];
    }
    return NULL;
}

/**
 * registra dati inseriti
 * @param type $form form generata
 * @param type $form_state risposta dati
 */
function fee_edit_form_submit($form, &$form_state) {
    global $user;
    $values = $form_state['values'];
    $op = $form_state['op'];
    $last_fee = get_last_fee($values);
    $values['created'] = time();
    $values['uid'] = $user->uid;
    //ricalcolo delle percentuali
    if ($values['fee_percent'] == 0)
        $values['fee_percent'] = $values['fee'] / $values['value'];
    else {
        $values['fee_percent'] = $values['fee_percent'] / 100; //rinormalizza
        if ($values['fee'] == 0)
            $values['fee'] = $values['fee_percent'] * $values['value'];
    }
    // conversione delle date
    $values['billed_begin'] = dateArrayToTime($values['billed_begin']);
    $values['billed_end'] = dateArrayToTime($values['billed_end']);
    //se la data di fine e precedente all'inizio la data non è impostata
    if ($values['billed_end'] <= $values['billed_begin'])
        unset($values['billed_end']);

    $fee = (object) $values;
    my_canone_write_record('fees', $fee, 'feeid');
}

/**
 * ritorna l'ultimo canone definito
 * @param array $fee riferimento di ricerca (solo APPA_CODI o introito)
 * @return array ultimo canone o falso se non trova nulla.
 */
function get_last_fee($fee) {
    if (!is_array($fee) && is_numeric($fee)) { // selezione da ultimo id
        $where = ' feeid = ' . $fee . ' ';
    } else if ($fee['APPA_CODI']) {
        $where = ' APPA_CODI = ' . $fee['APPA_CODI'] . ' ';
        $group = 'GROUP BY APPA_CODI';
    } else if ($fee['ANNO'] && $fee['NUMERO_INTROITO']) {
        $where = ' ANNO = ' . $fee['ANNO'] . 'AND  NUMERO_INTROITO = ' . $fee['NUMERO_INTROITO'] . ' ';
        $group = 'GROUP BY ANNO ,NUMERO_INTROITO';
    }
    else
        return FALSE;
    // recupera data massima per quel costo
    $feeid = db_result(db_query("SELECT max(feeid) FROM {fees} WHERE $where $group"));
    $qy = db_query("SELECT * FROM {fees} WHERE feeid = $feeid ");
    // recupera ultimo record analogo inserito
    if ($f = db_fetch_array($qy)) {
        return $f;
    }
    return FALSE;
}

/**
 * visualizza l'elenco dei canoni stabiliti
 * @param string $filter clausola where completa di where
 * @return html table
 */
function tabella_canone($filter = NULL) {
    if ($filter) {// nessun filtro
//TODO: gestione del filtro
    }
    $schema = loadschemaCanone();
    $fields = 'f.feeid,f.APPA_CODI,f.ANNO,f.NUMERO_INTROITO,f.S_N,f.value,f.fee,f.class,t.description,f.billed_begin,f.billed_end';
    //creazione degli header
    $f = explode(',', $fields);
    $tables = array('t' => 'fee_type', 'f' => 'fees');
    foreach ($f as $field) {
        list($t, $field) = explode('.', $field);
        $header[] = array(
            'data' => isset($schema[$tables[$t]]['fields'][$field]['description']) ? $schema[$tables[$t]]['fields'][$field]['description'] : $field,
            'field' => $field
        );
    }
    $q = "SELECT $fields FROM {fees} f "
            . "LEFT JOIN {fee_type} t  ON t.typeid=f.typeid" . $filter . tablesort_sql($header);
    $q_c = "SELECT COUNT(*) FROM {fees} " . $filter;
    $q = pager_query($q, 100, 0, $q_c);

    $value = 0;
    $fee = 0;
    while ($r = db_fetch_array($q)) {
        $value+=$r['value'];
        $fee+=$r['fee'];
        $r['value'] = array(
            'data' => currencyFormat($r['value']),
            'style' => 'text-align: right;');
        $r['fee'] = array(
            'data' => currencyFormat($r['fee']),
            'style' => 'text-align: right;');
        $r['APPA_CODI'] = l($r['APPA_CODI'], 'tested/canone/edit/' . $r['feeid']);
        if (isset($r['billed_begin']) && $r['billed_begin'])
            $r['billed_begin'] = format_date($r['billed_begin'], 'custom', 'd/m/Y');
        if (isset($r['billed_end']) && $r['billed_end'])
            $r['billed_end'] = format_date($r['billed_end'], 'custom', 'd/m/Y');
        $rows[] = $r;
    }

    if (isset($rows)) {
        // ricostruzione delle intestazioni
        $value_TOT = db_result(db_query("SELECT SUM(value) FROM {fees}"));
        $fee_TOT = db_result(db_query("SELECT SUM(fee) FROM {fees}"));
        $rows[] = array(
            array('data' => 'Totale pagina', 'colspan' => 4),
            array('data' => currencyFormat($value), 'style' => 'text-align: right;'),
            array('data' => currencyFormat($fee), 'style' => 'text-align: right;'),
            array('data' => '', 'colspan' => 5)
        );
        $rows[] = array(
            array('data' => 'Totale generale', 'colspan' => 4),
            array('data' => currencyFormat($value_TOT), 'style' => 'text-align: right;'),
            array('data' => currencyFormat($fee_TOT), 'style' => 'text-align: right;'), array('data' => '', 'colspan' => 5)
        );
        return theme_pager() . '<br/>' . theme('table', $header, $rows) . '<br/>' . theme_pager();
    } else {
        //non ci sono dati creazione del DB
        return l('crea canoni', 'tested/canone/create');
    }
}

//funzione provvisoria per la scrittura sulle tabelle
function my_canone_write_record($table, &$object, $update = array()) {
    // Standardize $update to an array.
    if (is_string($update)) {
        $update = array($update);
    }

    $schema = loadschemaCanone($table);
    $schema = $schema[$table];
    if (empty($schema)) {
        return FALSE;
    }

    // Convert to an object if needed.
    if (is_array($object)) {
        $object = (object) $object;
        $array = TRUE;
    } else {
        $array = FALSE;
    }

    $fields = $defs = $values = $serials = $placeholders = array();

    // Go through our schema, build SQL, and when inserting, fill in defaults for
    // fields that are not set.
    foreach ($schema['fields'] as $field => $info) {
        // Special case -- skip serial types if we are updating.
        if ($info['type'] == 'serial' && count($update)) {
            continue;
        }

        // For inserts, populate defaults from Schema if not already provided
        if (!isset($object->$field) && !count($update) && isset($info['default'])) {
            $object->$field = $info['default'];
        }

        // Track serial fields so we can helpfully populate them after the query.
        if ($info['type'] == 'serial') {
            $serials[] = $field;
            // Ignore values for serials when inserting data. Unsupported.
            unset($object->$field);
        }

        // Build arrays for the fields, placeholders, and values in our query.
        if (isset($object->$field)) {
            $fields[] = $field;
            $placeholders[] = db_type_placeholder($info['type']);

            if (empty($info['serialize'])) {
                $values[] = $object->$field;
            } else {
                $values[] = serialize($object->$field);
            }
        }
    }

    // Build the SQL.
    $query = '';
    if (!count($update)) {
        $query = "INSERT INTO `{" . $table . "}` (" . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $return = SAVED_NEW;
    } else {
        $query = '';
        foreach ($fields as $id => $field) {
            if ($query) {
                $query .= ', ';
            }
            $query .= '`' . $field . '`' . ' = ' . $placeholders[$id];
        }

        foreach ($update as $key) {
            $conditions[] = "$key = " . db_type_placeholder($schema['fields'][$key]['type']);
            $values[] = $object->$key;
        }

        $query = "UPDATE `{" . $table . "}` SET $query WHERE " . implode(' AND ', $conditions);
        $return = SAVED_UPDATED;
    }

    // Execute the SQL.
    if (db_query($query, $values)) {
        if ($serials) {
            // Get last insert ids and fill them in.
            foreach ($serials as $field) {
                $object->$field = db_last_insert_id($table, $field);
            }
        }
    } else {
        $return = FALSE;
    }

    // If we began with an array, convert back so we don't surprise the caller.
    if ($array) {
        $object = (array) $object;
    }

    return $return;
}

/*
  aggiorna gli elenchi delle attrezzature
  ricerca tutte le attrezzature in si3c
  verifica che non siano già ineseite nell'elenco
  se non lo sono le inserisce
 */

function cron_update_list() {
    //data ultimo aggiornamento canone salvata
    $last_update = variable_get('canone_list_last_update', 0);
    $N_MAX = 1000; //massimo numero di record caricati a ciclo
    $conn = si3c_connect();
    $dd = gmdate('Y.m.d', $last_update);
    $qy = "SELECT a.* ,
             TO_CHAR(APPA_DATA_UAGG,'YYYY-MM-DD HH24.MI.SS') AS last_update
             FROM SI3C.T_APPA a
             WHERE APPA_CANC = 'N' AND APPA_DATA_UAGG >= TO_DATE('$dd 00.00.01','YYYY-MM-DD HH24.MI.SS')
             ORDER BY APPA_DATA_UAGG ASC";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    //verifica ogni richiesta di intervento a partire dall'ultima data 
    while (($r = oci_fetch_array($qy) )&& $N_MAX--) {
        //aggiorna dati sistema
        updateANDcreate_system($r);
        //salva la data dell'aggiornamento corrente 
        variable_set('canone_list_last_update', strtotime($r['last_update']));
    }
}

/**
 * cerca l'apparecchiatura nel DB
 * @param array $item_data riga di T_APPA
 * @return record di systems o falso se non trovato
 */
function find_item($item_data) {
    $q = "SELECT * FROM {systems} WHERE APPA_CODI=%d ";
    $q = db_query($q, $item_data['APPA_CODI']);
    if ($sys = db_fetch_array($q))
        return $sys;
    return FALSE;
}

/**
 * crea la voce di sistema se non c'è
 * @param type $item_data
 * @return boolean
 */
function updateANDcreate_system($item_data) {
    global $user;
    $first = TRUE;
    $conn = si3c_connect();
    
    $qy = "SELECT a.* ,
             TO_CHAR(APPA_FINE_GARZ,'YYYY-MM-DD HH24.MI.SS') AS garantee
             FROM SI3C.T_APPA a 
             WHERE APPA_CANC = 'N' AND APPA_CODI = ".$item_data['APPA_CODI'].(($item_data['SIST_CODI']!==NULL) ?" OR SIST_CODI = " . $item_data['SIST_CODI']:'') .
            " ORDER BY APPA_PREZ_ACQU DESC";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    while ($r = oci_fetch_array($qy)) {
        $r = (object) $r;
        if (strlen($r->APPA_CODI_INVE) >= 6) { // 4 cifre anno + / + inventario
            $in = explode('/', $r->APPA_CODI_INVE);
            $r->ANNO = $in[0];
            $r->NUMERO_INTROITO = $in[1];
        }
        if (!$r->garantee) {
            $d = new DateTime($r->garantee . ' UTC');
            $r->garantee = $d->format('U');
        }
        $r->class = $r->CLAP_CODI;
        $r->created = time();
        $r->uid = $user->uid;
        $r->S_N = $r->APPA_CODI_MATR;
        //per i figli i campi sono già pronti
        if (isset($feeid))
            $r->feeid = $feeid;
        if (isset($systemid))
            $r->systemid = $systemid;
        if (find_item((array) $r))
        // se esiste già aggiorna la voce
            my_canone_write_record('systems', $r, array('APPA_CODI'));
        else
        //nuova voce
            my_canone_write_record('systems', $r);
        //inserimento voce principale (padre)
        if ($first) {
            $systemid = $r->sistemid;
            $q = "SELECT feeid FROM {fees} WHERE APPA_CODI=%d";
            $q = db_query($q, $r->APPA_CODI);
            // verifica se è presente nei canoni altrimenti lo aggiunge
            if (!$feeid = db_result($q)) {
                my_canone_write_record('fees', $r);
            }
            $feeid = $r->feeid;
            my_canone_write_record('systems', $r, array('systemid')); //aggiorna feeid del sistema
            $first = FALSE; //il primo è già inserito
        }
    }
    if (isset($systemid))
        return $systemid;
    else
        return FALSE;
}

function value_validate($element, &$form_state) {
    $value = $element['#value'];
    $match = preg_match("/^([0-9]+)(,[0-9][0-9][0-9])*([.][0-9]){0,1}([0-9]*)$/", $value, $m);
    if (!$match) {
        form_error($element, 'errore nel numero inserito');
    }
}

function coerence_validation($element, &$form_state) {
    $values = $form_state['values'];
    if ($values['fee_percent'] == 0 && $values['fee'] == 0) {
        form_error($element, 'Va impostato un canone o una percentuale');
    }
    if ($values['fee_percent'] != 0 && $values['fee'] != 0) {
        form_error($element, 'Va lasciato a zero un canone o una percentuale');
    }
}
