<?php

include_once 'si3c_lnk.php.inc';

/**
 * verifica se la giornata è festiva
 * @param type $date data da verificare (in secondi)
 * @return boolean vero se è festivo
 */
function is_holiday($date) {
    $Y = gmdate('Y', $date); // anno per il calcolo di pasqua
    $date = gmdate('j-n', $date); //elimina ore minuti e secondi
    switch ($date) {
        case '1-1': //capodanno
        case '6-1': //epifania
        case '24-4': //liberazione
        case '1-5': //festa del lavoro
        case '2-6': //festa della repubblica
        case '12-7': //patrono
        case '15-8': //ferragosto
        case '1-11': //tutti i santi
        case '8-12': //assunzione
        case '25-12': //natale
        case '26-12': //s.stefano
            return TRUE;
    }
    $e = easter_day($Y);
    if ($date == ($e['d'] . '-' . $e['m']))
        return TRUE; //pasqua
    if ($date == (($e['d'] + 1) . '-' . $e['m']))
        return TRUE; //pasquetta

    return FALSE;
}

/**
 * calcolo della pasqua 
 */
function easter_day($Y) {
    static $easter = array(); //variabile statica per accelerare l'esecuzione
    if (isset($easter[$Y]))
        return $easter[$Y];
    //algoritmo di oudin-tondering
    $g = $Y % 19;
    $c = $Y % 100;
    $h = (int) ($c - (int) ($c / 4) - (int) ((8 * $c + 13) / 25) + 19 * $g + 15) % 30;
    $i = (int) $h - (int) ($h / 28) * (1 - ((int) (29 / ($h + 1))) * ((int) (21 - $g) / 11));
    $j = (int) ($Y + (int) ($Y / 4) + $i + 2 - $c + (int) ($c / 4)) % 7;
    $l = $i - $j;
    $mm = 3 + (int) (($l + 40) / 44);
    $dd = $l + 28 - 31 * (int) ($mm / 4);
    $easter[$Y] = array('m' => $mm, 'd' => $dd);
    return $easter[$Y];
}

/**
 * calcola il tempo di guasto in ore lavorative
 * @param int $from_date data e ora inizio
 * @param int $to_date data ora fine
 * @return int durata in secondi
 */
function failure_work_time($from_date, $to_date, $profile_time) {
    $time = 0;
    // sanity checks
    if (!$profile_time || !isset($profile_time) || !is_array($profile_time) || count($profile_time) <> 7)
        return 0;
    if ($from_date > $to_date)
        return 0; //date incompatibili
    while (is_holiday($from_date) && $from_date < $to_date)
        $from_date = ((int) ($from_date / 86400) + 1) * 86400; //sposta in avanti di un giorno l'inizio se è festivo
    while (is_holiday($to_date) && $from_date < $to_date)
        $to_date = ((int) ($to_date / 86400) - 1) * 86400; //sposta a ritroso la data di arrivo se è festiva


    $from_wday = $profile_time[gmdate('w', $from_date)]['start']; // orario minimo di inizio
    $to_wday = $profile_time[gmdate('w', $to_date)]['end']; //orario massimo di fine
//normalizzzione intervalli
    //normalizzo ora d'inizio
    if ($from_date % 86400 > $from_wday) {  //ora inizio successiva inizio attività lavorativa
        if ($from_date % 86400 > $to_wday) { //ora inizio successiva fine attività lavorativa
            // la riporto al giorno dopo e azzero lo scarto
            $from_date = ((int) ($from_date / 86400) + 1) * 86400 + $from_wday;
            $h_start = $from_wday;
        } else//viceversa l'attività inizia all'interno dell'orario calcolo lo scarto
            $h_start = $from_date % 86400;
    }
    else {// l'attività inizia prima dell'orario la riporto all'inizio
        $from_date = (int) ($from_date / 86400) * 86400 + $from_wday;
        $h_start = $from_wday;
    }
    //normalizzo ora fine
    if ($to_date % 86400 < $to_wday) {//ora fine precedente ora fine attività lavorativa
        if ($to_date % 86400 < $from_wday) { //ora fine precedente ora inizio attività laorativa
            //la riporto al giorno successivo a fine attività
            $to_date = ((int) ($to_date / 86400) - 1) + $to_wday;
            $h_end = $to_wday;
        } else
            $h_end = $to_date % 86400; //l'attività è finita all'interno dell'orario calcolo lo scarto
    }
    else {
        // l'attività è finita dopo l'orario di lavoro la riporto alla fine
        $to_date = (int) ($to_date / 86400) + $to_wday;
        $h_end = $to_wday;
    }
    if ($from_date > $to_date)
        return 0; //se le date sono invertite l'attività è fuori orario

    $days = (int) ($to_date / 86400) - (int) ($from_date / 86400);
    if ($days > 0) {//la durata è su più giorni
        $time = $h_end - $from_wday + $to_wday - $h_start; //conteggio residui giorni di testa e coda
        for ($i = 1; $i < $days; $i++) {
            // calcolo dei giorni intermedi
            if (!is_holiday($from_date + ($i * 86400))) {
                $time+=$profile_time[gmdate('w', $from_date + ($i * 86400))]['end'] -
                        $profile_time[gmdate('w', $from_date + ($i * 86400))]['start'];
            }
        }
    } else //la durata è sullo stesso giorno
        $time = $h_end - $h_start;

    return $time;
}

