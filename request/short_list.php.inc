<?php



include_once('request_aux_function.php.inc');
include_once('request.php.inc');
function retreive_all(){
    $qy= "
        SELECT n.nid as nid, st.short as s, st.description as struttura,dpt.short as d, dpt.description as DPT,
        r.priority as priority, IF(r.value_vat<>0,r.value ,r.value* 1.2) as value, r.type_acq as type_acq
        FROM {node} n INNER JOIN ".query_status(_OPEN)." s ON s.nid=n.nid
        INNER JOIN requests r ON r.nid=n.nid AND r.vid=n.vid
        INNER JOIN (SELECT * , if(parent=0,id,parent) as dpt FROM struttura) st ON st.id=r.struct
        LEFT JOIN struttura dpt ON dpt.id=st.dpt
        ORDER BY dpt.description, st.description, r.priority desc
    ";
//print $qy;

    $qy=db_query($qy);
    $st='';
    $dpt='';
    $pry='';

    while($rs=db_fetch_object($qy)){
        if ($dpt<>$rs->DPT){
            if(!empty($dpt_count)&&!empty($dpt_value)){
                $dpt_output.='Totali per :<b>'.$rs->d.' '.$dpt.'</b><br>';
                foreach($dpt_count as $pry => $val){
                    $dpt_output.='Priorit&agrave;<b> '.get_priority($pry).'</b> richieste '.$dpt_count[$pry].'<br>';
                }
                $rows=array();
                $typ_acq=array();
                $totacq=array();
                foreach($socs as $soc => $key){
                    $row=array();
                    $row[]=$soc;
                    foreach (get_priority(NULL,TRUE) as $p => $tx ) 
                        $row[]=array('data'=>isset($key[$p])?currencyFormat($key[$p]):0,'align'=>'right');
                    $rows[]=$row;
                     $typ_acq[]=array($soc,
                            array('data'=>(isset($key['acq'][0])?currencyFormat($key['acq'][0]):0),'align'=>'right'),
                            array('data'=>(isset($key['acq'][1])?currencyFormat($key['acq'][1]):0),'align'=>'right'),
                            array('data'=>(isset($key['acq'][2])?currencyFormat($key['acq'][2]):0),'align'=>'right'));
                      $totacq[0]+=isset($key['acq'][0])?$key['acq'][0]:0;
                      $totacq[1]+=isset($key['acq'][1])?$key['acq'][1]:0;
                      $totacq[2]+=isset($key['acq'][2])?$key['acq'][2]:0;
                }
                $typ_acq[]=array('<b>Totali</b>',array('data'=>currencyFormat($totacq[0]),'align'=>'right'),
                        array('data'=>currencyFormat($totacq[1]),'align'=>'right'),
                        array('data'=>currencyFormat($totacq[2]),'align'=>'right'));
                $row=array();
                $row[]='<b>Totali</b>';
                foreach (get_priority(NULL,TRUE) as $p => $tx ) 
                    $row[]=array('data'=>isset($dpt_value[$p])?currencyFormat($dpt_value[$p]):0,'align'=>'right');
                $rows[]=$row;
                $hd=array();
                $hd[]='Struttura';
                foreach (get_priority(NULL,TRUE) as $tx ) 
                    $hd[]=array('data'=>$tx,'style'=>'text-align: right;');
                $dpt_output.='<br>'.theme('table',$hd,$rows);
                $dpt_output.='<br>'.theme('table',array('Struttura / Tipo di acquisto ',
                        array('data'=>get_type_acq(0),'style'=>'text-align: right;'),
                        array('data'=>get_type_acq(1),'style'=>'text-align: right;'),
                        array('data'=>get_type_acq(2),'style'=>'text-align: right;')),
                        $typ_acq
                    );
                $dpt_output.='</div>';
            }
            $output.=$dpt_output.$content;
            $content='';
            $dpt=$rs->DPT;
            $st=$rs->struttura;
            $pry=$rs->priority;
            $dpt_output='<div style="page-break-before: always; page-break-after: always;"
                        <h1 style="text-align:center;">'.'<i>'.$rs->d.'</i> '.$rs->DPT.'</h1>';
            $content.='<div style="page-break-before: always; text-align:center;"><h4>'.'<i>'.$rs->s.'</i> '.$rs->struttura.'</h4>';
            $content.='<small><i>'.$rs->d.'</i> '.$rs->DPT.'</small>';
            $content.='<h1> Priorit&agrave;:<b>'.get_priority($rs->priority).'</b></h1></div>';
            $dpt_count=array();
            $dpt_value=array();
            $socs=array();
        }
 /*        if ($st<>$rs->struttura){
            $st=$rs->struttura;
            $socs[]=$st;
           $output.='<div style="page-break-before: always; text-align:center;"><h4>'.'<i>'.$rs->s.'</i> '.$rs->struttura.'</h4>';
            $output.='<small><i>'.$rs->d.'</i> '.$rs->DPT.'</small></div>';
        }*/
        if ($pry<>$rs->priority || $st<>$rs->struttura){
//            if ($st<>$rs->struttura) $socs[$rs->struttura]['acq'][$rs->type_acq]=0;
            $pry=$rs->priority;
            $st=$rs->struttura;
            $content.='<div style="page-break-before: always; text-align:center;"><h4>'.'<i>'.$rs->s.'</i> '.$rs->struttura.'</h4>';
            $content.='<small><i>'.$rs->d.'</i> '.$rs->DPT.'</small>';
            $content.='<h1> Priorit&agrave;:<b>'.get_priority($rs->priority).'</b></h1></div>';
//            $dpt_count[$pry]=0;
//            $dpt_value[$pry]=0;
            $socs[$rs->struttura][$pry]=0;

        }
        $dpt_count[$pry]++;
        $socs[$rs->struttura][$pry]+=$rs->value;
        $dpt_value[$pry]+=$rs->value;
        $socs[$rs->struttura]['acq'][$rs->type_acq]+=$rs->value;
        $content .='<div style="page-break-inside: avoid;">'. get_node(node_load(array('nid'=>$rs->nid))).'</div>';
    }
            if(!empty($dpt_count)&&!empty($dpt_value)){
                $dpt_output.='Totali per :<b>'.$rs->d.' '.$dpt.'</b><br>';
                foreach($dpt_count as $pry => $val){
                    $dpt_output.='Priorit&agrave;<b> '.get_priority($pry).'</b> richieste '.$dpt_count[$pry].'<br>';
                }
                $rows=array();
                $typ_acq=array();
                $totacq=array();
// riassuntivi per struttura                
                foreach($socs as $soc => $key){
//conteggi per priorità                    
                    $row=array();
                    $row[]=$soc;
                    foreach (get_priority(NULL,TRUE) as $p => $tx ) 
                        $row[]=array('data'=>isset($key[$p])?currencyFormat($key[$p]):0,'align'=>'right');
                    $rows[]=$row;
// conteggi per tipologia di ACQ
                     $typ_acq[]=array($soc,
                            array('data'=>(isset($key['acq'][0])?currencyFormat($key['acq'][0]):0),'align'=>'right'),
                            array('data'=>(isset($key['acq'][1])?currencyFormat($key['acq'][1]):0),'align'=>'right'),
                            array('data'=>(isset($key['acq'][2])?currencyFormat($key['acq'][2]):0),'align'=>'right'));
                      $totacq[0]+=isset($key['acq'][0])?$key['acq'][0]:0;
                      $totacq[1]+=isset($key['acq'][1])?$key['acq'][1]:0;
                      $totacq[2]+=isset($key['acq'][2])?$key['acq'][2]:0;
                }
                $typ_acq[]=array('<b>Totali</b>',array('data'=>currencyFormat($totacq[0]),'align'=>'right'),
                        array('data'=>currencyFormat($totacq[1]),'align'=>'right'),
                        array('data'=>currencyFormat($totacq[2]),'align'=>'right'));
 // riga totali di acq x priorità  
                $row=array();
                $row[]='<b>Totali</b>';
                foreach (get_priority(NULL,TRUE) as $p => $tx ) 
                    $row[]=array('data'=>isset($dpt_value[$p])?currencyFormat($dpt_value[$p]):0,'align'=>'right');
                $rows[]=$row;
 // crea intestazione e tabella acq x priorità
                $hd=array();
                $hd[]='Struttura / priorit&agrave; ';
                foreach (get_priority(NULL,TRUE) as $p => $tx ) 
                    $hd[]=array('data'=>$tx,'style'=>'text-align: right;');
                $dpt_output.='<br>'.theme('table',$hd,$rows);
// crea intestazione e tabella acq x tipologia                
                $dpt_output.='<br>'.theme('table',array('Struttura / Tipo di acquisto ',
                        array('data'=>get_type_acq(0),'style'=>'text-align: right;'),
                        array('data'=>get_type_acq(1),'style'=>'text-align: right;'),
                        array('data'=>get_type_acq(2),'style'=>'text-align: right;')),
                        $typ_acq
                    );
                $dpt_output.='</div>';
            }
    $output.=$dpt_output.$content;
    return $output;
}

