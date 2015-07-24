<?php

/********************************************************************************************
------------------------------------------- aux functions -----------------------------------
*********************************************************************************************/
/*

constant and functions for request module
*/

define ("_REQUESTS_TABLE",'requests'); 	//define new node addon table for module requests

/**
 *   Authorization profile
 */
define ("_VIEW_RCQ",'view request');  			//only view request status
define ("_INSERT_RCQ",'insert request');			//only insert request
define ("_MODIFY_RCQ",'modify own request' );		//owner only change
define ("_MANGE_RCQ",'manage only request' );		//manage only
define ("_FULL_MANGE_RCQ",'modify all request' );		//modify all

/*definizione dello stato */
define ("_DELETED",1); 	   // richiesta cancellata dalle liste
define ("_REFUSED",2);
define ("_TEST",80);
define ("_OPEN",100);          // aperta e pendente
define ("_EVALUTATE",200);  // in fase di valutazione (bloccata la modifica)
define ("_RIVALUTATE",201);  // in fase di rivalutazione (bloccata la modifica)

/* da qui in poi le richieste entrano in lavorazione e una loro modifica potrebbe comportare problemi grossi */
define ("_APPROVED",300);	   // in lista per l'acquisto
define ("_TEC_DOC",301);	   //  documentazione tecnica per acq
define ("_PUBLISHED",350);         //  pubblicazione
define ("_MOVED",400);	   	// importo ridestinato
define ("_CLOSED",1000);	   // evasa, acquisto avvenuto
define ("_TESTED",2000);	   // collaudata



/*define ("_PAGER_DEFAULT_VALUE",50);	   // record per pagina
  define ("_PAGER_DEFAULT_LABEL",'pager_requests');	   // system variable label
*/


/* Abilita l'inserimento delle richieste pregresse approvate*/
define ("_PREGRESSE",FALSE);


/*
 unique hook_perm funtion
*/

function request_permission() {
    $data= array(
    	_VIEW_RCQ,
        _INSERT_RCQ,
        _MODIFY_RCQ,
        _MANGE_RCQ,
        _FULL_MANGE_RCQ
    );
    return $data;
}
/**
 *  selezione stati di default
 * @return <type>
 */
define ("_PENDING_REQUESTS",' r.rcq_status IN ('._APPROVED.','._OPEN.')'); // richieste non approvate e pendenti


function request_status_default() {
    return  array(
    _DELETED=>0, 	   // richiesta cancellata dalle liste
    _TEST=>0,          // aperta e pendente
    _OPEN=>_OPEN,          // aperta e pendente
    _EVALUTATE=>0,  // in fase di valutazione (bloccata la modifica)
    _APPROVED=>_APPROVED,	   // in lista per l'acquisto
    //		   _RIVALUTATE=>0,	   // in corso di acquisto
    _TEC_DOC=>0,
    _PUBLISHED=>0,
    _REFUSED=>0, 	   // richiesta cancellata dalle liste
    _CLOSED=>0,
    _TESTED=>0);	   // evasa, acquisto avvenuto
}

/**
 *  hook _status_message, ritorna le informazioni sullo  stato o la lista degli stati disponibili
 * @param <type> $sel
 *      id dello stato da mostrare
 * @return <type>
 *      descrizione dello stato (se sel <> NULL) o la lista degli stati disponibili
 */
function request_status_message($sel=NULL) {
    $data=array (
        _DELETED=>'Cancellata', 	// richiesta cancellata dalle liste
        _TEST=>'In prova',				// prove per richieste
        _OPEN=>'Proposta',              // aperta e pendente
        _EVALUTATE=>'In valutazione',   // in fase di valutazione
        _APPROVED=>'Approvata',         // in lista per l'acquisto
        //	   _RIVALUTATE=>'Da rivalutare',
        _TEC_DOC=>'Doc.Tecnica OK ',	// documentazione tecnica pronta
        _PUBLISHED=>'Pubblicata',       //pubblicata la gara
        _REFUSED=>'Rifiutata',          // richiesta cancellata dalle liste
        _MOVED=>'Sostituita',           //richiesta approvata ma "sostituita" e i fondi vengono ridestinati
        _CLOSED=>'Acquistata',          // evasa, acquisto avvenuto
        _TESTED=>'Collaudata');         // collaudata

    if ($sel!==NULL) return $data[$sel];
    else return $data;
}

