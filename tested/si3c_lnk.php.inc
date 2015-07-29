<?php

// $Id$
include_once("admin_settings.php.inc");
include_once('Xref_SI3C_CDC.php'); // carica matrice  cross reference centri di costo
//include_once("tested.php.inc");
//include_once(drupal_get_path('module','gratcore').'/gratCore.php.inc');
//define(_TABLE_COLLAUDI,'principale_di_collaudo');
//define(_ITEMS_COLLAUDI,'apparecchiature_per_ordine');

function si3c_connect() {
    static $connect = NULL;
    if (!$connect) {
        $connect = oci_connect(variable_get('si3c_usr', _SI3C_USR), variable_get('si3c_pwd', _SI3C_PWD), variable_get('si3c_connect', _SI3C_CONNECT));
    }
    return $connect;
}

/**
 * seleziona il filtro per le appaercchiature.
 * se è un sistema l'appacodi è un array con tutte le voci correlate
 * 
 * @param array, numeric or null $appa_codi
 * @return string 
 */
function select_filter_range($appa_codi) {
    if ($appa_codi) {
        if (is_array($appa_codi)) {
            return 'APPA_CODI IN (' . implode(',', $appa_codi) . ') AND ';
        } else if (is_numeric($appa_codi)) {
            return " APPA_CODI=$appa_codi  AND ";
        } else
            return '';
    } else
        return '';
}

function test_si3c($sede, $codice) {

    $targa = array(
        '#title' => 'Dati apparecchiatura',
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#value' => targa_macchina($codice, $sede),
        '#attributes' => array('class' => 'dati_targa')
    );
    $grafici = array(
        '#title' => 'Grafici andamento',
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#value' => '<table><tr><td>
             <div class="plot" src="tested/plot/grafici_appareccchiatura/' . $codice . '">
             </div></td></tr>
             <tr><td><div class="plot" src="tested/plot/grafici_disponibilita/' . $codice . '"></div></td></tr></table>',
        '#attributes' => array('class' => 'grafici')
    );
    $conteggi = array(
        '#title' => 'Andamento conteggi',
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#value' => '<table><tr><td> <b>Manutenzioni preventive:</b></td><td style="text-align: right">' . count_preventiva($codice) . '</td></tr>
                    <tr><td> <b>Verifiche di sicurezza:</b></td><td style="text-align: right">' . verifiche_elettriche($codice) . '</td></tr>
                    <tr><td> <b>Preventive scadute:</b></td><td style="text-align: right">' . preventive_scadute($codice) . '</td></tr> 
        <tr><td> <b>Verifiche da fare:</b></td><td style="text-align: right">' . verifiche_da_fare($codice) . '</td></tr>
                 </table>',
        '#attributes' => array('class' => 'conteggi')
    );
    $sistema = array(
        '#title' => 'Componenti sistema',
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#value' => sistema_associato($codice),
        '#attributes' => array('class' => 'sistema')
    );
    $t = array('#type' => 'item',
        '#title' => 'Gestione',
        '#value' => libromacchina($codice));
    $output.= '<table><tr><td style="width:60%;">' . drupal_render($targa) . '</td>';
    $output.='<td valign="top"> ' . drupal_render($sistema);
    $output.=drupal_render($conteggi);
    $output.=drupal_render($grafici);
    $output.= '</td></tr></table>';
    $output.=drupal_render($t);

    return $output;
}

/**
 * recupera la vista riassuntiva attività di Si3C
 * @return type 
 */
function attivita_manutentiva() {
    unset($rows);

    /* ----------------------- risoluzione guasti --------------------- */ /*
      $anno=date('Y');
      $qy=oci_parse($conn,"SELECT I_E,RANGE,MESE,TOT  FROM SI3C.V_QOS_MC WHERE ANNO=$anno ORDER BY MESE,RANGE ");
      oci_execute($qy,OCI_DEFAULT); //db_query($qy);
      $ie=array('I'=>'Interno','E'=>'Esterno');
      $i_e='';
      unset($r);
      while ($row=oci_fetch_array($qy)){
      if (($r[2]<> $row['RANGE'] || $r[1]<>$row['MESE']) && isset($r))
      $rows[]=array($r[1],$r[2],array('data'=>$r[3],'class'=>'integer'),array('data'=>$r[4],'class'=>'integer'));
      if ($row['I_E']=="E")$r[4]=format_tempi($row['TOT']);
      if ($row['I_E']=="I")$r[3]=format_tempi($row['TOT']);
      $r[2]= $row['RANGE'];
      $r[1]=$row['MESE'];

      }

      If(!isset($rows))
      $output.='Nessuna registrazione Si<sup>3</sup>C';
      else
      $rows[]=array($r[1],$r[2],array('data'=>$r[3],'class'=>'integer'),array('data'=>$r[4],'class'=>'integer'));
      $output.=theme('table', array('Mese','Range', array('data'=>'Durata interventi Interni (h)','class'=>'integer'), array('data'=>'Durata interventi esterni (h)','class'=>'integer')),$rows);
      unset($rows);
     *  
     */
    $output.=lista_richieste_intervento();

    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT * FROM SI3C.V_SCADENZE");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    while ($row = oci_fetch_array($qy)) {
        $rows[] = array($row['LABEL'],
            array('data' => $row['NUM'], 'class' => 'integer'));
    }
    If (!isset($rows))
        $output.='Nessuna registrazione Si<sup>3</sup>C';
    else
        $output.=theme('table', array('Parametro', 'valori'), $rows);
    unset($rows);
    return $output;
}

/**
 * interpreta il numero in giorni e ore 
 * @param type $tempi 
 */
function format_tempi($tempi) {
    $ore = $tempi % 24;
    $giorni = (integer) ($tempi / 24);
    return $giorni . ' gg  ' . $ore . ' h';
}