function get_node($node){
/*       $output= "
	<label>Richiesta di:</label>".$node->main['descrizione']."(<b>".get_type_acq($node->type_acq).'<\b>'.")
	<br><label>Costo presunto (iva esclusa):</label>".currencyFormat($node->value,$node->value_vat)."
	<style=\"vertical-align: top;\"><table>

        <tr><th style=\"width: 35%\"></th><th></th></tr>
	<tr><td><label>Descrizione:</label></td><td><strong>".$node->title."</strong></td></tr>
	<tr><td><label>Tipologia dell'acquisto:</label></td><td><strong>".get_type_acq($node->type_acq)."</strong></td></tr>";
*/
 //       $rows[]=Array('<label>Titolo:</label>','<strong>'.$node->title.'</strong>');
        $output.='<br><h3 style="border-style: solid ;
                                border-color: black;
                                border-width: 1px;
                                padding-left: 4px;
                                padding-right: 4px;
                                padding-top: 4px;
                                padding-bottom: 4px;">Id:'.$node->nid.' - <b>'.$node->title.' </b></h3>';
        $rows[]=array('Descrizione:',$node->main['descrizione_apparecchiatura']);
        $rows[]=array('Priorit&agrave;:','<strong>'.get_priority($node->priority).'</strong>');
        if ($node->type_acq==2)$rows[] = array(array('data' => t('Dispositivo per la sicurezza dei lavoratori'), 'colspan' => '7'));

	if ($node->type_acq==1) {
		$rows[]=array('Apparecchio o sistema da sostituire:','Prod:'.$node->main['produttore'].
                    ' mod:'.$node->main['modello'].' Anno:'.$node->main['anno_acq']);
/*		$rows[]=array("N. Inventario:","<strong>".$node->main['inventario']."</strong>");
		$rows[]=array("Produttore:","<strong>".$node->main['produttore']."</strong>");
		$rows[]=array("Modello:","<strong>".$node->main['modello']."</strong>");
		$rows[]=array("Anno di acquisto:","<strong>".$node->main['anno_acq']."</strong>");
		$rows[]=array("Utilizzatori:","<strong>".$node->main['utilizzatori']."</strong>");
*/
		 // punto DUE della scheda
		$rows[] = array('Motivo della sostituzione:','<strong>'.get_fuoriuso($node->main['motivo']).'</strong>');
	}
	// punto TRE della scheda
//	$rows[]=array(array('data' => t('<h4><strong>3. Descrizione apparecchiatura o sistema richiesto:</strong></h4>'), 'colspan' => '7'));

	$rows[]=array("Tipologia delle prestazioni.","<strong>".$node->main['tipologia']."</strong>");
/*	$rows[]=array("Codice nomenclatore ICD9CM","<strong>".$node->main['ICD9CM']."</strong>");
	$rows[]=array("Volume prestazioni","<strong>".$node->main['volume']."</strong>");
	$rows[]=array("Bacino di utenza","<strong>".$node->main['bacino']."</strong>");
	$rows[]=array("Anni di vita presunta","<strong>".$node->main['anni_vita']."</strong>");
*/
	// punto QUATTRO della scheda


	$rows[]=array("Benefici clinici:","

					Bnefici clinici: <strong>".get_clinici($node->main['clinici'])."</strong><br>
					Bisogno di salute: <strong>".$node->main['nuovo_bisogno']."</strong><br>
					Indicatore di monitoraggio e valore obiettivo:<strong>".$node->main['indicatore_clinico']."</strong><br>
				"
				);
	$rows[]=array("Benefici gestionali:","
					<strong>".get_gestionali($node->main['gestionali'])."</strong><br>
					Riduzione di altre prestazioni: <strong>".$node->main['altri_benefici_g']."</strong><br>
					Indicatore di monitoraggio e valore obiettivo:<strong>".$node->main['indicatore_gestionale']."</strong><br>
				"
				);
/*	$rows[]=array("Strutturali:","
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
*/
	// punto OTTO della scheda
	$rows[] = array('Costo presunto per <b>'.$node->qta.'</b> pezzi:','<h4><b>'.currencyFormat($node->value,$node->value_vat).'</b></h4>');
	// punto NOVE della scheda
//	$rows[] = array(array('data' => t('<h4><strong>9. Alternative:</strong>'.$node->main['alternative'].'</h4>'), 'colspan' => '7'));



	return $output.theme('table',array('',''),$rows);
}