//workflow degli stati
function request_status_check($current_status,$new_status,$nid=NULL) {
    $msg='';
    switch ($current_status){
        case _DELETED    :
            if ($new_status == _EVALUTATE || $new_status == _OPEN)
                return TRUE;
        break;
        case _TEST       :
            if ($new_status == _OPEN || $new_status == _EVALUTATE || $new_status == _REFUSED || $new_status == _DELETED)
                return TRUE;
        break;
        case _OPEN       :
            if ($new_status == _EVALUTATE || $new_status == _APPROVED || $new_status == _REFUSED|| $new_status == _DELETED)
                return TRUE;
        break;
        case _RIVALUTATE :
        case _EVALUTATE  :
            if ($new_status == _OPEN || $new_status == _APPROVED || $new_status == _REFUSED || $new_status == _DELETED)
                return TRUE;
        break;
        case _APPROVED   :
            if ($new_status == _TEC_DOC || $new_status == _TESTED)
                return TRUE;
        break;
        //	   _RIVALUTATE=>'Da rivalutare',
        case _TEC_DOC    :
            if ($new_status == _PUBLISHED || $new_status == _CLOSED || $new_status == _TESTED)
                return TRUE;
        break;
        case _PUBLISHED  :
            if ($new_status == _CLOSED || $new_status == _TESTED)
                return TRUE;
        break;
        case _REFUSED    :
            if ($new_status == _EVALUTATE || $new_status == _OPEN)
                return TRUE;
        break;
        case _MOVED      :
            if ($new_status == _PUBLISHED)
                return TRUE;
        break;
        case _CLOSED     :
            if ($new_status == _TESTED)
            return TRUE;
        break;
        case _TESTED     :
            return "La richiesta $nid &egrave; stata gi&agrave; collaudata non pu&ograve; cambiare stato";
        break;
    }
    return "La richiesta $nid &egrave; <b>".request_status_message($current_status)."</b> e non pu&ograve; essere cambiata in <b>".request_status_message($new_status)."</b>";
/*
    switch ($new_status) {
        case _EVALUTATE:
            if ($status ==_OPEN )
                $msg.="La richiesta $nid non pu&ograve; essere completata perch&egrave; gi&agrave; stata approvata.<br>";
            break;
        case _OPEN:
            if (!($status != _APPROVED))
                $msg.="La richiesta $nid &egrave; stata approvata non pu&ograve; essere rivalutata.<br>";
            break;
        case _APPROVED:
            if (!($status ==_EVALUTATE || $status==_RIVALUTATE))
                $msg.="La richiesta $nid &egrave; gi&agrave; stata approvata.<br>";
            break;
        case :_REFUSED
            if (!($status ==_EVALUTATE || $status==_RIVALUTATE))
                $msg.="La richiesta $nid pu&ograve; essere rifiutata solo se non &egrave; gi&agrave; stata approvata.<br>";
            break;
        case _RIVALUTATE:
            if !(($status ==_DELETED || $status==_REFUSED))
                $msg.="La richiesta $nid non pu&ograve; essere rivalutata.<br>";
            break;
        case _TEC_DOC:
            if (!($status ==_APPROVED))
                $msg.="La richiesta $nid non &egrave; approvata.<br>";
            break;
        case _MOVED:
            if (!($status == _APPROVED || $status==_TEC_DOC))
                $msg.="La richiesta $nid non &egrave; approvata e non ci sono fondi da recuperare<br>";
            break;
        case _DELETED:
            if (!($status != _APPROVED))
                $msg.="La richiesta $nid non pu&ograve; essere cancellata perch&egrave; &egrave; approvata.<br>";
            break;
        case _CLOSED:
            if (!($status == _APPROVED || $status == _MOVED || $status==_TEC_DOC))
               $msg.="La richiesta $nid non pu&ograve; essere chiusa perch&egrave; non &egrave; approvata .<br>";
            break;
    }
    if ($msg<>'') {
        drupal_set_message("$msg",'warning',TRUE);
        return FALSE;

    else {
        if ($set_nid!==NULL) set_status($nid,$new_status);
        return TRUE;
    }
 *
 */
}