function lista_richieste_intervento() {
    unset($rows);
    $conn = si3c_connect();
    $year = date('Y');
//interventi aperti e mai chiusi       
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         WHERE o.RIIN_STAT = 'A' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_RICE < to_date('" . ($year - 1) . "-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count = oci_fetch_array($qy);
    $rows[] = array('Interventi aperti prima del<b> 01/01/' . ($year - 1) . '</b> e non ancora chiusi:', $count['C'], '<b>Mai Chiusi</b>');
//interventi aperti e non presi in carico       
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         LEFT JOIN SI3C.T_INTE i ON o.RIIN_CODI=i.RIIN_CODI
                         WHERE o.RIIN_STAT = 'A' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_RICE >= to_date('" . ($year - 1) . "-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')
                            AND i.INTE_CODI IS NULL ");
    oci_execute($qy, OCI_DEFAULT);
    $count = oci_fetch_array($qy);
    $rows[] = array('Richieste aperte senza intervento:', $count['C'], '');
//interventi aperti ad oggi    
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o WHERE o.RIIN_STAT = 'A' AND o.RIIN_CANC='N' 
                         AND o.RIIN_DATA_RICE >= to_date('" . ($year - 1) . "-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_o = oci_fetch_array($qy);
//interventi chiusi ad oggi    
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o WHERE o.RIIN_STAT = 'C' AND o.RIIN_CANC='N' 
            AND o.RIIN_DATA_RICE >= to_date('" . ($year - 1) . "-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_c = oci_fetch_array($qy);
    $rows[] = array('Interventi dal <b> 01/01/' . ($year - 1) . '</b> ad oggi:', $count_o['C'], $count_c['C']);
//interventi aperti nell'anno in corso       

    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         WHERE o.RIIN_STAT = 'A' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_RICE >= to_date('$year-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_o = oci_fetch_array($qy);
//interventi chiusi nell'anno in corso       
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         WHERE o.RIIN_STAT = 'C' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_FINE >= to_date('$year-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_c = oci_fetch_array($qy);
    $rows[] = array('Interventi dall\'inizio anno ad oggi:', $count_o['C'], $count_c['C']);
    //interventi aperti nel mese in corso   
    $y_m = date('Y-m');
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         WHERE o.RIIN_STAT = 'A' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_RICE >= to_date('$y_m-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_o = oci_fetch_array($qy);
//interventi chiusi  nel mese in corso   
    $qy = oci_parse($conn, "SELECT COUNT(*) AS C FROM SI3C.T_RIIN o 
                         WHERE o.RIIN_STAT = 'C' 
                            AND o.RIIN_CANC = 'N' 
                            AND o.RIIN_DATA_FINE >= to_date('$y_m-01 00:00:01','YYYY-MM-DD HH24:MI:SS')");
    oci_execute($qy, OCI_DEFAULT);
    $count_c = oci_fetch_array($qy);
    $rows[] = array('Interventi  nel mese in corso:', $count_o['C'], $count_c['C']);
    $output.=theme('table', array('', 'Aperti', 'Chiusi'), $rows, array(), '<h2>Riassuntivi generali</h2>');

    $output.='<div class="plot" src="tested/plot/interventi_aperti/null"></div>';

    //  $fields=get_table_fields('SI3C.T_RIIN','r');
    unset($rows);
    $headers = array('Num.',
        array('field' => 'c.CLAP_NOME', 'data' => 'APPARECCHIATURA'),
        array('field' => 'r.RIIN_DATA_RICE', 'data' => 'Ricevuta'),
        array('field' => 'r.RIIN_DATA_FINE', 'data' => 'Chiusa'),
        array('field' => 'r.RIIN_NOTE', 'data' => 'Note'),
        array('field' => 'r.RIIN_RIFE', 'data' => 'Riferimento'),
        array('field' => 'u.UBIC_NOME', 'data' => 'Ubicazione'),
        array('field' => 'a.CECO_CODI', 'data' => 'Attribuito a'),
        array('field' => 'INTERVAL', 'data' => 'Durata (gg:hh:mm)'));

    // tutti gli interventi aperti e i chiusi l'ultimo mese
    $qy = oci_parse($conn, "SELECT 
                                r.APPA_CODI,
                                r.RIIN_DATA_RICE,
                                r.RIIN_DATA_FINE,
                                r.RIIN_NOTE ,
                                r.RIIN_RIFE ,
                                u.UBIC_NOME,
                                a.CECO_CODI,
                                NVL(r.RIIN_DATA_FINE, SYSTIMESTAMP) - r.RIIN_DATA_RICE AS INTERVAL,
                                r.RIIN_CODI,
                                i.INTE_CODI
                         FROM SI3C.T_RIIN r
                         INNER JOIN SI3C.T_APPA a ON a.APPA_CODI=r.APPA_CODI AND a.SEDE_CODI=r.T_A_SEDE_CODI
                         LEFT JOIN si3c.T_INTE i ON r.RIIN_CODI=i.RIIN_CODI AND i.INTE_CANC='N'
                         LEFT JOIN SI3C.T_UBIC u ON u.UBIC_CODI=r.MITT_UBIC_CODI AND u.OSPE_CODI=r.MITT_OSPE_CODI
                         LEFT JOIN SI3C.T_CLAP c ON c.CLAP_CODI=a.CLAP_CODI 
                         WHERE ((r.RIIN_DATA_RICE >= to_date('$y_m-01 00:00:01','YYYY-MM-DD HH24:MI:SS') AND r.RIIN_STAT = 'C')
                                OR (r.RIIN_STAT = 'A' AND i.INTE_STAT='A')) AND r.RIIN_CANC = 'N' AND a.STAP_CODI <> 'FU' 
                                AND r.RIIN_DATA_RICE >= to_date('" . ($year - 1) . "-01-01 00:00:01','YYYY-MM-DD HH24:MI:SS')
                         " . tablesort_sql($headers)); //, "a.APPA_RISK_CODI ASC, r.RIIN_STAT ASC,"));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    $i = 1;
    unset($show);

    while ($row = oci_fetch_array($qy)) {
        $rw[] = $i++;
        foreach ($row as $lbl => $cc) {
            if (!is_numeric($lbl)) {
                //campi non visualizzati
                if ($lbl == 'CLAP_NOME' || $lbl == 'RIIN_CODI' || $lbl == 'INTE_CODI')
                    continue;
                //ettichetta 
                if ($lbl == 'APPA_CODI') {
                    $rw[] = l(device_name($cc), 'tested/si3c/APPA_CODI/' . $cc, array('html' => TRUE)) . '<br/><small>(ric. ' . $row['RIIN_CODI'] . ' | 
                        Int. ' . $row['INTE_CODI'] . ')</small> '; //.'#IN-'.$row['INTE_CODI']);
                } else if ($lbl == 'CECO_CODI') {
                    // recupera il nome della struttura associata
                    $struct = get_structure(Xref_SI3C_CDC($cc), 20, NULL, 'Sconosciuto');
                    if ($struct[0] !== 'Sconosciuto') {
                        $rw[] = $struct[0];
                    } else {
                        $rw[] = $cc;
                    }
                } else {
                    if ($lbl == 'INTERVAL') {
                        $d = trunc($row['INTERVAL']);
                        $h = ($row['INTERVAL'] - $d) * 24;
                        $m = ($h - trunc($h)) * 60;
                        $rw[] = $d . ':' . trunc($h) . ':' . trunc($m);
                    } else
                        $rw[] = $cc;
                }
            }
        }
        $rows[] = array('data' => $rw, 'id' => 'RI-' . $row['RIIN_CODI']);
        $first = FALSE;
        unset($rw);
    }
    If (!isset($rows))
        $output.='Nessuna registrazione Si<sup>3</sup>C';
    else
        $output.=theme('table', $headers, $rows, array(), '<br/><h2>Elenco interventi aperti dal 01/01/' . ($year - 1) . ' ad oggi, e interventi chiusi nel mese in corso</h2><br/>');

    return $output;
}

function trunc($number) {

    return (int) substr((string) $number, 0, strpos((string) $number, '.'));
}

function libromacchina($appa_codi) {
    include_once 'risk-analyzer.php';
    update_one_item($appa_codi, TRUE);

    $t = array(
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#title' => 'Tabella di rischio',
        '#value' => item_risk_table($appa_codi, TRUE),
        '#attributes' => array('class' => 'tabella_rischi')
    );
    $output = get_collaudi($appa_codi);
    $output.= drupal_render($t);
    $output.= richiesta_associata($appa_codi);
    $output.= manutenzione_preventiva($appa_codi);
    $output.= scadenze_preventiva($appa_codi);
    $output.=verifiche($appa_codi);
    return $output;
}

function richiesta_associata($APPA_CODI) {
    $conn = si3c_connect();
    $table = array('r' => 'SI3C.T_RIIN');
    $qy = oci_parse($conn, "SELECT *
                         FROM SI3C.T_RIIN r
                         WHERE r.APPA_CODI=$APPA_CODI ORDER BY r.RIIN_DATA_RICE DESC 
                         "); //. tablesort_sql($headers, "a.APPA_RISK_CODI ASC, r.RIIN_STAT ASC,"));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    $output = array(
        '#type' => 'fieldset',
        '#title' => 'Manutenzioni correttive',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => build_table($qy, 'SI3C.T_RIIN', 'RIIN_CODI', 'intervento_associato', 'Dettagli Intervento'),
        '#attributes' => array('class' => 'richiesta_associata', 'id' => 'correttive')
    );
    return drupal_render($output);
}

function intervento_associato($RIIN_CODI) {
    $conn = si3c_connect();
    $table = array('i' => 'SI3C.T_INTE');
    $qy = oci_parse($conn, "SELECT *
                         FROM SI3C.T_INTE i
                         WHERE i.RIIN_CODI=$RIIN_CODI ORDER BY i.INTE_DATA_INIZ DESC 
                         "); //. tablesort_sql($headers, "a.APPA_RISK_CODI ASC, r.RIIN_STAT ASC,"));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    return build_table($qy, 'SI3C.T_INTE', 'INTE_CODI', 'intervento_esterno_associato', 'Dettagli attivit&agrave;');
}

function intervento_esterno_associato($INTE_CODI) {
    $conn = si3c_connect();
    $table = array('e' => 'SI3C.T_RIEM');
    $qy = oci_parse($conn, "SELECT e.*
                         FROM SI3C.T_RIEM e
                         WHERE e.INTE_CODI=$INTE_CODI ORDER BY e.RIEM_DATA_EMIS DESC, e.RIEM_CODI DESC
                         "); //. tablesort_sql($headers, "a.APPA_RISK_CODI ASC, r.RIIN_STAT ASC,"));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    return build_table($qy, 'SI3C.T_RIEM');
}

/**
 * recupera la tabella per la visualizzazione
 * @param object $oracle_query
 * @param string $table_name 
 */
function build_table($oracle_query, $table_name, $field_id = NULL, $function = NULL, $table_label = NULL) {
    $first = true;
    unset($headers);
    unset($rows);
    $cols = 0;
    while ($row_qy = oci_fetch_array($oracle_query)) {
        foreach ($row_qy as $lbl => $val)
            if (!is_numeric($lbl))
//                $val = sostituzione_ettichette($lbl, $val); //sostituisce le ettichette se le trova
                if ($show = labellize_field($table_name, $lbl)) {
                    if ($first) {
                        $cols++;
                        $headers[] = $show;
                    }
                    $row[] = array('data' => $val, 'class' => $lbl, 'value' => $val);
                } else
                    continue;
        $rows[] = array('data' => $row, 'no_striping' => TRUE);
        unset($row);
        //recupero informazioni associate e nidicficate
        if ($field_id !== NULL && $function !== NULL && function_exists($function)) {
            $t = array(
                '#type' => 'fieldset',
                '#collapsed' => TRUE,
                '#collapsible' => TRUE,
                '#title' => $table_label ? $table_label : '+',
                '#value' => $function($row_qy[$field_id]),
            );

            $rows[] = array(array('data' => drupal_render($t),
                    'colspan' => $cols)
            );
            unset($row);
        }
        $first = FALSE;
    }
    if (isset($rows))
        return theme('table', $headers, $rows, array('class' => $function));
    else
        return '<p>Nessun dato</p>';
}

/**
 * 
 * @param type $riferimento ettichetta del campo 
 * @param type $valore valore da sostituire
 * @return type
 */
function sostituzione_ettichette($riferimento, $valore) {
    if (is_null($valore) || is_null($riferimento))
        return $valore;
    $conn = si3c_connect();
    $q = oci_parse($conn, "SELECT RV_MEANING FROM SI3C.V_REF_CODES_LING
                            WHERE RV_DOMAIN = '$riferimento' 
                                  AND RV_LOW_VALUE = '$valore'
                                  AND LING_CODI='I' ;");
    oci_execute($q, OCI_DEFAULT);
    if ($mean = oci_fetch_array($q)) {
        return $mean['RV_MEANING'];
    }
    return $valore;
}

function device_name($appa_codi) {
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT a.*, d.*
                         FROM SI3C.T_APPA a
                         INNER JOIN SI3C.T_CLAP d ON d.CLAP_CODI=a.CLAP_CODI
                         WHERE d.CLAP_VALI='S' AND a.APPA_CODI=$appa_codi");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy);
    if ($r = oci_fetch_array($qy)) {
        $name = $appa_codi;
        if (isset($r['APPA_FREE_CLAP']) && strlen($r['APPA_FREE_CLAP']) > 1) {
            $name = $r['APPA_FREE_CLAP'];
        } else {
            $name = $r['CLAP_NOME'];
        }
        return $name;
    } else
        return 'Device sconosciuto';
}

function targa_macchina(&$codice, $sede = 'APPA_CODI') {
    if ($sede == 'APPA_CODI') {
        $filtro = " AND a.APPA_CODI=$codice ";
    } else {
        $filtro = " AND a.SEDE_CODI=$sede AND a.APPA_ETIC_CODI=$codice ";
    }
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT a.*, c.*, m.*, d.*, t.*, s.*, ac.*, st.*, se.*, u.*,sz.*,
                        v.AZIE_NOME as Venditore,
                        ma.AZIE_NOME as Manutentore
                         FROM SI3C.T_APPA a
                         INNER JOIN SI3C.T_CLAP c ON c.CLAP_CODI=a.CLAP_CODI
                         INNER JOIN SI3C.T_MOAP m ON m.CLAP_CODI=a.CLAP_CODI AND
                                                     m.MOAP_CODI=a.MOAP_CODI AND
                                                     m.DITT_CODI=a.DITT_CODI
                         LEFT JOIN SI3C.T_DITT d ON d.DITT_CODI=a.DITT_CODI AND d.DITT_CANC='N' AND d.DITT_VALI='S'
                         LEFT JOIN SI3C.T_TIAP t ON t.TIAP_CODI=a.TIAP_CODI AND t.TIAP_CANC='N'
                         LEFT JOIN SI3C.T_STAP s ON s.STAP_CODI=a.STAP_CODI AND s.STAP_CANC='N'
                         LEFT JOIN SI3C.T_MOAC ac ON ac.MOAC_CODI=a.MOAC_CODI AND ac.MOAC_CANC='N'
                         LEFT JOIN SI3C.T_STCO st ON st.STCO_CODI=a.STCO_CODI AND st.STCO_CANC='N'
                         LEFT JOIN SI3C.T_SEDE se ON se.SEDE_CODI=a.SEDE_CODI AND se.SEDE_CANC='N'
                         LEFT JOIN SI3C.T_AZIE az ON az.AZIE_CODI=a.AZIE_CODI AND az.AZIE_CANC='N'
                         LEFT JOIN SI3C.T_AZIE v ON v.AZIE_CODI=a.AZIE_VEND_CODI AND v.AZIE_CANC='N'
                         LEFT JOIN SI3C.T_AZIE ma ON ma.AZIE_CODI=a.AZIE_MANU_CODI AND ma.AZIE_CANC='N'
                         LEFT JOIN SI3C.T_STAN sz ON sz.OSPE_CODI=a.OSPE_CODI AND sz.UBIC_CODI=a.UBIC_CODI AND sz.UBIC_CODI=a.UBIC_CODI AND sz.STAN_CODI=a.STAN_CODI AND SZ.STAN_CANC='N'
                         LEFT JOIN (SELECT a.UBIC_CODI as UBIC, b.OSPE_CODI as OSPE, CONCAT(CONCAT(b.OSPE_NOME, ' - ') ,a.UBIC_NOME) as UBIC_NOME FROM SI3C.T_UBIC a 
                                INNER JOIN SI3C.T_OSPE b ON a.OSPE_CODI=b.OSPE_CODI) u 
                                ON u.UBIC=a.UBIC_CODI AND u.OSPE=a.OSPE_CODI 
                         WHERE c.CLAP_VALI='S' $filtro");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    while ($row = oci_fetch_array($qy)) {
        foreach ($row as $key => $value) {
            if (!is_numeric($key) && $value != '' && $lbl = labellize_field('SI3C', $key)) {
                if ($key == 'CECO_CODI') {
                    $value = get_structure(Xref_SI3C_CDC($value), NULL, NULL, 'sconosciuto');
                    $value = $value[0];
                }
                if ($key == 'APPA_CODI_INVE') {
                    $inv = explode('/', $value);
                    $inventario = ascott_inventario($inv[0], $inv[1]);
                    if ($inventario) {
                        include_once 'ascott_lnk.php.inc';
                        $inventario = array(
                            '#title' => $value,
                            '#type' => 'fieldset',
                            '#collapsible' => TRUE,
                            '#collapsed' => TRUE,
                            '#value' => $inventario,
                            '#attributes' => array('class' => 'inventario'));
                        $rows[] = array('<b>' . $lbl . ':</b>', drupal_render($inventario));
                        if ($accessori = ascott_schede($inv[0], $inv[1])) {
                            $accessori = array(
                                '#title' => 'Accessori',
                                '#type' => 'fieldset',
                                '#collapsible' => TRUE,
                                '#collapsed' => TRUE,
                                '#value' => $accessori,
                                '#attributes' => array('class' => 'inventario'));
                            $rows[] = array('Accessori', drupal_render($accessori));
                            continue;
                        }
                    }
                }
                $rows[] = array('<b>' . $lbl . ':</b>', $value);
                // //$output.='<tr><td><b>'.$key.':</b></td><td>'.$value.'</td></tr>';
            }
        }
        $codice = $row['APPA_CODI']; // sostituisce l'appa codi all'ettichetta in ogni caso 
        $sede = 'APPA_CODI';
    }
    If (!isset($rows))
        $output.='Nessuna registrazione Si<sup>3</sup>C';
    else {
        $output.=theme('table', array('Parametri', 'valori'), $rows);
    }
    //recupero attributi estesi 
    unset($rows);
    $ext = " SELECT ATTR_NOME, APAT_VALO "
            . "FROM SI3C.T_APAT v "
            . "INNER JOIN SI3C.T_ATTR t ON t.ATTR_CODI=v.ATTR_CODI AND t.ATTR_CANC='N' AND t.ATTR_VALI='S' "
            . "INNER JOIN SI3C.T_APPA a ON a.APPA_CODI=v.APPA_CODI AND a.SEDE_CODI=v.SEDE_CODI AND a.APPA_CANC='N' "
            . "WHERE v.APAT_CANC='N' $filtro ORDER BY ATTR_NOME";
    $ext = oci_parse($conn, $ext);
    oci_execute($ext, OCI_DEFAULT); //db_query($qy); 
    while ($r = oci_fetch_array($ext)) {
        $rows[] = array($r['ATTR_NOME'], $r['APAT_VALO']);
    }
    if (!isset($rows)) {
        $output.='Nessuna parametro esteso';
    } else {
        $output.=theme('table', array('Parametro esteso', 'valore'), $rows, array(), 'Parametri estesi');
    }
    return $output;
}

function sistema_associato($appa_codi) {
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT b.APPA_CODI
                         FROM SI3C.T_APPA a
                         INNER JOIN SI3C.T_SIST s ON a.SIST_CODI=s.SIST_CODI
                         INNER JOIN SI3C.T_APPA b ON s.SIST_CODI=b.SIST_CODI
                         WHERE a.APPA_CODI=$appa_codi");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    $i = 0;
    while ($row = oci_fetch_array($qy)) {
        $i++;
        $rows[] = array($i, l(device_name($row['APPA_CODI']), 'tested/si3c/APPA_CODI/' . $row['APPA_CODI'], array('html' => TRUE)));
    }
    $headers = array('n.', 'sistema');
    if (isset($rows))
        return theme('table', $headers, $rows);
    else
        return 'nessun sistema associato';
}

// conteggio manutenzioni preventive
function count_preventiva($appa_codi) {
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT count(*) as PREVENTIVA
                         FROM SI3C.T_INPR 
                         WHERE APPA_CODI=$appa_codi AND INPR_CANC='N'");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    if ($count = oci_fetch_array($qy)) {
        return $count['PREVENTIVA'];
    }
    return 0;
}

function manutenzione_preventiva($appa_codi = NULL) {
    $selection = select_filter_range($appa_codi);
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT I.*, 
                                P.PROT_TITL, P.PROT_EDIZ, P.PROT_DATA_UAGG,
                                F.FORN_NOME,
                                S.SCMP_NOME, S.SCMP_DATA_MAXI
                         FROM SI3C.T_INPR I
                         LEFT JOIN SI3C.T_SCMP S ON S.SCMP_CODI=I.SCMP_CODI AND S.SCMP_CANC = 'N'
                         LEFT JOIN SI3C.T_FORN F ON F.FORN_CODI=I.FORN_CODI AND F.FORN_CANC = 'N'
                         LEFT JOIN SI3C.T_PROT P ON P.PROT_CODI=I.PROT_CODI AND P.PROT_VALI='S' AND P.PROT_CANC='N'
                         WHERE $selection INPR_CANC='N' ORDER BY INPR_DATA_INIZ DESC");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    $output = array(
        '#type' => 'fieldset',
        '#title' => 'Manutenzioni preventive',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => build_table($qy, 'SI3C.T_INPR'),
        '#attributes' => array('class' => 'manutenzione_preventiva')
    );
    return drupal_render($output);
}

function verifiche($appa_codi, $TO_DO = TRUE) {
    $conn = si3c_connect();

    $qy = oci_parse($conn, "SELECT *
                         FROM SI3C.T_VESI 
                         WHERE APPA_CODI=$appa_codi  AND VESI_CANC='N'
                         ORDER BY VESI_DATA DESC, VESI_STAT ASC");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    $output = array(
        '#type' => 'fieldset',
        '#title' => 'Verifiche',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => build_table($qy, 'SI3C.T_VESI') .
        '<table><tr><td value="A" class="VESI_STAT">A</td><td>Aperta</td>
                    <td value="C" class="VESI_STAT">A</td><td>Chiusa</td>
                    <td value="P" class="VESI_STAT">A</td><td>Programmata</td>
                    <td value="V" class="VESI_STAT">A</td><td>Archiviata</td></tr></table>',
        '#attributes' => array('class' => 'verifiche')
    );
    return drupal_render($output);
}

function scadenze_preventiva($appa_codi = NULL) {
    $T_SCMP = array(
        array('field' => 'A.APMP_FREQ', 'data' => 'Frequenza',),
        array('field' => 'A.APMP_STAT', 'data' => 'Stato',),
        array('field' => 'A.APMP_NOTE', 'data' => 'note',),
        array('field' => 'S.SCMP_DATA_MAXI', 'data' => 'scadenza', 'order' => 'desc'),
        array('field' => 'S.SCMP_ANNO', 'data' => 'Anno',),
        array('field' => 'S.SCMP_MESE', 'data' => 'Mese',),
        array('field' => 'S.SCMP_SETT', 'data' => 'Sett.',),
        array('field' => 'S.SCMP_NOME', 'data' => 'Scheda',),
        array('field' => 'M.PIMP_LOTT', 'data' => 'Lotto',),
        array('field' => 'M.PIMP_NOTE', 'data' => 'piano',),
        array('field' => 'P.PROT_TITL', 'data' => 'protocollo',),
//        array('field' => 'INPR_DATA_INIZ', 'data' => 'eseg. dal',),
//        array('field' => 'INPR_DATA_FINE', 'data' => 'al',),
    );
    foreach ($T_SCMP as $field) {
        $field_list[] = $field['field'];
    }
    $field_list = implode(',', $field_list);
    $selection = select_filter_range($appa_codi);
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT $field_list " .
//        A.*, S.*, M.*,
//                                P.PROT_TITL, P.PROT_EDIZ, P.PROT_DATA_UAGG, P.PROT_CODI
            "FROM SI3C.T_APMP A
                         INNER JOIN SI3C.T_SCMP S ON S.SCMP_CODI=A.SCMP_CODI AND S.SCMP_CANC = 'N'
                         INNER JOIN SI3C.T_PIMP M ON M.PIMP_LOTT=S.PIMP_LOTT AND M.SETT_CODI=S.SETT_CODI 
                                                    AND M.SEDE_CODI=S.SEDE_CODI AND M.PIMP_CANC = 'N'                          
                         LEFT JOIN SI3C.T_PROT P ON P.PROT_CODI=A.PROT_CODI AND P.PROT_VALI='S' AND P.PROT_CANC='N'
            WHERE $selection APMP_CANC='N' AND APMP_STAT = 'A' " . tablesort_sql($T_SCMP));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    while ($rs = oci_fetch_array($qy)) {
        //calcolo livello di allarme
        $alert = (int) ceil((time() - strtotime($rs['SCMP_DATA_MAXI'])) / 3600 / 24);
        //creazione tabella
        foreach ($rs as $field => $value) {
            if (!is_numeric($field)) {
//                $value = sostituzione_ettichette($field, $value);
                if ($field == 'APMP_STAT')
                    $row[] = array('data' => $value,
                        'class' => ($alert > 30 ? 'alert' : ($alert > 7 ? 'warning' : 'ok')),
                        'title' => ($alert > 30 ? 'Ritardo superiore a un mese (' . $alert . ' gg)' : ($alert > 7 ? 'ritardo superiore a una settimana(' . $alert . ' gg)' : '')),
                        'value' => $value);
                else
                    $row[] = array('data' => $value); //, 'class' => $field, 'value' => $value);
            }
        }
        $rows[] = $row;
        unset($row);
    }
    $output = array(
        '#type' => 'fieldset',
        '#title' => 'Scadenza preventive',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#value' => theme('table', $T_SCMP, $rows),
        '#attributes' => array('class' => 'scadenza_preventive')
    );
    return drupal_render($output);
}

//conteggio verifiche di sicurezza 
function verifiche_elettriche($appa_codi) {
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT count(*) as VERIFICHE
                         FROM SI3C.T_VESI 
                         WHERE APPA_CODI=$appa_codi AND VESI_STAT='V' AND VESI_CANC='N'");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    if ($count = oci_fetch_array($qy)) {
        return $count['VERIFICHE'];
    }
    return 0;
}

function verifiche_da_fare($appa_codi) {
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT count(*) as VERIFICHE
                         FROM SI3C.T_VESI 
                         WHERE APPA_CODI=$appa_codi AND VESI_STAT IN ('A', 'P') AND VESI_CANC='N'");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    if ($count = oci_fetch_array($qy)) {
        return $count['VERIFICHE'];
    }
    return 0;
}

function preventive_scadute($appa_codi = NULL) {
    $selection = select_filter_range($appa_codi);
    $conn = si3c_connect();
    $qy = oci_parse($conn, "
                         SELECT count(SCMP_STAT) AS SCAD
                         FROM SI3C.T_APMP A
                         INNER JOIN SI3C.T_SCMP S ON S.SCMP_CODI=A.SCMP_CODI AND S.SCMP_CANC = 'N'
                         WHERE $selection APMP_STAT = 'A' AND APMP_CANC = 'N' ");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    if ($count = oci_fetch_array($qy)) {
        return $count['SCAD'];
    }
    return 0;
}

function search_inventario($inventario) {
    $find = array();
    $conn = si3c_connect();
    if (is_array($inventario)) { // costruzione elenco di ricerca
        foreach ($inventario as $ele) {
            $list[] = " APPA_CODI_INVE LIKE '%$ele%' ";
        }
        $inventario = implode(' OR ', $list);
    } else
        $inventario = " APPA_CODI_INVE LIKE '%$inventario%' ";
    $qy = oci_parse($conn, "SELECT * FROM SI3C.T_APPA 
                         WHERE $inventario");
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    while ($list = oci_fetch_array($qy)) {
        $find[] = array('title' => device_name($list['APPA_CODI']),
            'link' => url('tested/si3c/APPA_CODI/' . $list['APPA_CODI']),
            'date' => date('Y-m-d H:i:s', $list['APPA_DATA_UAGG']),
            'snippet' => 'Ettichetta:<b>' . $list['SEDE_CODI'] . $list['APPA_ETIC_CODI'] . '</b> Registrato il: ' . $list['APPA_DATA_RILE'] .
            ' Inventario:' . $list['APPA_CODI_INVE']);
    }
    return $find;
}

function search_codice($inventario) {
    $find = array();
    $conn = si3c_connect();
    if (!is_array($inventario))  // costruzione elenco di ricerca
        $search[] = $inventario;
    else
        $search = $inventario;

    foreach ($search as $ele) {
//            $list[]=" APPA_CODI = $ele ";
        $where[] = " APPA_ETIC_CODI = $ele OR APPA_CODI_MATR LIKE '%$ele%'";
    }

    $qy = oci_parse($conn, "SELECT * FROM SI3C.T_APPA 
                         WHERE " . implode(' OR ', $where));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    while ($list = oci_fetch_array($qy)) {
        $ff = array('title' => device_name($list['APPA_CODI']),
            'link' => url('tested/si3c/APPA_CODI/' . $list['APPA_CODI']),
            'date' => date('Y-m-d H:i:s', $list['APPA_DATA_UAGG']),
            'snippet' => 'Ettichetta:' . $list['SEDE_CODI'] . $list['APPA_ETIC_CODI'] . ' Registrato il: ' . $list['APPA_DATA_RILE'] .
            ' Inventario:' . $list['APPA_CODI_INVE'] . ' Matricola:' . $list['APPA_CODI_MATR']);
        foreach ($search as $s) {
            $ff['snippet'] = preg_replace('/' . $s . '/', "<b>$0</b>", $ff['snippet']);
        }
        $find[] = $ff;
    }
    return $find;
}

function search_description($text) {
    $find = array();
    $conn = si3c_connect();
    if (!is_array($inventario))  // costruzione elenco di ricerca
        $search[] = $text;
    else
        $search = $text;

    foreach ($search as $ele) {
//            $list[]=" APPA_CODI = $ele ";
        $ele = strtoupper($ele);
        $where[] = " UPPER(DITT_NOME) LIKE ('%$ele%') OR UPPER(MOAP_NOME) LIKE ('%$ele%') OR UPPER(CLAP_NOME) LIKE ('%$ele%') ";
    }

    $qy = oci_parse($conn, "SELECT * FROM SI3C.T_APPA a
                        INNER JOIN SI3C.T_CLAP c ON a.CLAP_CODI=c.CLAP_CODI
                        INNER JOIN SI3C.T_MOAP m ON a.CLAP_CODI=m.CLAP_CODI AND a.MOAP_CODI=m.MOAP_CODI AND a.DITT_CODI=m.DITT_CODI
                        INNER JOIN SI3C.T_DITT d ON a.DITT_CODI=d.DITT_CODI
                        WHERE " . implode(' OR ', $where));
    oci_execute($qy, OCI_DEFAULT); //db_query($qy); 
    while ($list = oci_fetch_array($qy)) {
        $ff = array('title' => device_name($list['APPA_CODI']),
            'link' => url('tested/si3c/APPA_CODI/' . $list['APPA_CODI']),
            'date' => date('Y-m-d H:i:s', $list['APPA_DATA_UAGG']),
            'snippet' => 'Ettichetta:' . $list['SEDE_CODI'] . $list['APPA_ETIC_CODI'] . ' Registrato il: ' . $list['APPA_DATA_RILE'] .
            '<br/> Inventario:' . $list['APPA_CODI_INVE'] . ' Matricola:' . $list['APPA_CODI_MATR'] .
            '<br/> Ditta:' . $list['DITT_NOME'] . ' Modello:' . $list['MOAP_NOME'] . ' descrizione:' . $list['CLAP_NOME']);
        foreach ($search as $s) {
            $ff['snippet'] = preg_replace('/' . $s . '/i', "<b>$0</b>", $ff['snippet']);
        }
        $find[] = $ff;
    }
    return $find;
}

function grafico_richieste_intervento($appacodi) {
    $conn = si3c_connect();
    unset($data);
    $qy = oci_parse($conn, "SELECT TO_CHAR(SI3C.T_RIIN.RIIN_DATA_RICE,'YYYY-MM-DD') as INIZIO, TO_CHAR(SI3C.T_RIIN.RIIN_DATA_FINE,'YYYY-MM-DD') as FINE,
                CASE WHEN SI3C.T_RIIN.RIIN_STAT = 'A' THEN SYSDATE - SI3C.T_RIIN.RIIN_DATA_RICE 
                     ELSE  SI3C.T_RIIN.RIIN_DATA_FINE -SI3C.T_RIIN.RIIN_DATA_RICE END AS GIORNI 
        FROM SI3C.T_APPA INNER JOIN SI3C.T_RIIN ON (SI3C.T_APPA.SEDE_CODI = SI3C.T_RIIN.SEDE_CODI) AND (SI3C.T_APPA.APPA_CODI = SI3C.T_RIIN.APPA_CODI)
        WHERE SI3C.T_APPA.APPA_CANC='N' AND SI3C.T_RIIN.RIIN_CANC='N' AND SI3C.T_APPA.APPA_CODI=$appacodi
        ORDER BY SI3C.T_RIIN.RIIN_DATA_RICE");
    //GROUP BY TO_CHAR(SI3C.T_RIIN.RIIN_DATA_RICE,'YYYY-MM-DD')");
    oci_execute($qy, OCI_DEFAULT);
    $max = 0;
    $tt = 0;
    while ($list = oci_fetch_array($qy)) {
        $dt[] = array($list['INIZIO'], (float) $list['GIORNI']);
        $tt+=(float) $list['GIORNI'];
        $dc[] = array($list['INIZIO'], $tt);
        $max = ((float) $list['GIORNI'] > $max ? (float) $list['GIORNI'] : $max);
    }
    //return $data;
    if (!isset($dt))
        return;

    $data['data'][] = $dt;
    $data['data'][] = $dc;
    $data['options'] = array(
        'title' => 'distribuzione manutenzioni',
        'seriesColors' => array('#d46c6c', '#d4936c', '#d2d46c', '#77d46c', '#6c83d4', '#c1c2d0'),
        'series' => array(
            array(
//                'yaxis'=>'yaxis',
                'label' => 'durata intervento'),
            array(
//                'yaxis'=>'y2axis',
                'label' => 'accumulatore giorni di intervento')
        ),
        'axes' => array(
            'xaxis' => array(
                'renderer' => '$jq.jqplot.DateAxisRenderer',
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
        'highlighter' => array(
            'show' => true,
            'sizeAdjust' => 7.5
        ),
        'cursor' => array(
            'show' => false,
        ),
        'legend' => array('show' => true, 'location' => 's', 'placement' => 'outsideGrid')
    );
    return $data;
}

function grafico_interventi_aperti() {
    //grafico interventi aperti negli ultimi giorni  
    $year = format_date(time(), 'custom', 'Y'); // anni a ritroso
    $conn = si3c_connect();
    $qy = oci_parse($conn, "SELECT COUNT(RIIN_STAT) AS C,
                            to_char(RIIN_DATA_RICE,'YYYY-MM-DD') AS D
                         FROM SI3C.T_RIIN A
                         WHERE RIIN_CANC = 'N' 
                            AND to_number(to_char(RIIN_DATA_RICE,'YYYY')) > " . ($year - 2) . "
                            AND RIIN_DATA_FINE <= SYSDATE
                         GROUP BY to_char(RIIN_DATA_RICE,'YYYY-MM-DD') 
                         ORDER BY to_char(RIIN_DATA_RICE,'YYYY-MM-DD')");
    oci_execute($qy, OCI_DEFAULT);
    while ($rw = oci_fetch_array($qy)) {

        $d[] = array($rw['D'], (integer) $rw['C'],);
    }
    if (!isset($d))
        return;
    $data['data'][] = $d;
    unset($d);
    $qy = oci_parse($conn, "SELECT COUNT(RIIN_STAT) AS C,
                            to_char(RIIN_DATA_FINE,'YYYY-MM-DD') AS D
                         FROM SI3C.T_RIIN 
                         WHERE RIIN_CANC = 'N' 
                            AND to_number(to_char(RIIN_DATA_FINE,'YYYY')) > " . ($year - 2) . "
                            AND RIIN_DATA_FINE <= SYSDATE
                         GROUP BY to_char(RIIN_DATA_FINE,'YYYY-MM-DD') 
                         ORDER BY to_char(RIIN_DATA_FINE,'YYYY-MM-DD')");
    oci_execute($qy, OCI_DEFAULT);
    while ($rw = oci_fetch_array($qy)) {

        $d[] = array($rw['D'], (integer) $rw['C'],);
    }
    $data['data'][] = $d;
    $data['options'] = array(
        'title' => 'Storico interventi aperti ultimi 2 anni',
        'seriesColors' => array('#d46c6c', '#d2d46c', '#77d46c', '#6c83d4', '#c1c2d0'),
        'series' => array(
            array(
//                'yaxis'=>'yaxis',
                'label' => 'Interventi aperti'),
            array(
//                'yaxis'=>'yaxis',
                'label' => 'interventi chiusi'),
        ),
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
        'legend' => array('show' => true, 'location' => 'ne')//, 'placement'=>'outsideGrid')
    );
//    drupal_add_js($data, 'setting');

    return $data;
}

function conteggi_generali($from_year = NULL) {
    //query conteggio verifiche
    $ver_sic = "SELECT L.PIMP_LOTT, V.VESI_STAT, COUNT(V.VESI_STAT) AS VERIFICHE
                FROM SI3C.T_VESI V 
                INNER JOIN SI3C.T_LOVE L ON V.LOVE_CODI = L.LOVE_CODI
                GROUP BY L.PIMP_LOTT, V.VESI_STAT WHERE V.VESI_CANC=N' AND L.LOVE_CANC='N'";

    //query conteggio manutentive
    $MAN_PREV = "SELECT S.PIMP_LOTT, M.APMP_STAT, Count(M.APMP_STAT) AS ConteggioDiAPMP_STAT
                 FROM SI3C.T_APMP M 
                 INNER JOIN SI3C.T_SCMP S ON M.SCMP_CODI = S.SCMP_CODI
                 GROUP BY S.PIMP_LOTT, M.APMP_STAT ";
}

/** ricerca la pratica del collaudo
 * 
 * @param type $appa_codi codice apparecchiatura
 * @return ritorna il link alla pratica 
 */
function get_collaudi($appa_codi) {
    $conn = si3c_connect();
    $q = oci_parse($conn, "SELECT * FROM SI3C.T_APPA
                            WHERE APPA_CANC = 'N' 
                            AND  APPA_CODI=$appa_codi
                            ");
    oci_execute($q, OCI_DEFAULT);
    if ($rw = oci_fetch_array($q)) {
        $etic = $rw['APPA_ETIC_CODI'];
        $qy = "SELECT c.nid FROM " . _ITEMS_COLLAUDI . " a
            INNER JOIN " . _TABLE_COLLAUDI . " c ON c.`numero ordine`=a.`numero ordine`
            WHERE `etichetta italtbs`=%d";
        $qy = db_query($qy, $etic);
        if ($nid = db_result($qy))
            return l('Pratica di collaudo', 'node/' . $nid);
    }
    return '<p>Non disponibile</p>';
}

function ajax_plot($parm = NULL, $options = NULL) {
    switch ($parm) {
        case 'programmata':
            include_once 'gestione_manutenzione.php';
            $data = grafico_manutenzioni_programmate();
            break;
        case 'interventi_aperti':
            $data = grafico_interventi_aperti();
            break;
        case 'grafici_appareccchiatura':
            $data = grafico_richieste_intervento($options);
            break;
        case 'grafici_disponibilita':
            include_once 'risk-analyzer.php';
            $data = MT_stats($options);
            $data = grafico_Mean_Times(unserialize($data['data']));
        break;
    }

    return drupal_json($data);
    exit();
}
