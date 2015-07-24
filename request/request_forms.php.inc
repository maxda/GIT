<?php

/********************************************************************************************
------------------------------------------- collect and show functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/

define ("_REQUESTS_TABLE",'requests'); 	//define new node addon table for module requests
define ("_MAXLENTEXT",1000); // limite dimensioni campi di testo
/**
 *   Authorization profile
 */
include_once('request_aux_function.php.inc');

function request_main_form($node,&$form) {
    if (isset($node->vesion)) {
        $function='form_'.$node->version;
        return $function($node,$form);
    }
    else {
        return base_form($node,$form);
    }

}

//funzione di selezione della "view" delle info
function request_main_view($node, $teaser) {
    if (isset($node->vesion)) {
        $function='view_'.$node->version;
        return $function($node, $teaser);
    }
    else {
        return base_view($node, $teaser);
    }
}



// form di base per le info delle richieste
function base_form($node,&$form) {
    $disabled=FALSE;//$node->status==_APPROVED || $node->status==_CLOSED; //disabilita alcuni campi se la richiesta � ormai approvata
    $dot=0;
    $form['main']['title']=array( // title per farlo coincidere col campo $node->title;
        '#type'=>'textfield',
        '#title'=>t('Descrizione'),
        '#description'=>t("Denominazione o brevissima descrizione del bene meglio rispondente alle descrizioni proposte.<p>
							Dove possibile utilizzare una delle voci proposte in automantico."),
        '#default_value'=> (isset($node->title)?$node->title:''),
        '#parents' => array('title'), // per farlo coincidere col campo $node->title;
        '#weight' => ++$dot,
        '#required' => TRUE,
        '#autocomplete_path' => 'cud/description',

    );
    $form['main']['qta'] = array(
        '#type' => 'textfield',
        '#disabled'=>$disabled,
        '#title' => t('Quantit&agrave;'),
        '#description'=>'numero di apparecchiature identiche o analoghe richieste',
        '#default_value'=> (isset($node->qta)?$node->qta:1),
        '#parents' => array('qta'), // per farlo coincidere col campo $node->qta;
        '#weight' => ++$dot,
        '#prefix'=>'<table><tr><td style="width:20%;">',
        '#suffix'=>'</td>',
        '#attributes'=>array('class'=> 'integer'),
        '#size'=>5,
        '#element_validate'=>array('validate_integer'),
    );
    $form['main']['type_acq'] = array(
        '#type' => 'radios',
        '#disabled'=>$disabled,
        '#title' => t('Tipologia dell\'acquisto'),
        '#default_value'=> (isset($node->type_acq)?$node->type_acq:0),
        '#parents' => array('type_acq'),
        '#options' => array('Apparecchiatura nuova(saltare i punti 1. e 2. e compilare dal 3 in poi)',
        'Sostituzione/reintegro apparecchiatura esistente','Dispositivo per la sicurezza e salute dei lavoratori'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td>',
        '#weight' => ++$dot,
    );
    // se la priorità è stata imposta da amministratori la blocca
    if ($node->priority>9 && !user_access(_MANGE_RCQ) && !user_access(_FULL_MANGE_RCQ)){
        $form['main']['priority']= array (
            '#type'=>'hidden',
            '#value'=>$node->priority,
            '#parents' => array('priority')
        );
        $form['main']['priority1']=array(
            '#title' => t('Priorit&agrave;'),
            '#type'=>'item',
            '#value'=>get_priority($node->priority),
            '#prefix'=>'<td>',
            '#suffix'=>'</td></tr></table>',
            '#weight' => ++$dot
         );
    }
    else {   
        $form['main']['priority'] = array(
            '#type' => 'radios',
            '#title' => t('Priorit&agrave;'),
            '#default_value' => isset($node->priority) ? $node->priority : 0,
            '#options' => get_priority(),
            '#description'=>t('Indicare la priorit&agrave; dell\'attrezzatura.'),
            '#parents' => array('priority'),
            '#prefix'=>'<td>',
            '#suffix'=>'</td></tr></table>',
            '#weight' => ++$dot
        );
    }
    //punto uno della scheda
    $form['main']['dot1']=array(
        '#type'=>'fieldset',
        '#title'=>t('1. Apparecchio o sistema da sostituire'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#weight' => ++$dot,
    );

    $form['main']['dot1']['inventario']=array(
        '#type'=>'textfield',
        '#title'=>t('N. Inventario'),
        '#default_value'=> (isset($node->main['inventario'])?$node->main['inventario']:''),
        '#parents' => array('main','inventario'),
        '#prefix'=>'<table><tr><td>',
        '#suffix'=>'</td>',
        '#weight' => 2,
        '#element_validate'=>array('validate_inventario'),
    );
    $form['main']['dot1']['produttore']=array(
        '#type'=>'textfield',
        '#title'=>t('Produttore'),
        '#default_value'=> (isset($node->main['produttore'])?$node->main['produttore']:''),
        '#parents' => array('main','produttore'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td></tr><tr>',
        '#weight' => 3,
    );
    $form['main']['dot1']['modello']=array(
        '#type'=>'textfield',
        '#title'=>t('Modello'),
        '#default_value'=> (isset($node->main['modello'])?$node->main['modello']:''),
        '#parents' => array('main','modello'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td>',
        '#weight' => 4,
    );
    $form['main']['dot1']['anno_acq']=array(
        '#type'=>'textfield',
        '#title'=>t('Anno di acquisto'),
        '#default_value'=> (isset($node->main['anno_acq'])?$node->main['anno_acq']:''),
        '#parents' => array('main','anno_acq'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td></tr><tr>',
        '#weight' => 5,
    );
    $form['main']['dot1']['utilizzatori']=array(
        '#type'=>'textfield',
        '#title'=>t('Utilizzatori'),
        '#default_value'=> (isset($node->main['utilizzatori'])?$node->main['utilizzatori']:''),
        '#parents' => array('main','utilizzatori'),
        '#prefix'=>'<td>',
        '#suffix'=>'</td></tr></table>',
        '#weight' => 6,
    );

    // punto DUE della scheda
    $form['main']['dot2']=array(
        '#type'=>'fieldset',
        '#title'=>t('2. Motivo della sostituzione'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#prefix'=>'',
        '#suffix'=>'',
        '#weight' => ++$dot
    );
    $form['main']['dot2']['motivo']=array(
        '#type'=>'checkboxes',
        '#options' => get_fuoriuso() ,
        '#title'=>t('Motivo'),
        '#default_value'=> (isset($node->main['motivo'])?$node->main['motivo']:array()),
        '#parents' => array('main','motivo'),
        '#prefix'=>'<table><tr><td>',
        '#suffix'=>'</td>',
        '#weight' => 1,
        '#element_validate'=>array('validate_motivo'),
    );
    $form['main']['dot2']['altro_motivo']=array(
        '#type'=>'textfield',
        '#title'=>t('altro'),
        '#default_value'=> (isset($node->main['altro_motivo'])?$node->main['altro_motivo']:''),
        '#parents' => array('main','altro_motivo'),
        '#prefix'=>'<td style="width:70%">',
        '#suffix'=>'</td></tr></table>',
        '#weight' => 3,
    );

    /// punto TRE della scheda
    $form['main']['dot3']=array(
        '#type'=>'fieldset',
        '#title'=>t('3. Descrizione apparecchiatura/sistema richiesto'),
        '#description'=>t('S\'intende la fornitura chiavi in mano, quindi completa di accessori e delle eventuali opere di installazione'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#required' => TRUE,
        '#weight' => ++$dot
    );
    $form['main']['dot3']['descrizione_apparecchiatura']=array(
        '#type' => 'textarea',
        '#title' => t('Descrizione dell\'apparecchiatura/sistema'),
        '#description'=>t('breve descrizione dell\'apparecchiatura/sistema richiesta'),
        '#rows' => 2,
        '#default_value'=> (isset($node->main['descrizione_apparecchiatura'])?$node->main['descrizione_apparecchiatura']:''),
        '#parents' => array('main','descrizione_apparecchiatura'),
        '#required' => TRUE,
        '#weight' => 1,
        '#element_validate'=>array('validate_text'),
    );
    $form['main']['dot3']['tipologia']=array(
        '#type' => 'textarea',
        '#title' => t("Tipologia delle prestazioni"),
        '#description'=>t('breve descrizione delle prestazioni garantibili dall\'attrezzatura
							   ai fini della corretta identificazione della destinazione d\'uso.'),
        '#default_value'=> (isset($node->main['tipologia'])?$node->main['tipologia']:''),
        '#rows' => 2,
        '#parents' => array('main','tipologia'),
        '#required' => !(user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)),
        '#weight' => 2,
        '#element_validate'=>array('validate_text'),
    );
    $form['main']['dot3']['ICD9CM']=array(
        '#type'=>'textfield',
        '#title' => t("Codice nomenclatore ICD9CM"),
        '#description'=>t("dal catalogo ICD 9"),
        '#default_value'=> (isset($node->main['ICD9CM'])?$node->main['ICD9CM']:''),
        '#parents' => array('main','ICD9CM'),
        '#weight' => 3,
        '#prefix'=>'<table><tr><td>',
        '#suffix'=>'</td>',
    );
    $form['main']['dot3']['volume']=array(
        '#type'=>'textfield',
        '#title' => t("Volume prestazioni (numero)"),
        '#description'=>t("prestazioni annue previste ."),
        '#default_value'=> (isset($node->main['volume'])?$node->main['volume']:0),
        '#parents' => array('main','volume'),
        '#weight' => 4,
        '#prefix'=>'<td>',
        '#suffix'=>'</td>',
        '#element_validate'=>array('validate_integer'),
    );
    $form['main']['dot3']['bacino']=array(
        '#type'=>'textfield',
        '#title' => t("Bacino di utenza"),
        '#description'=>t("numero di abitanti serviti"),
        '#default_value'=> (isset($node->main['bacino'])?$node->main['bacino']:0),
        '#parents' => array('main','bacino'),
        '#weight' => 5,
        '#prefix'=>'<td>',
        '#suffix'=>'</td>',
    );
    $form['main']['dot3']['anni_vita']=array(
        '#type'=>'textfield',
        '#title' => t("Anni di vita presunta"),
        '#description'=>t("compresa obsolescenza"),
        '#default_value'=> (isset($node->main['anni_vita'])?$node->main['anni_vita']:0),
        '#parents' => array('main','anni_vita'),
        '#weight' => 5,
        '#prefix'=>'<td>',
        '#suffix'=>'</td></tr></table>',

        '#element_validate'=>array('validate_integer'),
    );

    // punto QUATTRO della scheda
    $form['main']['dot4']=array(
        '#type'=>'fieldset',
        '#title'=>t('4. Benefici attesi'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#weight' => ++$dot
    );
    $dot_int=0;
    $form['main']['dot4']['benefici_c'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Clinici'),
        '#collapsible'=> FALSE,
        '#weight' => 1
    );
    $form['main']['dot4']['benefici_c']['clinici'] = array(
        '#type' => 'checkboxes',
        '#title' => t(''),
        '#default_value'=> (isset($node->main['clinici'])?$node->main['clinici']:array()), // default	'Aumento efficacia'
        '#parents' => array('main','clinici'),
        '#weight' => 2,
        '#options' => get_clinici(),
    );
    $form['main']['dot4']['benefici_c']['nuovo_bisogno']=array(
        '#type'=>'textarea',
        '#title' => t("Bisogno di salute/Problema clinico"),
        '#default_value'=> (isset($node->main['nuovo_bisogno'])?$node->main['nuovo_bisogno']:''),
        '#parents' => array('main','nuovo_bisogno'),
        '#required' => !(user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)),
        '#weight' => 3,
        '#element_validate'=>array('validate_text'),
    );
    $form['main']['dot4']['benefici_c']['indicatore_clinico']=array(
        '#type'=>'textarea',
        '#title' => t("Indicatore di monitoraggio e valore obiettivo"),
        '#description'=>t('Descrivere i parametri per la misurazione dell\'utilizzo e dell\'appropriatezza della tecnologia'),
        '#default_value'=> (isset($node->main['indicatore_clinico'])?$node->main['indicatore_clinico']:''),
        '#parents' => array('main','indicatore_clinico'),
        '#weight' => 4,
        '#element_validate'=>array('validate_text'),
    );

    $form['main']['dot4']['benefici_g'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Gestionali'),
        '#collapsible'=> FALSE,
        '#weight' => 5,
    );
    $form['main']['dot4']['benefici_g']['gestionali'] = array(
        '#type' => 'checkboxes',
        '#title' => t(''),
        '#default_value'=> (isset($node->main['gestionali'])?$node->main['gestionali']:array()), // default 'riduzione tempi diagnostico/terapeutici'
        '#options' => get_gestionali(),
        '#parents' => array('main','gestionali'),
        '#weight' => 6,
    );
    $form['main']['dot4']['benefici_g']['altri_benefici_g']=array(
        '#type'=>'textfield',
        '#title' => t("riduzione di altre prestazioni.Specificare"),
        '#default_value'=> (isset($node->main['altri_benefici_g'])?$node->main['altri_benefici_g']:'nessuna'),
        '#parents' => array('main','altri_benefici_g'),
        '#weight' => 7,
    );
    $form['main']['dot4']['benefici_g']['indicatore_gestionale']=array(
        '#type'=>'textfield',
        '#title' => t("Indicatore di monitoraggio e valore obiettivo"),
        '#default_value'=> (isset($node->main['indicatore_gestionale'])?$node->main['indicatore_gestionale']:''),
        '#parents' => array('main','indicatore_gestionale'),
        '#weight' => 8,
    );

    $form['main']['dot4']['benefici_s'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Strutturali'),
        '#collapsible'=> FALSE,
        '#weight' => 9,
    );
    $form['main']['dot4']['benefici_s']['strutturali'] = array(
        '#type' => 'checkboxes',
        '#title' => t(''),
        '#options' => get_strutturali(),
        '#default_value'=> (isset($node->main['strutturali'])?$node->main['strutturali']:array()),
        '#parents' => array('main','strutturali'),
        '#weight' => 10,
    );
    $form['main']['dot4']['benefici_s']['altri_benefici_s']=array(
        '#type'=>'textarea',
        '#title' => t("Altro.Specificare"),
        '#default_value'=> (isset($node->main['altri_benefici_s'])?$node->main['altri_benefici_s']:''),
        '#parents' => array('main','altri_benefici_s'),
        '#weight' => 11,
    );
    $form['main']['dot4']['benefici_sic'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Sicurezza'),
        '#collapsible'=> FALSE,
        '#weight' => 12,
    );
    $form['main']['dot4']['benefici_sic']['sic_pazienti'] = array(
        '#type'=>'textarea',
        '#title' => t('Riduzione dei rischi per i pazienti'),
        '#default_value'=> (isset($node->main['sic_pazienti'])?$node->main['sic_pazienti']:''),
        '#parents' => array('main','sic_pazienti'),
        '#weight' => 13,
        '#rows' =>2 ,
    );
    $form['main']['dot4']['benefici_sic']['sic_operatori']=array(
        '#type'=>'textarea',
        '#title' => t("Riduzione dei rischi per gli operatori"),
        '#default_value'=> (isset($node->main['sic_operatori'])?$node->main['sic_operatori']:''),
        '#parents' => array('main','sic_operatori'),
        '#weight' => 14,
        '#rows' =>2 ,
    );

    // punto CINQUE della scheda
    $form['main']['dot5']=array(
        '#type'=>'fieldset',
        '#title'=>t('5. Possibilit&agrave; di integrazione'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#weight' => ++$dot
    );
    $form['main']['dot5']['integrazione'] = array(
        '#type' => 'checkboxes',
        '#title' => t(''),
        '#default_value'=> (isset($node->main['integrazione'])?$node->main['integrazione']:array()), // default 'con sistema informativo aziendale'
        '#options' => get_integrazione(),
        '#parents' => array('main','integrazione'),
    );

    // punto SEI della scheda
    $form['main']['dot6']=array(
        '#type'=>'fieldset',
        '#title'=>t('6. Documentazione bibliografica(<b>obbligatorio per tecnologie nuove</b>)'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#weight' => ++$dot
    );
    $form['main']['dot6']['articoli']=array(
        '#type'=>'textarea',
        '#rows'=> 2,
        '#title' => t("Articoli bibliografici correlati"),
        '#description'=>'Indicare i titoli della bibliografia a supporto dell\'attrezzatura. Allegare i file di documentazione
							mediante i pulsanti a fondo pagina.',
        '#default_value'=> (isset($node->main['articoli'])?$node->main['articoli']:''),
        '#parents' => array('main','articoli'),
        //			'#required' => TRUE,
        '#weight' => 1,
        '#element_validate'=>array('validate_Articoli'),
    );
    $form['main']['dot6']['linee_guida']=array(
        '#type'=>'textfield',
        '#title' => t('Linee guida'),
        '#description' => t("Esistenza di linee guida per l'impiego dell'apparecchiatura"),
        '#default_value'=> (isset($node->main['linee_guida'])?$node->main['linee_guida']:''),
        '#parents' => array('main','linee_guida'),
        '#weight' => 2,

    );
    // punto SETTE della scheda
    $form['main']['dot7']=array(
        '#type'=>'fieldset',
        '#title'=>t('7. Fabbisogni'),
        '#collapsible'=> TRUE,
        '#tree' => FALSE,
        '#weight' => ++$dot
    );
    $dot_int=0;
    $form['main']['dot7']['personale'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Risorse umane'),
        '#collapsible'=> FALSE,
        '#weight' => 1,
    );
    $form['main']['dot7']['personale']['ore_sett'] = array(
        '#type' => 'textfield',
        '#title' => t('Risorse umane aggiuntive necessarie'),
        '#description' => t('Indicare le ore uomo per settimana,eventualmente utilizzare un valore negativo per indicare il risparmio di risorse '),
        '#default_value'=> (isset($node->main['ore_sett'])?$node->main['ore_sett']:'0'),
        '#parents' => array('main','ore_sett'),
        '#weight' => 2,
        '#element_validate'=>array('validate_integer'),
    );
    $form['main']['dot7']['personale']['formazione']=array(
        '#type'=>'textfield',
        '#title' => t("Formazione specifica/specialistica"),
        '#description' => t('specificare la tipologia di formazione necessaria'),
        '#default_value'=> (isset($node->main['formazione'])?$node->main['formazione']:'NO'),
        '#parents' => array('main','formazione'),
        '#weight' => 3,
    );
    $form['main']['dot7']['strutturali'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Risorse strutturali'),
        '#collapsible'=> FALSE,
        '#weight' => 4,
    );

    $form['main']['dot7']['strutturali']['edili']=array(
        '#type'=>'radios',
        '#options' => array('No','Si'),
        '#title' => t("Richiede interventi edilizi?"),
        '#default_value'=> (isset($node->main['edili'])?$node->main['edili']:0),
        '#parents' => array('main','edili'),
        '#weight' => 5,
    );
    $form['main']['dot7']['strutturali']['impianti']=array(
        '#type'=>'checkboxes',
        '#options' => get_impianti(),
        '#title' => t("Richiede interventi sugli impianti?"),
        '#default_value'=> (isset($node->main['impianti'])?$node->main['impianti']:array()),
        '#parents' => array('main','impianti'),
        '#weight' => 6,
    );

    $form['main']['dot7']['strutturali']['informatica']=array(
        '#type'=>'textfield',
        '#title' => t("Richiede infrastrutture informatiche?"),
        '#description' => t('Se si specificare quali come ad esempio: computer, rete dati, software specifici etc.'),
        '#default_value'=> (isset($node->main['informatica'])?$node->main['informatica']:'NO'),
        '#parents' => array('main','informatica'),
        '#weight' => 7,
    );
    $form['main']['dot7']['beni'] =array(
        '#type'=>'fieldset',
        '#title'=>t('Beni'),
        '#collapsible'=> FALSE,
        '#weight' => 8,
    );
    $form['main']['dot7']['beni']['mat_consumo']=array(
        '#type'=>'textfield',
        '#title' => t("Richiede materiali di consumo?"),
        '#description' => t('Se si specificare quali, il costo indicativo e la quantit&agrave;/anno'),
        '#default_value'=> (isset($node->main['mat_consumo'])?$node->main['mat_consumo']:''),
        '#parents' => array('main','mat_consumo'),
        '#required' =>  !(user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)) , //i gestori non devono necessariametne compilare tutti i campi,
        '#weight' => 9,
    );

// punto OTTO della scheda
    $form['main']['dot8']=$form['classify']['value_field'];

    unset($form['classify']['value_field']);
    $form['main']['dot8']['#weight'] =++$dot;
    $form['main']['dot8']['value']['#title']=t('8. Costo presunto');



    // punto NOVE della scheda
    $form['main']['alternative']=array(
        '#type'=>'textarea',
        '#title'=>t('9. Alternative'),
        '#description'=>t('Indicare eventuali alternative tecnologiche e/o organizzative per soddisfare la stessa funzione. Indicare inoltre le alternative in uso attualmente in azienda (se ci sono)'),
        '#collapsible'=> FALSE,
        '#default_value'=> (isset($node->main['alternative'])?$node->main['alternative']:''),
        '#parents' => array('main','alternative'),
        '#weight' => ++$dot
    );

    // punto DIECI della scheda
    $form['main']['dot10']=array(
        '#type'=>'fieldset',
        '#title'=>t('10. Fornitori/Utilizzatori'),
        '#collapsible'=> FALSE,
        '#tree' => FALSE,
        '#weight' => ++$dot
    );
    $form['main']['dot10']['fornitori']=array(
        '#type'=>'textarea',
        '#rows' => 3,
        '#prefix' => '<table><tr><td>',
        '#suffix' => '</td>',
        '#default_value'=> (isset($node->main['fornitori'])?$node->main['fornitori']:''),
        '#title' => t("Fornitori possibili"),
        '#description' => t('indicare almeno 3 possibili fornitori'),
        '#parents' => array('main','fornitori'),
        '#weight' => 0,
        '#element_validate'=>array('validate_text'),
    );
    $form['main']['dot10']['utilizzatori_nuovo']=array(
        '#type'=>'textarea',
        '#rows' => 3,
        '#prefix' => '<td>',
        '#suffix' => '</td></tr></table>',
        '#default_value'=> (isset($node->main['utilizzatori_nuovo'])?$node->main['utilizzatori_nuovo']:''),
        '#title' => t("Utilizzatori attuali"),
        '#description' => t('indicare almeno 3 utilizzatori(referenze)'),
        '#parents' => array('main','utilizzatori_nuovo'),
        '#weight' => 1,
        '#element_validate'=>array('validate_text'),
    );

    return $form;

}

// funzione di verifica sui numeri
function validate_numbers ($element,&$form_state) {
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
        form_error($element,'Il campo '.$element['#title'].' deve essere un numero intero!');
    }
}


// funzione di verifica che, se � selezionata una sostituzione venga anche inserita il riferimento di cosa si sostituisce
function validate_inventario (&$element,$form_state) {
    if (user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)) return; //i gestori non devono necessariametne compilare tutti i campi
    if ($form_state['values']['type_acq']==1 &&
        (trim($element['#value'])=='' ||
        $element['#value']==null))
        form_error($element,'Il campo '.$element['#title'].' deve contenere il numero di inventario essendo una sostituzione!');
}
function validate_Articoli (&$element,$form_state) {
    if (user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)) return; //i gestori non devono necessariametne compilare tutti i campi
    if ($form_state['values']['type_acq']==0 &&
        (trim($element['#value'])=='' ||
        $element['#value']==null)|| (strlen($element['#value'])>_MAXLENTEXT))
        if (strlen($element['#value'])>_MAXLENTEXT) 
            form_error($element,'Il campo '.$element['#title'].' &egrave; troppo lungo si prega di riassumerne i contenuti o allegare un file tramite il link in fondo!');
        else
        form_error($element,'Il campo '.$element['#title'].' deve contenere dei riferimenti bibliografici per la nuova tecnologia richiesta');
}

function validate_motivo (&$element,$form_state) {
    if (user_access(_MANGE_RCQ)||user_access(_FULL_MANGE_RCQ)) return; //i gestori non devono necessariametne compilare tutti i campi
    if ($form_state['values']['type_acq']==1 &&
        (is_array($element['#value'])&& empty($element['#value'])))
        form_error($element,'Il campo '.$element['#title'].' deve contenere il numero di inventario essendo una sostituzione!');
}

function validate_text (&$element,$form_state) {
    if ((strlen($element['#value'])>_MAXLENTEXT))
        form_error($element,'Il campo '.$element['#title'].' &egrave; troppo lungo si prega di riassumerne i contenuti o allegare un file tramite il link in fondo!');
}


function base_view($node, $teaser) {
    $output='';
    if ($teaser) return "
	<label>Richiesta di:</label>".$node->main['descrizione']."(<underline>".get_type_acq($node->type_acq)."<\underline>)
	<br><label>Costo presunto (iva esclusa):</label>".$node->main['costo']."
	<style=\"vertical-align: top;\"><table>

        <tr><th style=\"width: 35%\"></th><th></th></tr>
	<tr><td><label>Descrizione:</label></td><td><strong>".$node->title."</strong></td></tr>
	<tr><td><label>Tipologia dell\'acquisto:</label></td><td><strong>".get_type_acq($node->type_acq)."</strong></td></tr>";
    if ($node->type_acq==2)$rows[] = array(array('data' => t('<h4><strong>Dispositivo per la sicurezza dei lavoratori</strong></h4>'), 'colspan' => '7'));

    if ($node->type_acq==1) {
        $rows[]=array(array('data' => t('<h4><strong>1. Apparecchio o sistema da sostituire:</strong></h4>'), 'colspan' => '7'));
        $rows[]=array("N. Inventario:","<strong>".$node->main['inventario']."</strong>");
        $rows[]=array("Produttore:","<strong>".$node->main['produttore']."</strong>");
        $rows[]=array("Modello:","<strong>".$node->main['modello']."</strong>");
        $rows[]=array("Anno di acquisto:","<strong>".$node->main['anno_acq']."</strong>");
        $rows[]=array("Utilizzatori:","<strong>".$node->main['utilizzatori']."</strong>");

        // punto DUE della scheda
        $rows[] = array(array('data' => t('<h4><strong>2. Motivo della sostituzione:</strong></h4>'.get_fuoriuso($node->main['motivo']).'</strong>'), 'colspan' => '7'));
    }
    // punto TRE della scheda
    $rows[]=array(array('data' => t('<h4><strong>3. Descrizione apparecchiatura o sistema richiesto:</strong></h4>'), 'colspan' => '7'));
    $rows[]=array(array('data' => t('<strong>'.$node->main['descrizione_apparecchiatura'].'</strong>'), 'colspan' => '7'));
    $rows[]=array("Tipologia delle prestazioni.","<strong>".$node->main['tipologia']."</strong>");
    $rows[]=array("Codice nomenclatore ICD9CM","<strong>".$node->main['ICD9CM']."</strong>");
    $rows[]=array("Volume prestazioni","<strong>".$node->main['volume']."</strong>");
    $rows[]=array("Bacino di utenza","<strong>".$node->main['bacino']."</strong>");
    $rows[]=array("Anni di vita presunta","<strong>".$node->main['anni_vita']."</strong>");

    // punto QUATTRO della scheda
    $rows[] = array(array('data' => t('<h4><strong>4. Benefici attesi:</strong></h4>'), 'colspan' => '7'));

    $rows[]=array("Clinici:","
				<ul>
					<li><strong>".get_clinici($node->main['clinici'])."</strong></li>
					<li>Bisogno di salute <strong>".$node->main['nuovo_bisogno']."</strong></li>
					<li>Indicatore di monitoraggio e valore obiettivo:<strong>".$node->main['indicatore_clinico']."</strong></li>
				</ul>"
    );
    $rows[]=array("Gestionali:","
				<ul>
					<li><strong>".get_gestionali($node->main['gestionali'])."</strong></li>
					<li>Riduzione di altre prestazioni: <strong>".$node->main['altri_benefici_g']."</strong></li>
					<li>Indicatore di monitoraggio e valore obiettivo:<strong>".$node->main['indicatore_gestionale']."</strong></li>
				</ul>"
    );
    $rows[]=array("Strutturali:","
				<ul>
					<li><strong>".get_strutturali($node->main['strutturali'])."</strong></li>
					<li>Altro: <strong>".$node->main['altri_benefici_s']."</strong></li>
				</ul>"
    );
    $rows[]=array("Sicurezza:","
				<ul>
					<li>Riduzione dei rischi per i pazienti: <strong>".$node->main['sic_pazienti']."</strong></li>
					<li>Riduzione dei rischi per gli operatori: <strong>".$node->main['sic_operatori']."</strong></li>
				</ul>"
    );
    // punto CINQUE della scheda
    $rows[] = array(array('data' => t('<h4><strong>5. Possibilit&agrave; di integrazione :</strong></h4>'.get_integrazione($node->main['integrazione']).'</strong>'), 'colspan' => '7'));

    // punto SEI della scheda
    $rows[] = array(array('data' => t('<h4><strong>6. Documentazione bibliografica(per apparecchiature nuove):</strong></h4>'), 'colspan' => '7'));
    $rows[]=array("articoli: ",$node->main['articoli']);
    $rows[]=array("Linee guida: ",$node->main['linee_guida']);
    // punto SETTE della scheda
    $rows[] = array(array('data' => t('<h4><strong>7. Fabbisogni:</strong></h4>'), 'colspan' => '7'));

    $rows[]=array("Personale","
				 <ul>
					<li>Risorse umane: <strong>".$node->main['ore_sett']."</strong></li>
					<li>Formazione specifica/specialistica: <strong>".$node->main['formazione']."</strong></li>
				</ul>");
    $rows[]=array("Risoerse strutturali","
				<ul>"
        .($node->main['edili']<>0?"<li><strong>Richiede interventi edili</strong></li>":'').
        "<li>Richiede interventi sugli impianti <strong>".get_impianti($node->main['impianti'])."</strong></li>
					<li>Richiede infrastrutture informatiche: <strong>".$node->main['informatica']."</strong></li>
				</ul>");
    $rows[]=array("Beni","
				<ul>
					<li>Richiede materiali di consumo: <strong>".$node->main['mat_consumo']."<strong></li>
				</ul>");

    // punto OTTO della scheda
    $rows[] = array(array('data' => t('<h4><strong>8.</strong> Costo presunto :<b>'.currencyFormat($node->value,$node->value_vat).'</b></h4>'), 'colspan' => '7'));
    // punto NOVE della scheda
    $rows[] = array(array('data' => t('<h4><strong>9. Alternative:</strong>'.$node->main['alternative'].'</h4>'), 'colspan' => '7'));


    // punto DIECI della scheda
    $rows[] = array(array('data' => t('<h4><strong>10. Fornitori/Utilizzatori</strong></h4>'), 'colspan' => '7'));
    $rows[]=array("Fornitori possibili","Utilizzatori attuali");
    $rows[]=array($node->main['fornitori'],$node->main['utilizzatori_nuovo']);

    return theme('table',array('',''),$rows);
}




/**
 *  form a più passaggi
 * @param <array> $form_state
 * @param <type> $parm1
 */
//TODO: implementare la form per utilizzarla
function multistage_form($form_state,$parm1) {

    if (empty($form_state)) {
        $form['type_acq'] = array(
            '#type' => 'radios',
            '#title' => t('Tipologia dell\'acquisto'),
            '#default_value' => 0,
            '#options' => array('Apparecchiatura nuova','Sostituzione/reintegro apparecchiatura esistente'),
        );
    }
    else {
        if ($form_state['type_acq']['#value']==0) {
            $form['dot1']=array(
                '#type'=>'fieldset',
                '#prefix' => '<ol><li>',
                '#suffix' => '</li>',
                '#title'=>t('1. Apparecchio o sistema da sostituire'),
                '#collapsible'=> FALSE,
                '#weight' => 0
            );
            $form['dot1']['descrizione']=array(
                '#type'=>'textfield',
                '#title'=>t('1. Apparecchio o sistema da sostituire'),
                '#collapsible'=> FALSE,
                '#weight' => 1
            );
            $form['dot2']=array(
                '#type'=>'radios',
                '#prefix' => '<li>',
                '#suffix' => '</li>',
                '#options' => array('fuori uso','fuori norma','obsoleto','costi di gestione elevati','fuori manutenzione', 'altro'),
                '#title'=>t('2. Motivo della sostituzione'),
                '#collapsible'=> FALSE,
                '#weight' => 1
            );

        }
        $form['dot3']=array(
            '#type'=>'fieldset',
            '#prefix' => (($form_state['type_acq']['#value']<>0)?'<ol>':'').'<li>',
            '#suffix' => '</li>',
            '#title'=>t('3. Descrizione apparecchiatura o sistema richiesto'),
            '#description'=>t('S\'intende la fornitura chiavi in mano, quindi completa di accessori e delle eventuali opere di installazione'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot4']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('4. Benefici attesi'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot5']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('5. Possibilit&agrave; di integrazione'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot6']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('6. Documentazione bibliografica(per apparecchiature nuove)'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot7']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('7. Fabbisogni'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot8']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('8. Costo presunto (iva esclusa)'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot9']=array(
            '#type'=>'textarea',
            '#prefix' => '<li>',
            '#suffix' => '</li>',
            '#title'=>t('9. Alternative'),
            '#description'=>t('Indicare eventuali alternative tecnologiche e/o organizzative per soddisfare la stessa funzione'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );
        $form['dot10']=array(
            '#type'=>'fieldset',
            '#prefix' => '<li>',
            '#suffix' => '</li></ol>',
            '#title'=>t('10. Fornitori/Utilizzatori'),
            '#collapsible'=> FALSE,
            '#weight' => 0
        );

    }

}