/*------------------------------------------------
functions for select, option buttons and checkbox
--------------------------------------------------*/
function get_reasons($sel=NULL) {
    $data=array (
        1=>'Aggiornamento parco macchine',
        2=>'Ad integrazione parco esistente',
        3=>'Ad integrazione ed aggiornamento',
        4=>'Nuova tecnologia');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}
function get_priority($sel=NULL, $force_all=FALSE) {
    static $user;
    $data=array (
        0=>'Nessuna priorit&agrave;',
        1=>'bassa',
        2=>'media',
        3=>'alta'
     );
    if(user_access(_MANGE_RCQ) || user_access(_FULL_MANGE_RCQ) || $sel!==NULL|| $force_all) {
       // le priorità più alte abilitate solo per gli amministratori ma visibili a tutti 
       $data[10]='Sostituzione URGENTE';
       $data[20]='Integrazione finanziamento';
       $data[30]='Investimento vincolato';
    }
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}

function get_classification($sel=NULL) {
    $data=array (
        0=>'Nessuna classificazione',
        1=>'Biomedicale',
        2=>'Economale',
        3=>'Informatica');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}



function get_type_acq($sel=NULL) {
    $data=array(0=>'Nuovo', 1=>'Sostituzione', 2=>'Per la sicurezza');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}

//* mappa dello stato delle richieste
//  ci possono essere dei sotto stati da definire in fase successiva


function get_planning($sel=NULL) {
    $data=array (
        0=>'Non pianificata',
        1=>'Primo semestre Urgente (A+)',
        2=>'Primo semestre (A)',
        3=>'Secondo semestre (B)',
        4=>'Terzo semestre (C)',
        5=>'Vedere Note',
    );

    if ($sel!==NULL) return $data[$sel];
    else return $data;
}


function get_fuoriuso ($sel=NULL) {
    return main_option_selector(array(1=>'fuori uso',2=>'fuori norma',3=>'obsoleto',4=>'costi di gestione elevati',5=>'fuori manutenzione'),
    $sel);
}

function get_clinici ($sel=NULL) {
    return main_option_selector(array(1=>'Aumento appropriatezza', 2=>'Aumento efficacia', 3=>'Minore invasivit&agrave;', 4=>'Aumento efficienza'),
    $sel);
}
function get_gestionali ($sel=NULL) {
    return main_option_selector(array(1=>'minor impiego personale',
    2=>'riduzione tempi diagnostico/terapeutici',
    3=>'minori tempi di assistenza',
    4=>'Aumento efficienza'),
    $sel);
}
function get_strutturali ($sel=NULL) {
    return main_option_selector(array(1=>'requisiti minimi DPR 14/1/97'),
    $sel);
}
function get_integrazione ($sel=NULL) {
    return main_option_selector(array(1=>'con altre apparecchiature/sistemi',
    2=>'con sistema informativo aziendale',
    3=>'con altre unit&agrave; operative'),
    $sel);
}

function get_impianti ($sel=NULL) {
    return main_option_selector(array(1=>'Idrici',2=>'Gas medicali',3=>'Elettrico',4=>'Condizionamento',5=>'Telefonico/Dati'),
    $sel);
}

function get_money($sel=NULL) {
    $data=array (
        1=>'A piano (conto capitale)',
        2=>'Service',
        3=>'Donazione',
        4=>'Comodato gratuito');
    if ($sel!==NULL) return $data[$sel];
    else return $data;
}

function update_rcq_status($nid,$status,$message){
    $node=node_load($nid);
    $node->rcq_status=$status;
    $node->log=$message;
    $node->revision=TRUE;
    node_save($node);
}