function loadschemaControllo() {
    $schema['fails'] = array(
        'description' => t('tempistiche'),
        'fields' => array(
            'failid' => array('type' => 'serial', 'not null' => TRUE,),
            'APPA_CODI' => array('description' => 'id manutentore', 'type' => 'int', 'size' => 'big', 'not null' => FALSE, 'default' => 0),
            'ANNO' => array('description' => 'anno ascott', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'NUMERO_INTROITO' => array('description' => 'Introito Ascott', 'type' => 'int', 'size' => 'big', 'not null' => TRUE, 'default' => 0),
            'RIIN_CODI' => array('description' => 'codice richiesta', 'type' => 'int', 'size' => 'big', 'not null' => FALSE, 'default' => 0),
            'INTE_CODI' => array('description' => 'codice intervento', 'type' => 'int', 'size' => 'big', 'not null' => FALSE, 'default' => 0),
            'blocked' => array('description' => 'Intervento blocccante', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'category' => array('description' => 'Categoria attrezzatura', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'request_date' => array('description' => 'Data richiesta', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'start_date' => array('description' => 'Inizo attività', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'end_date' => array('description' => 'Fine attività', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'end_request' => array('description' => 'Fine richiesta', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'request_time_offer' => array('description' => 'Tempo intervento', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'fail_time_offer' => array('description' => 'Durata intervento', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'request_time_STD' => array('description' => 'Tempo intervento standard', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'fail_time_STD' => array('description' => 'Durata intervento standard', 'type' => 'int', 'not null' => TRUE, 'unsigned' => TRUE, 'default' => 0),
            'is_close' => array('description' => 'aperta', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            //intervento in ritardo approvato se approved <> 0 con le note
            'approved' => array('description' => 'Approvato', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'notes' => array('description' => 'Note approvazione', 'type' => 'varchar', 'length' => '500', 'not null' => FALSE, 'default' => "(none)"),
            // note di registrazone aggregate a ogni refresh 
            'system_notes' => array('description' => 'Note di sistema', 'type' => 'varchar', 'length' => '500', 'not null' => FALSE, 'default' => "(none)"),
            'DB_update' => array('description' => 'last update DB date', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'changed' => array('description' => 'ultimo aggiornamento', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
            'uid' => array('description' => 'utente', 'type' => 'int', 'not null' => TRUE, 'default' => 0),
        ),
        'primary key' => array('failid'),
        'indexes' => array(
            'NUMERO_INTROITO' => array('ANNO', 'NUMERO_INTROITO'),
            'APPA_CODI' => array('APPA_CODI'),
            'RIIN_CODI' => array('RIIN_CODI'),
            'INTE_CODI' => array('INTE_CODI'),
            'changed' => array('changed'),
        ),
    );
    return $schema;
}

//
function get_data($start_date = '2014-07-01') {
    global $user; //, $STD_profile_time, $offer_profile_time;
// profili temporali lavorativi
    $offer_profile_time = variable_get('offer_profile_time', null);
    $STD_profile_time = variable_get('STD_profile_time', null);
    $last_update = variable_get('fails_last_update', strtotime('2014.07.01 00:00:01'));

    /*
     * giorno per giorno carica gli interventi fino a n_max
     * per ogni intervento calcola le tempistiche
     * se intervento aperto calcola a data odierna
     * terminati n_max il ciclo successivo riparte dall'intervento aperto con data più bassa
     * 
     *  i dati vengono registrati solo finchè le richieste o gli interventi non sono chiusi
     *  dopo di che c'è qualcosa che non va. Is_open è 3 per gli interventi chiusu correttamente
     *  è 1 per la fase transitoria e 2 è una condizione di anomalia
     */
    $N_MAX = 10000;

    //* terminati n_max il ciclo successivo riparte dall'intervento aperto con data più bassa
    $qy = db_query("SELECT MIN(request_date) FROM {fails} WHERE is_close <> 0");
    if ($date = db_result($qy)) {
        // recupera la data della richiesta aperta più vecchia    
        $start_date = gmdate('Y-m-d', max($date, strtotime($start_date)));
    }
    //* per ogni intervento calcola le tempistiche
    $conn = si3c_connect();
    for ($day = (int) (strtotime($start_date) / 86400) + 1; $day <= ((int) (time() / 86400)) && $N_MAX > 0; $day++) {

        $dd = gmdate('Y-m-d', $day * 86400);
        $qy = "SELECT r.RIIN_CODI, RIIN_STAT, INTE_STAT, i.INTE_CODI, r.APPA_CODI,
                                i.INTE_GDIS_INIZ,i.CECO_CODI,i.APPA_UBIC_CODI," .
                " TO_CHAR(INTE_DATA_INIZ,'YYYY-MM-DD HH24.MI.SS') AS INTE_DATA_INIZ,
                                TO_CHAR(INTE_DATA_FINE,'YYYY-MM-DD HH24.MI.SS') AS INTE_DATA_FINE,
                                TO_CHAR(RIIN_DATA_FINE,'YYYY-MM-DD HH24.MI.SS') AS RIIN_DATA_FINE,
                                TO_CHAR(RIIN_DATA_RICE,'YYYY-MM-DD HH24.MI.SS') AS RIIN_DATA_RICE, 
                                TO_CHAR(RIIN_DATA_UAGG,'YYYY-MM-DD HH24.MI.SS') AS RIIN_DATA_UAGG,
                                TO_CHAR(INTE_DATA_UAGG,'YYYY-MM-DD HH24.MI.SS') AS INTE_DATA_UAGG " .
                "FROM SI3C.T_RIIN r
                         LEFT JOIN SI3C.T_INTE i ON i.RIIN_CODI=r.RIIN_CODI AND i.INTE_CANC = 'N'
                         WHERE r.RIIN_DATA_RICE>= TO_DATE('$dd 00.00.01','YYYY-MM-DD HH24.MI.SS') 
                             AND r.RIIN_DATA_RICE<= TO_DATE('$dd 23.59.59','YYYY-MM-DD HH24.MI.SS') 
                             AND r.RIIN_CANC = 'N' ORDER BY r.RIIN_DATA_RICE ASC 
                         ";
        $qy = oci_parse($conn, $qy);
        oci_execute($qy, OCI_DEFAULT);

        //verifica ogni richiesta di intervento a partire dall'ultima data 
        while ($r = oci_fetch_array($qy)) {
            unset($failid); // cancella eventuali precedenti
            unset($system_notes);
            //verifiche per i campi ancora vuoti

            $tmp = new DateTime($r['RIIN_DATA_RICE'] . ' UTC');
            $request_date = $tmp->format('U');
            $tmp = is_null($r['INTE_DATA_INIZ']) ? new DateTime() : new DateTime($r['INTE_DATA_INIZ'] . ' UTC');
            $start_date = $tmp->format('U');
            $tmp = is_null($r['INTE_DATA_FINE']) ? new DateTime() : new DateTime($r['INTE_DATA_FINE'] . ' UTC');
            $end_date = $tmp->format('U');
            $tmp = is_null($r['RIIN_DATA_FINE']) ? new DateTime() : new DateTime($r['RIIN_DATA_FINE'] . ' UTC');
            $end_request = $tmp->format('U');

            // lo stato corretto delle richieste è che siano entrambe chiuse
            $is_close = ($r['RIIN_STAT'] == 'C') + ($r['INTE_STAT'] == 'C') * 2;

            $t1 = new DateTime($r['RIIN_DATA_UAGG'] . ' UTC');
            $t2 = new DateTime($r['INTE_DATA_UAGG'] . ' UTC');
            $DB_update = max($t1->format('U'), $t2->format('U'));

            //verifica precedenti controllando la presenza di RIIN_CODI 
            $q = "SELECT * FROM {fails} WHERE RIIN_CODI=%d";
            $q = db_query($q, $r['RIIN_CODI']);
            if ($f = db_fetch_object($q)) {
                /*
                 * il record esiste 
                 */

                if ($f->is_close == 1) {
                    //c'è qualcosa da controllare, in un giro precedente la richiesta è stata chiusa ma l'intervento era aperto      
                    $system_notes.=gmdate('Y-m-d h.i') . ' ERRORE! richiesta chiusa ma intervento aperto<br/>';
                } elseif ($f->is_close == 2) {
                    $system_notes.=gmdate('Y-m-d h.i') . ' ritardo nella chiusura della richiesta aperta ma intervento chiuso<br/>';
                } elseif ($f->is_close == 3) {
                    if ($f->DB_update < $DB_update) {
                        watchdog('tested', gmdate('Y-m-d h.i') . ' Attenzione! sono state effettuate variazioni successive alla chiusura su intervento nr.' . $f->failid . "(RIN_CODI:" . $r['RIIN_CODI'] . ")", NULL, WATCHDOG_WARNING);
                    }
                    if ($is_close <> $f->is_close) {
                        watchdog('tested', gmdate('Y-m-d h.i') . " Attenzione! lo stato dell'intervento nr.$f->failid (RIN_CODI:" . $r['RIIN_CODI'] . ") &egrave; variato rispetto all'ultima rilevazione su intervento", NULL, WATCHDOG_WARNING);
                    }
//TODO: controllare anche le date ed eventualmente aggiornare il log della richiesta                    
                    continue; //richiesta chiusa e registrata salta alla prossima senza aggiornare nulla
                } else {
                    $system_notes = $f->system_notes;
//TODO: effettuare eventualmente i controlli delle variazioni occorse  rispetto a quanto già registrato e tracciarle nelle note
                    $failid = $f->failid; //recupera la chiave per l'aggiornamento del record
                }
            }

//controlli di coerenza
            if ($end_date > $end_request)
                $system_notes.=gmdate('Y-m-d h.i') . ' ERRORE! Data fine intervento successiva alla chiusura della richiesta<br/>';
            if ($start_date < $request_date)
                $system_notes.=gmdate('Y-m-d h.i') . ' ERRORE! Data inizio intervento precedente alla ricezione della richiesta<br/>';

            $fail = array(
                'APPA_CODI' => $r['APPA_CODI'],
//            'ANNO' => $r['RIIN_DATA_INIZ']            // i dati diinventario non sono su queste tabelle
//            'NUMERO_INTROITO' => $r['RIIN_DATA_INIZ']
                'RIIN_CODI' => $r['RIIN_CODI'],
                'INTE_CODI' => is_null($r['INTE_CODI']) ? 0 : $r['INTE_CODI'],
//TODO: implementare le informazioni bloccanti e di categoria dell'intervento                
                'blocked' => $r['INTE_GDIS_INIZ'],
                'category' => 0,
                'request_date' => $request_date,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'end_request' => $end_request,
                'request_time_offer' => failure_work_time($request_date, $start_date, $offer_profile_time),
                'fail_time_offer' => failure_work_time($request_date, max($end_date, $end_request), $offer_profile_time),
//            'request_time_CSA' => 
//            'fail_time_CSA' => 
                'request_time_STD' => failure_work_time($request_date, $start_date, $STD_profile_time),
                'fail_time_STD' => failure_work_time($request_date, max($end_date, $end_request), $STD_profile_time),
                'is_close' => $is_close,
                // registra l'ultimo aggiornamente dei dati sulle tabelle del sistema
//                'DB_update' => max(date_timestamp_get(date_create($r['RIIN_DATA_UAGG'])), date_timestamp_get(date_create($r['INTE_DATA_UAGG']))),
                'DB_update' => $DB_update,
                'changed' => time(),
                'uid' => $user->uid
            );

            // registrazione in DB
            if (isset($failid)) {
                //update record in db
                $fail['failid'] = $failid;
                $log = my_drupal_write_record('fails', $fail, array('failid'));
            } else {
                //inserisce nuovo record nel db
                $log = my_drupal_write_record('fails', $fail);
            }

            if (!$log)
                watchdog('module', 'Errore nella registrazione dell\'intervento ' . $r['RIIN_CODI']);
            $N_MAX--;
        }
        if (($day + 1) >= ((int) (time() / 86400))) {
            // resetta la data se il ciclo sfonda la data odierna
            variable_del('fails_last_update');
        }
        if ($N_MAX <= 0) { //salva la data dell'ultimo conteggio
            variable_set('fails_last_update', $day * 86400);
        }
    }
}

function controllo() {
//parametri limite per le penali
    $STD_limits = array(
        1 => array(
            'blocked' => array('request' => 3600, 'fail' => 8 * 3600),
            'unblocked' => array('request' => 4 * 3600, 'fail' => 32 * 3600)
        ),
        2 => array(
            'blocked' => array('request' => 2 * 3600, 'fail' => 16 * 3600),
            'unblocked' => array('request' => 6 * 3600, 'fail' => 24 * 3600)
        ),
        3 => array(
            'blocked' => array('request' => 4 * 3600, 'fail' => 8 * 3600),
            'unblocked' => array('request' => 8 * 3600, 'fail' => 24 * 3600)
        ),
        4 => array(
            'blocked' => array('request' => 3600, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 3600, 'fail' => 16 * 3600)
        ),
        5 => array(
            'blocked' => array('request' => 8 * 3600, 'fail' => 16 * 3600),
            'unblocked' => array('request' => 24 * 3600, 'fail' => 60 * 3600)
        ),
    );

    $offer_limits = array(
        1 => array(
            'blocked' => array('request' => 15 * 60, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 30 * 60, 'fail' => 8 * 3600)),
        2 => array(
            'blocked' => array('request' => 30 * 60, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 3600, 'fail' => 8 * 3600)),
        3 => array(
            'blocked' => array('request' => 3600, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 2 * 3600, 'fail' => 16 * 3600)),
        4 => array(
            'blocked' => array('request' => 15 * 60, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 30 * 60, 'fail' => 8 * 3600)),
        5 => array(
            'blocked' => array('request' => 2 * 3600, 'fail' => 4 * 3600),
            'unblocked' => array('request' => 2 * 3600, 'fail' => 16 * 3600)),
    );

    // profili temporali lavorativi
    $offer_profile_time = array(
        array('start' => 0, 'end' => 0), //sunday
        array('start' => 7 * 3600, 'end' => 19 * 3600), //monday 7:00 - 19:00
        array('start' => 7 * 3600, 'end' => 19 * 3600), //tuesday 
        array('start' => 7 * 3600, 'end' => 19 * 3600), //wednesday
        array('start' => 7 * 3600, 'end' => 19 * 3600), //thursday
        array('start' => 7 * 3600, 'end' => 19 * 3600), //friday
        array('start' => 7 * 3600, 'end' => 12 * 3600), //saturday 7:00 - 12:00
    );

    $STD_profile_time = array(
        array('start' => 0, 'end' => 0), //sunday
        array('start' => 8 * 3600, 'end' => 17 * 3600), //monday 8:00 - 17:00
        array('start' => 8 * 3600, 'end' => 17 * 3600), //tuesday 
        array('start' => 8 * 3600, 'end' => 17 * 3600), //wednesday
        array('start' => 8 * 3600, 'end' => 17 * 3600), //thursday
        array('start' => 8 * 3600, 'end' => 17 * 3600), //friday
        array('start' => 0, 'end' => 0), //saturday
    );

    variable_set('STD_limits', $STD_limits);
    variable_set('offer_limits', $offer_limits);
    variable_set('offer_profile_time', $offer_profile_time);
    variable_set('STD_profile_time', $STD_profile_time);
    $schema = loadschemaControllo();
    foreach ($schema as $name => $table) {
        if (!db_table_exists($name))
            db_create_table($return, $name, $table);
    }
//    get_data();
    return summary();
}

function my_drupal_write_record($table, &$object, $update = array()) {
    // Standardize $update to an array.
    if (is_string($update)) {
        $update = array($update);
    }

    $schema = loadschemaControllo($table);
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

function format_time_table($time_array, $link = NULL) {
    foreach ($time_array as $lbl => $time) {
        if (($lbl != 'C')) {
            $dd = (int) ($time / 86400);
            $hh = (int) ($time % 86400 / 3600);
            $mm = (int) ($time % 3600 / 60);
            $dd = $dd > 0 ? $dd . 'g ' : '';
            $dd = ($link ? l($dd . $hh . ':' . $mm, $link) : $dd . $hh . ':' . $mm);
        } else
            $dd = $time;
        $d.='<td class="number">' . $dd . '</td>';
    }

    return '<table><tr>' .
            '<th style="text-align: center;">n&deg;</th>' .
            '<th style="text-align: center;">&Sigma;</th>' .
//        '<th style="text-align: center;">&mu;</th>'.
//        '<th style="text-align: center;">&sigma;</th>'.
            '<tr>' . $d . '</tr></table>';
}

function format_time($time) {
    $d .= (int) ($time / 86400);
    $h .= (int) ($time % 86400 / 3600);
    $m .= (int) ($time % 3600 / 60);
    return ($d > 0 ? $d . 'g ' : '') . ($h > 0 ? $h . 'h ' : '') . $m . 'm';
}

function show_details($filter_index) {
    session_name('grid_filter');
    if (!session_start())
        return '<p>Descrizione mancante</p>';

    $sql = $_SESSION['session_filters'][$filter_index];
    $title = $_SESSION['session_filters_description'][$filter_index];
    //limiti esperessi in secondi
    $sla_limits = variable_get($sla . '_limits', null);

    $header = array(
        array('field' => 'f.APPA_CODI', 'data' => 'Apparecchiatura'),
//        array('field'=>'RIIN_CODI','data'=>'Richiesta'),
        array('field' => 'request_date', 'data' => 'aperto'),
        array('field' => 'end_request', 'data' => 'chiuso'),
        array('field' => 'start_date', 'data' => 'rip. dal'),
        array('field' => 'end_date', 'data' => 'rip. al'),
        array('field' => 'request_time_STD', 'data' => 't.int.(8x5)'),
        array('field' => 'fail_time_STD', 'data' => 't.ris.(8x5)'),
        array('field' => 'request_time_offer', 'data' => 't.int.'),
        array('field' => 'fail_time_offer', 'data' => 't.ris.'),
        array('field' => 'is_close', 'data' => 'intervento chiuso'),
    );
//    $q = "SELECT * FROM {fails} fs INNER JOIN {fees} f ON f.APPA_CODI=fs.APPA_CODI ";
//    $out = 'WHERE ' . $time . '_time_' . $worktime . ' > %d';
//    $in = 'WHERE ' . $time . '_time_' . $worktime . ' <= %d';
//    $tot = '';
//    $reason = array('request' => 'apertura intervento', 'fail' => 'durata intervento');
//    $time_l = array('STD' => 'standard (8hx5)', 'offer' => 'offerte in gara (12hx5+5hx1)');
//    $in_out_l = array('in' => 'entro', 'out' => 'superiore a');
//    $title = "Elenco interventi con <i>" . $reason[$time] . "</i> $in_out_l[$in_out] <b>" .
//            format_time($sla_limits[1]['blocked'][$time]) .
//            "</b><br/> calcolato secondo le tempistiche <i>" . $time_l[$sla] . '</i>';
    $sql[0] = $sql[0] . ' ' . tablesort_sql($header);
//    $qy = db_query($q . ${$in_out} . ' ' . tablesort_sql($header), $sla_limits[2]['blocked'][$time]);
    $qy = db_query("SELECT * $sql[0]", $sql[1], $sql[2]);
    //inizializzo contatori
    $t = array('Totali',
        array('data' => 0, 'colspan' => 4, 'class' => 'number'),
        array('data' => 0, 'class' => 'number'),
        array('data' => 0, 'class' => 'number'),
        array('data' => 0, 'class' => 'number'),
        array('data' => 0, 'class' => 'number'), '');

    while ($r = db_fetch_array($qy)) {

        //recupera il link all'attrezzatura        
//        $row[] = l(device_name( $r['APPA_CODI'] ). '<br/><small>(ric. ' . $r['RIIN_CODI'] . '<br/>
//                            int.' . $r['INTE_CODI'] . ')</small>', 
//                   'tested/si3c/APPA_CODI/' . $r['APPA_CODI'], array('html' => TRUE));
        //duplicata per debug sneza link a si3c
        $row[] = l($r['APPA_CODI'] . '<small><br/>(ric. ' . $r['RIIN_CODI'] . '<br/>
                 int.' . $r['INTE_CODI'] . ')</small>', 'tested/si3c/APPA_CODI/' .
                $r['APPA_CODI'], array('html' => TRUE));
        $row[] = gmdate('d-m-Y H.i.s', $r['request_date']);
        $row[] = gmdate('d-m-Y H.i.s', $r['end_request']);
        $row[] = gmdate('d-m-Y H.i.s', $r['start_date']);
        $row[] = gmdate('d-m-Y H.i.s', $r['end_date']);
        $row[] = array('data' => format_time($r['request_time_STD']), 'class' => 'number');
        $t[2]['data']+=$r['request_time_STD'];
        $row[] = array('data' => format_time($r['fail_time_STD']), 'class' => 'number');
        $t[3]['data']+=$r['fail_time_STD'];
        $row[] = array('data' => format_time($r['request_time_offer']), 'class' => 'number');
        $t[4]['data']+=$r['request_time_offer'];
        $row[] = array('data' => format_time($r['fail_time_offer']), 'class' => 'number');
        $t[5]['data']+=$r['fail_time_offer'];
        $row[] = array('data' => $r['is_close'], 'is_close' => $r['is_close']);
        $rows[] = $row;
        unset($row);
        $t[1]['data'] ++;
    }
    if (isset($rows)) {
        for ($i = 2; $i <= 5; $i++) {
            $t[$i]['data'] = format_time($t[$i]['data']);
        }
        $rows[] = array('data' => $t, 'class' => 'summary');
        return theme('table', $header, $rows, array('class' => 'tempistiche'), $title);
    }
    return '<p>Nessun dato</p>';
}

/**
 * form per la selezione dei criteri di calcolo del sommario
 * @param array $form_status
 * @param array $form
 */
function summary_filter_form(&$form_status) {
    $sla = array('STD', 'offer');
    if (!$form_status['post']) {
        $values['sla'] = 0;
        $values['worktime'] = 0;
        $values['approved'] = TRUE;
        $values['date_from'] = array(
            'year' => 2014,
            'month' => 7,
            'day' => 1);
        $values['date_to'] = array(
            'year' => (int) gmdate('Y'),
            'month' => (int) gmdate('m'),
            'day' => (int) gmdate('d'));
    } else {
        $values = $form_status['post'];
        //limita l'escursione al contratto corrente
        if (dateArrayToTime($values['date_from']) < dateArrayToTime(array('year' => 2014, 'month' => 7, 'day' => 1))) {
            $values['date_from'] = array('year' => 2014, 'month' => 7, 'day' => 1);
        } else {
            $values['date_from']['year'] = (int) $values['date_from']['year'];
            $values['date_from']['month'] = (int) $values['date_from']['month'];
            $values['date_from']['day'] = (int) $values['date_from']['day'];
        }
//limita alla data corrente        
        if (dateArrayToTime($values['date_to']) >= time()) {
            $values['date_to'] = array('year' => (int) date('Y'), 'month' => (int) date('m'), 'day' => (int) date('d'));
        } else {
            $values['date_to']['year'] = (int) $values['date_to']['year'];
            $values['date_to']['month'] = (int) $values['date_to']['month'];
            $values['date_to']['day'] = (int) $values['date_to']['day'];
        }
    }
    $f['c1']['#type'] = 'fieldset';
    $f['c1']['#collapsible'] = FALSE;
    $f['c1']['#attributes']['class'] = 'container-inline';
    $f['c1']['date_from'] = array(
        '#title' => 'valutazione a partire da',
        '#type' => 'date',
        '#default_value' => $values['date_from'],
        '#attributes' => array('onchange' => "this.form.submit();")
    );
    $f['c1']['date_to'] = array(
        '#title' => ' fino a',
        '#type' => 'date',
        '#default_value' => $values['date_to'],
        '#attributes' => array('onchange' => "this.form.submit();")
    );
//    $f['c1']['approved'] = array(
//        '#title' => 'Solo non approvati',
//        '#type' => 'checkbox',
//        '#default_value' => $values['approved'],
//        '#attributes' => array('onchange' => "this.form.submit();")
//    );
    $f['c2']['#type'] = 'fieldset';
    $f['c2']['#collapsible'] = FALSE;
    $f['c2']['#attributes']['class'] = 'container-inline';
    $f['c2']['worktime'] = array(
        '#title' => 'Selezione orario di lavoro',
        '#type' => 'radios',
        '#default_value' => $values['worktime'],
        '#options' => array('Lavorativo standard (8x5)',
            'Come da offerta (12x5+4x1)'),
        '#attributes' => array('onclick' => "this.form.submit();"),
        '#suffix' => '&nbsp;&nbsp;',
    );
    $f['c2']['sla'] = array(
        '#title' => 'Livello di servizio',
        '#type' => 'radios',
        '#default_value' => $values['sla'],
        '#options' => array('Da capitolato',
            'Da offerta'),
        '#attributes' => array('onclick' => "this.form.submit();")
    );

    $f['#submit'] = array('summary_filter_submit');
    $f['summary'] = array(
        '#type' => 'fieldset',
        '#collapsible' => FALSE,
        '#value' => summary_grid($values),
    );
    $f['counters'] = array(
        '#title' => 'Situazione ritardi manutenzioni programmate',
        '#type' => 'fieldset',
        '#collapsible' => FALSE,
        '#value' => sommario_programmate(),
    );
    $f['maintenance'] = array(
        '#title' => 'Piani manutenzioni programmate',
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => planned_maintennace($values),
    );

    return $f;
}

function summary_filter_submit($form, &$form_state) {
    $values = $form_status['post'];
}

/**
 * sommario conteggi
 */
function summary_grid($filter_values) {
//TODO: check result session function    
    session_name('grid_filter');
    $is_session = session_start();

    $sla_arr = array('STD', 'offer');
    $sla = $sla_arr[$filter_values['sla']];
    $worktime = $sla_arr[$filter_values['worktime']];
    $datetime = dateArrayToTime($filter_values['date_from']);
    $datetime_end = dateArrayToTime($filter_values['date_to']);
    if ($datetime_end < time()) {//verifica che non si vada nel futuro
        $filter_end = "(request_date > $datetime AND request_date <= $datetime_end) ";
    } else {
        $filter_end = "(request_date > $datetime ) ";
    }
    // filtro su approvati e data. Gli approvati hanno data diversa da 0
    if ($datetime) {
        $where = " WHERE category = %d AND approved=0 AND ( $filter_end OR " .
                " end_date > $datetime OR end_request > $datetime OR " . //intervento chiuso successivamente alla data di riferimento
                " end_date = 0 OR end_request = 0 OR end_date IS NULL  OR end_request IS NULL) "; //interventi aperti
    } else
        $where = ' WHERE category = %d AND approved=0 ';
    $sla_limits = variable_get($sla . '_limits', $STD_limits);
//ettichette tempo 
    $m_lab = array('request' => 'tempo intervento', 'fail' => 'tempo risoluzione');
//ettichette conteggi
    $c_lab = array('C' => 'n&deg;', 'T' => '&Sigma;', 'm' => '&mu;', 'd' => '&sigma;');
//ettichette gruppi di reparto
    $area_lab = array(1 => 'Aree critical care, diagnostica, neonatale, PS, UCIC Radioterapia e 118',
        2 => 'Laboratori', 3 => 'Ambulatori, Degenze', 4 => 'Dialisi',
        5 => 'Ambulatori in distretti esterni');
    $block_lab = array('blocked' => 'si', 'unblocked' => 'no');
//ettichette penale
    $in_out_lab = array('in' => 'regolari', 'out' => 'in penale', 'tot' => 'totali');
    $h1_span = 0;
    $h2_span = 0;
    $filter_index = 0;
    $first = TRUE;
    $h1[] = array('data' => '', 'header' => TRUE, 'colspan' => 2);
//prima cella a sx, inestazione a 2 righe per i gruppi di reparto
    $h2[] = array('header' => true, 'style' => "text-align: center;", 'rowspan' => 2, 'data' => 'Area');
// seconda cella a sx, intestazione a due righe per i guasti bloccanti
    $h2[] = array('header' => true, 'style' => "text-align: center;", 'rowspan' => 2, 'class' => 'vertical-text', 'data' => 'Bloccanti');
//scansione per aree
    foreach ($sla_limits as $area => $blocs) {
        //prima intestazione di  riga 
        $row[] = array('data' => $area_lab[$area], 'rowspan' => 2, 'header' => TRUE);
        foreach ($blocs as $block => $limits) {
            // seconda intestazione di riga
            $row[] = array('data' => $block_lab[$block], 'class' => $block, 'header' => TRUE);
            //scansiona per tipo di durata
            foreach ($limits as $limit => $time) {
                $field = $limit . '_time_' . $worktime;
                $q = 'SELECT SUM(' . $field . ') AS T ' .
                        ', COUNT(' . $field . ') AS C ';
//                         ', AVG(' . $field . ') as m '.
//                         ', STD(' . $field . ') as d '.
                $table = 'FROM {fails} fs '
                        . 'INNER JOIN {fees} f ON f.APPA_CODI=fs.APPA_CODI ';

                $out = $where . ' AND ' . $field . ' > %d ';
                $in = $where . ' AND ' . $field . ' <= %d ';
                $tot = $where;
                $q = $q . $table;
                //scansione i fuori/dentro/totali tempi contratuali  
                foreach (array('out', 'in', 'tot') as $s) {
                    if ($block == 'blocked') {
                        $qy = $q . ${$s} . ' AND blocked >= 3 ';
                        $fl = $table . ${$s} . ' AND blocked >= 3 ';
                    } else {
                        $qy = $q . ${$s} . ' AND blocked < 3 ';
                        $fl = $table . ${$s} . ' AND blocked < 3 ';
                    }
                    //registrazione dei parametri query per la sessione di dettaglio
                    $_SESSION['session_filters'][$filter_index] = array($fl,
                        $area,
                        $time);
                    // caricamento descrizione
                    $filter_values['in_out'] = $s;
                    $filter_values['block'] = $block;
                    $filter_values['category'] = $area;
                    $filter_values['limit'] = $limit;
                    $_SESSION['session_filters_description'][$filter_index] = selection_description($filter_values);
                    $qy = db_query($qy, $area, $time);
                    if ($r = db_fetch_array($qy)) {
                        foreach ($r as $h => $d) {
                            if ($h == 'C')
                                $row[] = array('data' => $d, 'class' => 'count number ' . $s);
                            else {
                                $row[] = array('data' => $is_session ? l(format_time($d), "tested/controllo/$filter_index/$sla/$limit/$worktime") : $d, 'class' => 'number ' . $s);
                                $filter_index++; //incrementa il filtro
                            }
                            if ($first) {
                                $h3[] = array('style' => "text-align: center;", 'data' => $c_lab[$h], 'header' => TRUE);
                            }
                            $h1_span++;
                            $h2_span++;
                        } //scansione conteggi
                        if ($first) {
                            $h2[] = array('style' => "text-align: center;",
                                'data' => $in_out_lab[$s], 'header' => TRUE, 'colspan' => $h2_span);
                            $h2_span = 0;
                        }
//                        $results[$area][$block][$limit][$s] = $r;
                    } //scansione db per conteggi
                }
                if ($first) {
                    $h1[] = array('style' => "text-align: center;", 'data' => $m_lab[$limit],
                        'header' => TRUE, 'colspan' => $h1_span);
                    $h1_span = 0;
                }
            }
            if ($first) {
                $rows[] = $h2;
                $rows[] = $h3;
            }
            $first = FALSE;
            $rows[] = $row;
            unset($row);
        }
    }

    $output .= theme('table', $h1, $rows, array('class' => 'misure'), 'Manutenzione Correttiva');
    return $output;
}

function summary() {
    return drupal_get_form('summary_filter_form');
}

/**
 * imposta la descrizione testuale del filtro
 * @param type $filter_values
 * @return string
 */
function selection_description($filter_values) {
    $sla = array('STD', 'offer');
    $in_out = $filter_values['in_out'];
    $date_from = dateArrayToTime($filter_values['date_from']);
    $date_to = (dateArrayToTime($filter_values['date_to']) < time()) ? ' fino al ' . $filter_values['date_to']['day'] .
            '.' . $filter_values['date_to']['month'] . '.' . $filter_values['date_to']['year'] : '';
    $limit = $filter_values['limit'];
    $category = $filter_values['category'];
    $block = $filter_values['block'];
    $sla_limits = variable_get($sla[$filter_values['sla']] . '_limits', array());
    $reason = array('request' => 'tempo intervento', 'fail' => 'risoluzione guasto');
    $time_l = array('STD' => 'standard (8hx5)', 'offer' => 'offerto in gara (12hx5+5hx1)');
    $in_out_l = array('in' => 'entro', 'out' => 'superiore a');
    $block_l = array('blocked' => 'bloccanti', 'unblocked' => ' non bloccanti');
    $area_lab = array(1 => 'aree critical care, diagnostica, neonatale, PS, UCIC Radioterapia e 118',
        2 => 'Laboratori', 3 => 'Ambulatori, Degenze', 4 => 'Dialisi',
        5 => 'Ambulatori in distretti esterni');
    $title = "Interventi $block_l[$block] apparecchiature in <i> $area_lab[$category]</i> con $reason[$limit]  
            $in_out_l[$in_out] <b>" . format_time($sla_limits[$category][$block][$limit]) . "</b> 
                calcolato secondo orario di lavoro <i>" . $time_l[$sla[$filter_values['worktime']]] . '</i> ' .
            'a partire dal giorno ' . format_date($date_from, 'custom', 'd.m.Y') . $date_to;

    return $title;
}

function planned_maintennace($filter_values = NULL) {
    /*
     * STATO: nelle mp gli stati sono N non trovato, S sospeso, A aperto, C clhiuso, R rinviato
     *        nelle vs gli stati sono P pianificato, A aperto, C chiuso, V archiviato
     * la rimappatura sarà :
     * N non trovato N MP, LOVE_CODI IN ( 'QNN', 'NF5', 'NV5', 'NON') VS
     * P pianificato con A MP e P vs
     * E Effettuato con C MP e A C V di VS
     * R Ritardato S R T MP
     * 
     */
    $conn = si3c_connect();

    $mp = "SELECT count(APPA_CODI) C, SETT_CODI, PIMP_LOTT, STATUS FROM (  "
            . "SELECT APPA_CODI, SETT_CODI, PIMP_LOTT, "
            . "CASE APMP_STAT "
            . "WHEN 'A' THEN 'P' "
            . "WHEN 'C' THEN 'E' "
            . "WHEN 'S' THEN 'R' "
            . "WHEN 'T' THEN 'R' "
            . "ELSE APMP_STAT "
            . "END AS STATUS "
            . "FROM SI3C.T_SCMP s "
            . "INNER JOIN SI3C.T_APMP a ON a.SCMP_CODI=s.SCMP_CODI AND a.APMP_CANC='N' "
            . "WHERE s.SCMP_CANC='N' ) "
            . "GROUP BY PIMP_LOTT, SETT_CODI , STATUS ";

    $vs = "SELECT count(APPA_CODI) C, SETT_CODI, PIMP_LOTT , STATUS FROM ("
            . "SELECT APPA_CODI, SETT_CODI, PIMP_LOTT ,"
            . "CASE  "
            . "WHEN v.LOVE_CODI IN ( 'QNN', 'NF5', 'NV5', 'NON') THEN 'N' " //selezione lotti apparcchitura non trovate
            . "WHEN VESI_STAT = 'A' THEN 'E' "
            . "WHEN VESI_STAT = 'C' THEN 'E' "
            . "WHEN VESI_STAT = 'V' THEN 'E' "
            . "ELSE VESI_STAT "
            . "END AS STATUS "
            . "FROM SI3C.T_VESI v "
            . "INNER JOIN SI3C.T_LOVE a ON a.LOVE_CODI=v.LOVE_CODI AND a.LOVE_CANC='N' "
            . "WHERE v.VESI_CANC='N' ) "
            . "GROUP BY PIMP_LOTT, SETT_CODI , STATUS ";
    $qy = "SELECT p.*, j.*, t.*, t.SETT_NOME AS SETTORE, p.PIMP_NOTE AS LOTTO "
            . "FROM SI3C.T_PIMP p "
            . "INNER JOIN ( ($mp) UNION ALL ($vs) ) j ON j.SETT_CODI=p.SETT_CODI AND j.PIMP_LOTT=p.PIMP_LOTT "
            . "INNER JOIN SI3C.T_SETT t ON t.SETT_CODI=p.SETT_CODI AND t.SETT_CANC='N' "
            . "WHERE p.PIMP_CANC='N' " . (isset($filter_values['date_from']['year']) ? " AND p.PIMP_ANNO_INIZ>=" . $filter_values['date_from']['year'] : '')
            . "ORDER BY p.PIMP_ANNO_INIZ, p.PIMP_LOTT ASC , p.SETT_CODI ASC";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);

    $keys = array();
    $lotti = array();
    $sett = array();
    $legend = array('E' => 'Eseguito', 'N' => 'Non Trovato', 'R' => 'Ritardato', 'P' => 'Pianificato');
    while ($r = oci_fetch_array($qy)) {
        $data[$r['PIMP_LOTT']]['tot']+=$r['C'];
        $data[$r['PIMP_LOTT']][$r['SETT_CODI']]['tot']+=$r['C'];
        $data[$r['PIMP_LOTT']][$r['SETT_CODI']][$r['STATUS']] = $r['C'];
        if (array_search($r['STATUS'], $keys) === FALSE)
            $keys[] = $r['STATUS'];
        if (array_search($r['PIMP_LOTT'], $lotti) === FALSE) {
            $lotti[$r['PIMP_LOTT']]['description'] = $r['LOTTO'] . " (" . $r['PIMP_LOTT'] . ")"
                    . "<br/><small>Creato:<b>" . $r['PIMP_DATA_EDIZ'] . "</b> ediz.:<b>" . $r['PIMP_EDIZ'] . "</b></br>"
                    . "Inizio:<b>" . $r['PIMP_ANNO_INIZ'] . " </b> mese:<b>" . $r['PIMP_MESE_INIZ'] . "</b> durata mesi:<b>" . $r['PIMP_MESI_DURA'] . "</b></small>";
            $lotti[$r['PIMP_LOTT']]['key'] = $r['PIMP_LOTT'];
        }
        if (array_search($r['SETT_CODI'], $sett) === FALSE) {
            $sett[$r['SETT_CODI']]['description'] = $r['SETTORE'] . " (" . $r['SETT_CODI'] . ")";
            $sett[$r['SETT_CODI']]['key'] = $r['SETT_CODI'];
        }
    }
//inserisce le chiavi mancanti
    foreach ($data as $lotto => $settori) {
        if ($lotto == 'tot')
            continue;
        $row[] = array('data' => $lotti[$lotto]['description'], 'rowspan' => count($settori) - 1);
        $row[] = array('data' => $data[$lotto]['tot'], 'rowspan' => count($settori) - 1, 'class' => 'number');
        foreach ($settori as $settore => $counts) {
            if ($settore == 'tot')
                continue;
            $row[] = $sett[$settore]['description'];
            $row[] = array('data' => $settori[$settore]['tot'], 'class' => 'number');
            foreach ($keys as $key) {
//sistema le chiavi mancanti
                if (key_exists($key, $counts))
                    $row[] = array('data' => l($counts[$key], 'tested/preventiva/' .
                                $lotti[$lotto]['key'] . '/' . $sett[$settore]['key'] . '/' . $key),
                        'class' => 'number', 'title' => $legend[$key]);
                else
                    $row[] = array('data' => 0, 'class' => 'number');
            }
            $rows[] = $row;
            unset($row);
        }
    }
    $header = array('Piani', 'totale piano', 'Settori', 'totale settore');

    foreach ($keys as $key)
        $header[] = array('data' => $key, 'value' => $key, 'class' => 'legenda', 'title' => $legend[$key]);
    $output = '<table><caption>Legenda</caption>';
    foreach ($legend as $s => $d) {
        $output.="<tr><td value=\"$s\" class=\"legenda\">$s</td><td class=\"legenda\">$d</td></tr>";
    }
    $output.='</table>';
    $output = array(
        '#type' => 'fieldset',
        '#title' => 'legenda',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => $output
    );
    $output = drupal_render($output);
    return $output . theme('table', $header, $rows, array('class' => 'misure'), 'Manutenzioni programmate');
}

function planned_maintenance_detail($lotto, $settore, $stato) {

    /*
     * STATO: nelle mp gli stati sono N non trovato, S sospeso, A aperto, C clhiuso, R rinviato
     *        nelle vs gli stati sono P pianificato, A aperto, C chiuso, V archiviato
     * la rimappatura sarà :
     * N non trovato N MP, LOVE_CODI IN ( 'QNN', 'NF5', 'NV5', 'NON') VS
     * P pianificato con A MP e P vs
     * E Effettuato con C MP e A C V di VS
     * R Ritardato S R T MP
     * 
     */
    $st = array('E' => 'Eseguito', 'N' => 'Non Trovato', 'R' => 'Ritardato', 'P' => 'Pianificato');
    $filter = '';
    switch ($stato) {
        case 'N': if ($settore == 'MP') {
                $in_stato = "'N'";
            } else {
                $in_stato = " 'A', 'C', 'V', 'P' ";
                $filter = " AND v.LOVE_CODI IN ( 'QNN', 'NF5', 'NV5', 'NON')";
            }
            Break;
        case 'P':
            if ($settore == 'MP')
                $in_stato = "'A'";
            else
                $in_stato = "'P'";

            break;
        case 'E': if ($settore == 'MP')
                $in_stato = "'C'";
            else
                $in_stato = "'A', 'C', 'V'";
            break;
        case 'R': $in_stato = "'S', 'R', 'T'";
            break;
    }
//    $st = array('A' => 'Aperto', 'N' => 'Non Trovato', 'S' => 'Sospeso',
//        'C' => 'Chiuso', 'P' => 'Previsto', 'V' => 'Archiviato', 'T' => 'Traslato', 'R' => 'Rinviato');

    $conn = si3c_connect();
    $qy = "SELECT PIMP_NOTE, PIMP_ANNO_INIZ, PIMP_DATA_EDIZ, PIMP_EDIZ FROM SI3C.T_PIMP WHERE PIMP_LOTT='$lotto' AND SETT_CODI='$settore'";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    if ($r = oci_fetch_assoc($qy))
        $output = "Piano: <b>" . $r['PIMP_NOTE'] . "</b> ($lotto) anno: <b>" . $r['PIMP_ANNO_INIZ'] . '</b> edizione:<b> ' . $r['PIMP_EDIZ'] . '</b> del <b>' . $r['PIMP_DATA_EDIZ'] . '</b>';
    $qy = "SELECT SETT_NOME FROM SI3C.T_SETT WHERE SETT_CODI='$settore'";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    if ($r = oci_fetch_assoc($qy))
        $output .='<br/> Settore:<b>' . $r['SETT_NOME'] . "</b> ($settore)";
    $output .= '</br>Stato: <b>' . $st[$stato] . "</b>";



//    $mp = "SELECT a.APPA_CODI , s.SETT_CODI, s.PIMP_LOTT , a.APMP_STAT AS STATUS, s.SCMP_CODI "
//            . "FROM SI3C.T_SCMP s "
//            . "INNER JOIN SI3C.T_APMP a ON a.SCMP_CODI=s.SCMP_CODI AND a.APMP_CANC='N' "
//            . "WHERE  AND  AND APMP_STAT='$stato' ";

    $qy = "SELECT p.*, s.*, a.*,m.*, p.APPA_CODI AS APPA_CODI, p.SEDE_CODI AS SEDE_CODI "
            . "FROM SI3C.T_APMP a "
            . "INNER JOIN SI3C.T_APPA p ON a.APPA_CODI=p.APPA_CODI AND p.APPA_CANC='N' "
            . "INNER JOIN SI3C.T_SCMP s ON s.SCMP_CODI=a.SCMP_CODI AND s.PIMP_LOTT='$lotto' AND s.SETT_CODI='$settore' AND s.SCMP_CANC='N' "
            . "LEFT JOIN SI3C.T_INPR m ON m.SCMP_CODI=s.SCMP_CODI AND m.APPA_CODI=p.APPA_CODI "
            . "WHERE APMP_STAT IN ($in_stato) AND a.APMP_CANC='N' ";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    while ($r = oci_fetch_array($qy)) {
        $row[] = l(device_name($r['APPA_CODI']), 'tested/si3c/APPA_CODI/' . $r['APPA_CODI']);
        if (isset($r['INPR_DATA_INIZ']) && isset($r['INPR_DATA_FINE']))
            $row[] = $r['INPR_DATA_INIZ'] . '   ' . $r['INPR_DATA_FINE'];
        else
            $row[] = '--';

        if (isset($r['INPR_CODI']))
            $row[] = $r['INPR_CODI'];
        else
            $row[] = '--';
        $rows[] = $row;
        unset($row);
    }

    $vs = "SELECT v.APPA_CODI , a.SETT_CODI, a.PIMP_LOTT , v.VESI_STAT AS STATUS, v.VESI_CODI "
            . "FROM SI3C.T_VESI v "
            . "INNER JOIN SI3C.T_LOVE a ON a.LOVE_CODI=v.LOVE_CODI AND a.LOVE_CANC='N' "
            . "WHERE v.VESI_CANC='N' AND PIMP_LOTT='$lotto' AND SETT_CODI='$settore' AND VESI_STAT IN ($in_stato) $filter ";

    $qy = "SELECT p.*, j.*, v.*, p.APPA_CODI AS APPA_CODI, p.SEDE_CODI AS SEDE_CODI "
            . "FROM SI3C.T_APPA p "
            . "INNER JOIN ($vs)  j ON j.APPA_CODI=p.APPA_CODI "
            . "LEFT JOIN SI3C.T_VESI v ON v.VESI_CODI=j.VESI_CODI AND v.APPA_CODI=j.APPA_CODI "
            . "WHERE p.APPA_CANC='N' ";
    $qy = oci_parse($conn, $qy);
    oci_execute($qy, OCI_DEFAULT);
    while ($r = oci_fetch_array($qy)) {
        $row[] = l(device_name($r['APPA_CODI']), 'tested/si3c/APPA_CODI/' . $r['APPA_CODI']);
        if (isset($r['VESI_DATA']))
            $row[] = $r['VESI_DATA'];
        else
            $row[] = '--';

        if (isset($r['VESI_CODI']))
            $row[] = $r['VESI_CODI'];
        else
            $row[] = '--';
        $rows[] = $row;
        unset($row);
    }
    return $output . theme('table', array('apparecchiatura', 'date', 'codice'), $rows);
}

/**
 * sintesi manutenzione programmate
 */
function sommario_programmate($detail = NULL) {
    $conn = si3c_connect();
    $totali_query = "SELECT -- a.APPA_CODI, a.APPA_ETIC_CODI , CL.CLAP_NOME, -- A.APPA_RISK_CODI, 
            CL.CLAP_CRIT AS CRIT,
            count(A.APPA_FREQ_VESI) AS FREQ_VS, 
            count(PF.APAT_VALO) AS FREQ_MP, 
            count(QF.APAT_VALO) AS FREQ_QC, 
            MD.APAT_VALO AS MANDIR_MANCOST
       
            FROM SI3C.SN_T_APPA A
            LEFT JOIN SI3C.SN_T_CLAP CL ON CL.CLAP_CODI=A.CLAP_CODI AND CL.CLAP_CANC='N'
            LEFT JOIN SI3C.SN_T_APAT QF ON QF.APPA_CODI=A.APPA_CODI AND QF.APAT_CANC='N' AND QF.ATTR_CODI=9999886 -- period. funzionale
            LEFT JOIN SI3C.SN_T_APAT PF ON PF.APPA_CODI=A.APPA_CODI AND PF.APAT_CANC='N' AND PF.ATTR_CODI=9999885 -- period. preventiva
            LEFT JOIN SI3C.SN_T_APAT MD ON MD.APPA_CODI=A.APPA_CODI AND MD.APAT_CANC='N' AND MD.ATTR_CODI=99991148 -- mandir-mancost
        group by CL.CLAP_CRIT, MD.APAT_VALO
        order by  MD.APAT_VALO, CL.CLAP_CRIT DESC";
    $qy = oci_parse($conn, $totali_query);
    oci_execute($qy, OCI_DEFAULT);
    while ($rw = oci_fetch_object($qy)) {
        $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['VS'] = is_null($rw->FREQ_VS) ? 0 : $rw->FREQ_VS;
        $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['QC'] = is_null($rw->FREQ_QC) ? 0 : $rw->FREQ_QC;
        $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['MP'] = is_null($rw->FREQ_MP) ? 0 : $rw->FREQ_MP;
    }
    //elenco delle verifiche di sicurezza fatte
    $vs_query = " SELECT APPA_CODI, MAX(VESI_DATA) AS D FROM SI3C.SN_T_VESI 
                WHERE VESI_CANC='N' AND VESI_STAT in ('A','C','V') AND VESI_INDI_COLL='N' 
                GROUP BY APPA_CODI ";
    //elenco dei controlli di qualità fatti
    $qc_query = " SELECT APPA_CODI, MAX(VESI_DATA) AS D FROM SI3C.SN_T_VESI  
                WHERE VESI_CANC='N' AND VESI_STAT in ('A','C','V') AND VESI_INDI_COLL='N' AND VESI_INDI_PRES = 'S'
                GROUP BY APPA_CODI ";
    //elenco delle preventive fatte
    $mp_query = " SELECT APPA_CODI, MAX(INPR_DATA_FINE) AS D FROM SI3C.SN_T_INPR 
                WHERE INPR_CANC='N' AND inpr_apmp_stat='C'
                GROUP BY APPA_CODI ";
    $cr_query = " SELECT APPA_CODI, MAX(RIIN_DATA_RICE) AS LAST_CR FROM SI3C.SN_T_RIIN 
                WHERE RIIN_CANC='N' 
                GROUP BY APPA_CODI ";
    $main_query = "
        SELECT a.APPA_CODI, a.APPA_ETIC_CODI , CL.CLAP_NOME, -- A.APPA_RISK_CODI, 
            CL.CLAP_CRIT AS CRIT, 
            VS.D AS LAST_VS, A.APPA_FREQ_VESI AS FREQ_VS, TRUNC(MONTHS_BETWEEN( SYSDATE,VS.D),1) AS VS_COUNT,
            MP.D AS LAST_MP, PF.APAT_VALO AS FREQ_MP, TRUNC(MONTHS_BETWEEN( SYSDATE,MP.D),1) AS MP_COUNT,
            QC.D AS LAST_QC, QF.APAT_VALO AS FREQ_QC, TRUNC(MONTHS_BETWEEN( SYSDATE,QC.D),1) AS QC_COUNT,
            MD.APAT_VALO AS MANDIR_MANCOST, CR.LAST_CR
            -- a.CLAP_ECON, a.STCO_CODI , a.SIST_CODI , 
            -- a.APPA_DESC , a.APPA_CODI_INVE , a.APPA_CODI_MATR , a.APPA_CODI_AUSI, a.MANU_CODI, a.STAP_CODI 
            FROM SI3C.SN_T_APPA A
            LEFT JOIN SI3C.SN_T_CLAP CL ON CL.CLAP_CODI=A.CLAP_CODI AND CL.CLAP_CANC='N'
            LEFT JOIN SI3C.SN_T_APAT QF ON QF.APPA_CODI=A.APPA_CODI AND QF.APAT_CANC='N' AND QF.ATTR_CODI=9999886 -- period. funzionale
            LEFT JOIN SI3C.SN_T_APAT PF ON PF.APPA_CODI=A.APPA_CODI AND PF.APAT_CANC='N' AND PF.ATTR_CODI=9999885 -- period. preventiva
            LEFT JOIN SI3C.SN_T_APAT MD ON MD.APPA_CODI=A.APPA_CODI AND MD.APAT_CANC='N' AND MD.ATTR_CODI=99991148 -- mandir-mancost
            LEFT JOIN ( $cr_query ) CR ON A.APPA_CODI=CR.APPA_CODI -- ultima corretttiva richiesta
            LEFT JOIN ( $vs_query ) VS ON A.APPA_CODI=VS.APPA_CODI AND A.APPA_FREQ_VESI < MONTHS_BETWEEN( SYSDATE,VS.D) -- VERIFICHE sicurezza 
            LEFT JOIN ( $qc_query ) QC ON A.APPA_CODI=QC.APPA_CODI AND QF.APAT_VALO<MONTHS_BETWEEN( SYSDATE,QC.D) -- VERIFICHE FUNZIONALI O CONTROLLI DI QUALITà
            LEFT JOIN ( $mp_query ) MP ON A.APPA_CODI=MP.APPA_CODI AND PF.APAT_VALO<MONTHS_BETWEEN( SYSDATE,MP.D) --preventive 

            WHERE a.APPA_CANC='N'  AND a.STAP_CODI in ('OK' , 'FD') AND 
             NOT (QC.D IS NULL AND MP.D IS NULL AND  VS.D IS NULL)
            ";
    if ($detail === NULL) { //sommario generico con generazione di query
        $summary_query = "
            SELECT crit ,MANDIR_MANCOST, COUNT(*) as C, COUNT(vs_count) as COUNT_VS, COUNT(mp_count) as COUNT_MP, COUNT(qc_count) as COUNT_QC
            FROM 
                ( $main_query )
            GROUP BY MANDIR_MANCOST, CRIT ORDER BY MANDIR_MANCOST DESC, CRIT
            ";
        $qy = oci_parse($conn, $summary_query);
        oci_execute($qy, OCI_DEFAULT);
        while ($rw = oci_fetch_object($qy)) {
            $filter = ' AND  CL.CLAP_CRIT ' . (is_null($rw->CRIT) ? 'IS NULL ' : "=" . $rw->CRIT);
            $ref = _set_query($filter);
            $row[] = l(is_null($rw->CRIT) ? 'n.' : $rw->CRIT, 'tested/programmata/' . $ref);
            $filter.=' AND  MD.APAT_VALO ' . (is_null($rw->MANDIR_MANCOST) ? 'IS NULL ' : "='" . $rw->MANDIR_MANCOST . "' ");
            $ref = _set_query($filter);
            $row[] = l(is_null($rw->MANDIR_MANCOST) ? 'non spec.' : $rw->MANDIR_MANCOST, 'tested/programmata/' . $ref);

            $row[] = array('data' => $rw->C . '/' . ($totali[$rw->CRIT][$rw->MANDIR_MANCOST]['MP'] +
                $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['QC'] +
                $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['VS']), 'class' => 'number');

            $filter1 = $filter . ' AND A.APPA_FREQ_VESI < MONTHS_BETWEEN( SYSDATE,VS.D)';
            $ref = _set_query($filter1);
            $row[] = array('data' => l($rw->COUNT_VS, 'tested/programmata/' . $ref) . '/' . $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['VS']
                , 'class' => 'number');
            $row[] = array('data' => (int) ($rw->COUNT_VS / $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['VS'] * 100), 'class' => 'number');
            $filter1 = $filter . ' AND PF.APAT_VALO<MONTHS_BETWEEN(SYSDATE,MP.D)';
            $ref = _set_query($filter1);
            $row[] = array('data' => l($rw->COUNT_MP, 'tested/programmata/' . $ref) . '/' . $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['MP']
                , 'class' => 'number');
            $row[] = array('data' => (int) ($rw->COUNT_MP / $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['MP'] * 100), 'class' => 'number');
            $filter1 = $filter . ' AND QF.APAT_VALO<MONTHS_BETWEEN( SYSDATE,QC.D)';
            $ref = _set_query($filter1);
            $row[] = array('data' => l($rw->COUNT_QC, 'tested/programmata/' . $ref) . '/' . $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['QC']
                , 'class' => 'number');
            $row[] = array('data' => (int) ($rw->COUNT_QC / $totali[$rw->CRIT][$rw->MANDIR_MANCOST]['QC'] * 100), 'class' => 'number');
            $rows[] = $row;
            unset($row);
        }
//        drupal_add_js(grafico_manutenzioni_programmate(), 'setting');
        return '<div class="plot" src="tested/plot/programmata/null"></div><br/>'.theme('table', array('crticit&agrave;', 'mandir mancost', array('class' => 'number', 'data' => 'totali'),
            array('class' => 'number', 'data' => 'Verifiche<br/>sicurezza'), array('class' => 'number', 'data' => '%'), array('class' => 'number', 'data' => 'Manutenzioni<br/>preventive'), array('class' => 'number', 'data' => '%'),
            array('class' => 'number', 'data' => 'Controlli<br/>qualit&agrave;'), array('class' => 'number', 'data' => '%')), $rows, array(), 'Conteggi manuenzioni programamte fuori tempi massimi / totali specifici');
    } else {
        $headers = array(
            array('data' => 'Apparecchiatura', 'field' => 'CL.CLAP_NOME'), 'ettichetta',
            array('data' => 'criticit&agrave;', 'field' => 'CL.CLAP_CRIT'),
            array('data' => 'Ultima VS', 'field' => 'VS.D'),
            array('data' => 'Freq. VS<br/><small>mesi</small>', 'field' => 'A.APPA_FREQ_VESI'),
            'ritardo VS',
            array('data' => 'Ultima MP', 'field' => 'MP.D'),
            array('data' => 'Freq. MP<br/><small>mesi</small>', 'field' => 'PF.APAT_VALO'),
            'ritardo MP',
            array('data' => 'Ultima QC', 'field' => 'QC.D'),
            array('data' => 'Freq. QC<br/><small>mesi</small>', 'field' => 'QF.APAT_VALO'),
            'ritardo QC',
            array('data' => 'Posizione', 'field' => 'MD.APAT_VALO'),
            array('data' => 'Ultima correttiva', 'field' => 'CR.LAST_CR')
        );
        $summary_query = "$main_query " . $_SESSION['programmata'][$detail] . tablesort_sql($headers);
//                "    ORDER BY CL.CLAP_CRIT ASC, MONTHS_BETWEEN( SYSDATE,VS.D) DESC,  
//                     MONTHS_BETWEEN( SYSDATE,MP.D) DESC,  
//                     MONTHS_BETWEEN( SYSDATE,QC.D) DESC";
        $qy = oci_parse($conn, $summary_query);
        oci_execute($qy, OCI_DEFAULT);
        while ($rw = oci_fetch_object($qy)) {
            $last_cr = 0; //controllo ultima correttiva 
            $rw->APPA_CODI = l($rw->CLAP_NOME, 'tested/si3c/APPA_CODI/' . $rw->APPA_CODI);
            UNSET($rw->CLAP_NOME);
            if (is_null($rw->LAST_VS)) {
                $rw->FREQ_VS = '';
                $rw->VS_COUNT = '';
            } else
                $last_cr = max($last_cr, strtotime($rw->LAST_VS));
            if (is_null($rw->LAST_QC)) {
                $rw->FREQ_QC = '';
                $rw->QC_COUNT = '';
            } else
                $last_cr = max($last_cr, strtotime($rw->LAST_QC));
            if (is_null($rw->LAST_MP)) {
                $rw->FREQ_MP = '';
                $rw->MP_COUNT = '';
            } else
                $last_cr = max($last_cr, strtotime($rw->LAST_MP));
            if (!is_null($rw->LAST_CR) && (strtotime($rw->LAST_CR) > $last_cr) && $last_cr) {
                $rows[] = array('data' => (array) $rw, 'class' => 'highlight');
            } else {
                $rows[] = (array) $rw;
            }
        }
        return theme('table', $headers, $rows, array(), 'Dettaglio manutenzioni programmate');
    }
}

/**
 * memorizza i filtri nella sessione per un successivo dettaglio
 * @param type $query
 * @return type
 */
function _set_query($query) {
    $ref = hash('md5', rand());
    $_SESSION['programmata'][$ref] = $query;
    return $ref;
}

/**
 * grafica la progressione delle attività programmate
 * @return string placeholder contenitore per il grafico jPlot
 */
function grafico_manutenzioni_programmate() {
    //grafico interventi aperti negli ultimi giorni  
//    $year = format_date(time(), 'custom', 'Y'); // anni a ritroso
    $qy_conteggi = "
        SELECT --CL.CLAP_CRIT AS CRIT, 
       MD.APAT_VALO AS MANDIR_MANCOST,
       M.D AS D, COUNT(M.T) AS C, M.T AS T
            -- a.CLAP_ECON, a.STCO_CODI , a.SIST_CODI , 
            -- a.APPA_DESC , a.APPA_CODI_INVE , a.APPA_CODI_MATR , a.APPA_CODI_AUSI, a.MANU_CODI, a.STAP_CODI 
            FROM SI3C.SN_T_APPA A
            LEFT JOIN SI3C.SN_T_CLAP CL ON CL.CLAP_CODI=A.CLAP_CODI AND CL.CLAP_CANC='N'
            LEFT JOIN SI3C.SN_T_APAT MD ON MD.APPA_CODI=A.APPA_CODI AND MD.APAT_CANC='N' AND MD.ATTR_CODI=99991148 -- mandir-mancost            
            LEFT JOIN (
                        ( SELECT APPA_CODI,  TO_CHAR(VESI_DATA,'YYYY-MM-DD') AS D , 'Sic.Elettrica' AS T FROM SI3C.SN_T_VESI 
                            WHERE VESI_CANC='N' AND VESI_STAT in ('A','C','V') AND VESI_INDI_COLL='N' 
                        )   -- VERIFICHE sicurezza 
                        UNION 
                        ( SELECT APPA_CODI,  TO_CHAR(VESI_DATA,'YYYY-MM-DD') AS D, 'Controllo Qual.' AS T FROM SI3C.SN_T_VESI  
                            WHERE VESI_CANC='N' AND VESI_STAT in ('A','C','V') AND VESI_INDI_COLL='N' AND VESI_INDI_PRES = 'S'
                        )  -- VERIFICHE FUNZIONALI O CONTROLLI DI QUALITà
                        UNION
                        ( SELECT APPA_CODI, TO_CHAR(INPR_DATA_FINE,'YYYY-MM-DD') AS D, 'Man.Preventiva' AS T FROM SI3C.SN_T_INPR 
                            WHERE INPR_CANC='N' AND inpr_apmp_stat='C'
                        ) --preventive 
                       ) M ON A.APPA_CODI=M.APPA_CODI 

            WHERE a.APPA_CANC='N'  AND a.STAP_CODI in ('OK' , 'FD') AND NOT  M.D IS NULL AND M.D >= '2014-01-01' AND M.D <= '".format_date(time(), 'custom', 'Y-m-d').
           "' GROUP BY M.T, M.D, MD.APAT_VALO --, CL.CLAP_CRIT
            ORDER BY M.D";
    $conn = si3c_connect();
    $qy = oci_parse($conn, $qy_conteggi);
    oci_execute($qy, OCI_DEFAULT);
    while ($rw = oci_fetch_array($qy)) {
        $d[$rw['MANDIR_MANCOST']][$rw['T']][] = array($rw['D'], (integer) $rw['C'],);
    }
    if (!isset($d))
        return;
    foreach ($d as $M => $T) {
        foreach ($T as $l => $c) {
            $data['data'][] = $c;
            $series[] = array('label' => $l . '(' . $M . ')');
        }
    }

    $data['options'] = array(
        'title' => 'Manutenzioni programmate effettuate dal 01/01/2014',
//        'seriesColors' => array('#d46c6c', '#d2d46c', '#77d46c', '#6c83d4', '#c1c2d0'),
        'series' => $series,

        'axes' => array(
            'xaxis' => array(
                'renderer' => '$jq.jqplot.DateAxisRenderer',
                'tickOptions' => array(
                    'formatString' => '%Y-%m-%d',
                    'autoscale' => true
                ),
            ),
            'yaxis' => array(
                'tickOptions' => array(
                    'formatString' => '%d'
                ),
//                    'min'=>-1,
//                    'max'=>$max +2,
                'autoscale' => true,
//                    'label'=>'scala giorni intervento'
            ),
//                'y2axis' => array(
//                    'tickOptions' => array( 
//                        'formatString' => '%d&nbsp;gg'
//                      ),
//                    
//                    'label'=>'scala accumulatore'
//                ),
        ),
//      'highlighter'=> array(
//        'show'=> true,
//        'sizeAdjust'=>7.5
//      ),
        'cursor' => array(
//              'show'=> false,
            'zoom' => true,
            'looseZoom' => true
        ),
        'legend' => array(
            'renderer'=>'$jq.jqplot.EnhancedLegendRenderer',
            'show' => true, 
            'location' => 'ne',
            'placement'=>'outsideGrid',
            'seriesToggle'=>'normal',
            )
    );
    return $data;
}


