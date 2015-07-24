<?php
define (_TCH_VERSION,'1.0');

function tech_form($node){
    $pos=1;
    $form['systesi']=array(
        '#type' => 'textarea',
        '#title' => 'Descrizione',
        '#description'=>'DESCRIZIONE SINTETICA GENERALE DELLA TECNOLOGIA',
        '#rows' => 3,
        '#default_value'=> isset($node->main['descrizione_apparecchiatura'])?$node->main['descrizione_apparecchiatura']:'',
        '#required' => TRUE,
        
    );
    $form['motivazioni']=array(
        '#type' => 'textarea',
        '#title' => 'INTEGRAZIONE O SOSTITUZIONE DI APPARECCHIATURE ',
        '#description'=>'SPIEGARE LE RAGIONI DELL’INSORGENZA DELL’ESIGENZA',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['uso']=array(
        '#type'=>'fieldset',
        '#title'=>'DESTINAZIONE D\'USO E REQUISITI CLINICI',
        '#description'=>'INDICARE LA DESTINAZIONE D\'USO ED IL DETTAGLIO DELLE PRESTAZIONI DIAGNOSTICHE O TERAPEUTICHE DA EROGARE (per ordine di importanza)',
        '#collapsible'=>TRUE,
        '#collapsed'=>FALSE,
        
    );
        $form['uso']['dest-uso']=array(
            '#type' => 'textarea',
            '#title' => 'DESTINAZIONE D\'USO',
            '#description'=>'RISPETTO AL PARCO MACCHINE DELLO STESSO TIPO IN DOTAZIONE',
            '#rows' => 3,
            '#default_value'=> '',
            '#required' => TRUE,
        );
        $form['uso']['prestazioni']=array(
            '#type' => 'textarea',
            '#title' => 'DETTAGLIO PRESTAZIONI ',
            '#description'=>'per ordine di importanza',
            '#rows' => 3,
            '#default_value'=> '',
            '#required' => TRUE,
            
        );
    $form['config']=array(
        '#type'=>'fieldset',
        '#title'=>'CONFIGURAZIONE DI SISTEMA',
        '#description'=>'INDICARE, QUALORA PERTINENTE E POSSIBILE, GLI ELEMENTI ESSENZIALI INDISPENSABILI A COMPORRE IL SISTEMA/L\'APPARECCHIATURA (componenti hardware e software principali ed, eventualmente, secondari ed accessori)',
        '#collapsible'=>TRUE,
        '#collapsed'=>FALSE,
        
    );
    $form['config']['main']=array(
        '#type' => 'textarea',
        '#title' => 'COMPONENTI PRINCIPALI',
        '#description'=>'',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['config']['accessories']=array(
        '#type' => 'textarea',
        '#title' => 'COMPONENTI ACCESSORI ',
        '#description'=>'',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['config']['desired']=array(
        '#type' => 'textarea',
        '#title' => 'COMPONENTI ACCESSORI GRADITI',
        '#description'=>'Opzioni non strettametne necessarie',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['config']['altro']=array(
        '#type' => 'textarea',
        '#title' => 'Altro',
        '#description'=>'tutto ci&ograve; che non fa parte dell\'apparecchiatura
                         ma che &egrave; funzionale al suo utilizzo o accessorio all\'ergonimia del lavoro',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
       
    );
     $form['essentials']=array(
        '#type'=>'textarea',
        '#title'=>'REQUISITI ESSENZIALI',
        '#description'=>'ESPRIMERE VINCOLI NOTI E/O PARERI (SPIEGANDONE BREVEMENTE LE RAGIONI) CIRCA LE <b>CARATTERISTICHE TECNICO-PRESTAZIONALI</b>
                         CHE LA TECNOLOGIA DEVE POSSEDERE PER GARANTIRE L\'EROGAZIONE DELLE PRESTAZIONI INDICATE AL
                         PRECEDENTE PUNTO NELLE DIVERSE POSSIBILI CONDIZIONI OPERATIVE PREVISTE
                         (quali: ingombro e peso, maneggevolezza etc.., dimensione e caratteristiche
                          delle risorse hardware e software componenti l\'intero sistema, ulteriori caratteristiche
                          tecniche di rilievo, integrazioni e compatibilit&agrave; con altri sistemi,
                          altre peculiarit&agrave; funzionali, ….)',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['tech-altro']=array(
        '#type' => 'textarea',
        '#title' => 'ALTRE CARATTERISTICHE TECNICO-PRESTAZIONALI',
        '#description'=>'CIO\' CHE SI RITIENE OPPORTUNO CONSIDERARE NELLA FASE DI VALUTAZIONE
                        IN CORSO DI GARA PER UN CORRETTO GIUDIZIO DELLE SOLUZIONI OFFERTE',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['exclusiv']=array(
        '#type' => 'textarea',
        '#title' => 'CARATTERISTICHE ESCLUSIVE',
        '#description'=>'EVIDENZIARE E DESCRIVERE GLI EVENTUALI REQUISITI TECNICI IDENTIFICATI
                         COME INDISPENSABILI CHE PRESAGISCANO  L\'ESCLUSIVITA\' DI  UNO SPECIFICO
                         PRODOTTO O UNA FORTE LIMITAZIONE DI SCELTA SUL MERCATO',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['rcq-optional']=array(
        '#type' => 'textarea',
        '#title' => 'REQUISITI OPZIONALI',
        '#description'=>'INDICARE E DESCRIVERE LE EVENTUALI FUNZIONI ULTERIORI NON INDISPENSABILI
                        MA UTILI (che potrebbero essere richieste in opzione)',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
    $form['market']=array(
        '#type' => 'textarea',
        '#title' => 'MERCATO',
        '#description'=>'INDICARE PRODOTTI SUL MERCATO E RELATIVE PECULIARITA’ DI
                         CUI SI SIA A CONOSCENZA (ALLEGARE DOCUMENTAZIONE RACCOLTA)',
        '#rows' => 3,
        '#default_value'=> (isset($node->main['alternative'])?'Esistono le seguenti alternative: '.$node->main['alternative']:'').
                           (isset($node->main['fornitori'])?'esistono i seguenti fornitori: '.$node->main['fornitori']:''),
        '#required' => TRUE,
        
    );
    $form['what_done']=array(
        '#type' => 'textarea',
        '#title' => 'RELAZIONE',
        '#description'=>'ESPRIMERE EVENTUALE PARERE PREVENTIVO SULLE APPARECCHIATURE GIA\' NOTE/ESAMINATE (fra quelle sopra elencate)',
        '#rows' => 3,
        '#default_value'=> '',
        '#required' => TRUE,
        
    );
/*
    $parent='tech_info,'._TCH_VERSION;// per i puntatori 'parent' nella form
    $p=$node['main']['tech_info'][_TCH_VERSION];
    // riempimento sistematico dei campi già compliati
    foreach($p as $key => $value){
        //parsing deri campi normali
        if (isset($form[$key]) && $form[$key]['#type']=='textarea'){
            $form[$key]['#default_value']=$value;
//            $form[$key]['#parents']=explode(',', $parent.','.$key);
        }
        //parsing dei filedset
        foreach(element_children($form) as $element =>$content){
            if ($content['#type']=='fieldset'){
               //scansione degli elemnti coincidenti
               if(isset($content[$key]) && $content[$key]['#type']=='textarea'){
                    $content[$key]['#default_value']=$val;
//                    $content[$key]['#parents']=explode(',', $parent.','.$key);
                }
            }
        }
    }
 * 
 */
    return $form;
}
/**
 * funzione di visualizzazione dei dati tecnici
 * @param <array> $source dati da mostrare strutturati opportunamente
 * @return <html>
 */
function tech_view($source){
    $p=$source['tech_info'];
    switch (key($p)) {
        case '1.0':
             return teach_view_1_0($p['1.0']);
    }
}

function teach_view_1_0($s){
    $header=array('Descrizione<br><span class="description">DESCRIZIONE SINTETICA GENERALE DELLA TECNOLOGIA</span>');
    $rows[]=array($s['systesi']);
    $output.=theme('table',$header,$rows);
    unset($rows);
//-----------------------------------    
    $header=array('INTEGRAZIONE O SOSTITUZIONE DI APPARECCHIATURE
                <br><span class="description">SPIEGARE LE RAGIONI DELL\'INSORGENZA DELL\'ESIGENZA</span>');

    $rows[]=array($s['motivazioni']);
    $output.=theme('table',$header,$rows);
    unset($rows);
//-----------------------------------        
            
    
    $header=array('DESTINAZIONE D\'USO E REQUISITI CLINICI
                <br><span class="description">INDICARE LA DESTINAZIONE D\'USO ED
                IL DETTAGLIO DELLE PRESTAZIONI DIAGNOSTICHE O TERAPEUTICHE DA EROGARE (per ordine di importanza)</span>');
    $rows[]=array('<b>ESTINAZIONE D\'USO</b><br><span class="description">
                           RISPETTO AL PARCO MACCHINE DELLO STESSO TIPO IN DOTAZIONE</span>');
    $rows[]=array($s['dest-uso']);

//-----------------------------------        
    $rows[]=array('<b>DETTAGLIO PRESTAZIONI</b> <br><span class="description">per ordine di importanza</span>',);
    $rows[]=array($s['prestazioni']);
    $output.=theme('table',$header,$rows);
    unset($rows);
//-----------------------------------        
    $header=array(
        '#type'=>'fieldset',
        '#title'=>'<b>CONFIGURAZIONE DI SISTEMA</b>
        <br><span class="description">INDICARE, QUALORA PERTINENTE E POSSIBILE, 
        GLI ELEMENTI ESSENZIALI INDISPENSABILI A COMPORRE IL 
        SISTEMA/L\'APPARECCHIATURA (componenti hardware e software principali 
        ed, eventualmente, secondari ed accessori)</span>');
    $rows[]=array('<b>COMPONENTI PRINCIPALI</b>');
    $rows[]=array($s['main']);
//-----------------------------------          
    $rows[]=array('<b>COMPONENTI ACCESSORI<b>');
    $rows[]=array($s['accessories']);
//-----------------------------------        
    $rows[]=array('<b>COMPONENTI ACCESSORI GRADITI</b>');
    $rows[]=array($s['desired']);
//-----------------------------------    
    $rows[]=array('<b>Altro:</b>tutto ci&ograve; che non fa parte dell\'apparecchiatura
                         ma che &egrave; funzionale al suo utilizzo o accessorio all\'ergonimia del lavoro');
    $rows[]=array($s['altro']);
//-----------------------------------           
    $rows[]=array('<b>REQUISITI ESSENZIALI</b><br>
        <span class="description">
                        ESPRIMERE VINCOLI NOTI E/O PARERI (SPIEGANDONE BREVEMENTE LE RAGIONI) CIRCA LE <b>CARATTERISTICHE TECNICO-PRESTAZIONALI</b>
                         CHE LA TECNOLOGIA DEVE POSSEDERE PER GARANTIRE L\'EROGAZIONE DELLE PRESTAZIONI INDICATE AL
                         PRECEDENTE PUNTO NELLE DIVERSE POSSIBILI CONDIZIONI OPERATIVE PREVISTE
                         (quali: ingombro e peso, maneggevolezza etc.., dimensione e caratteristiche
                          delle risorse hardware e software componenti l\'intero sistema, ulteriori caratteristiche
                          tecniche di rilievo, integrazioni e compatibilit&agrave; con altri sistemi,
                          altre peculiarit&agrave; funzionali, ….)</span>');
    $rows[]=array($s['essentials']);
//-----------------------------------        
    $rows[]=array('<b>ALTRE CARATTERISTICHE TECNICO-PRESTAZIONALI</b><br>
                    <span class="description">CIO\' CHE SI RITIENE OPPORTUNO CONSIDERARE NELLA FASE DI VALUTAZIONE
                    IN CORSO DI GARA PER UN CORRETTO GIUDIZIO DELLE SOLUZIONI OFFERTE</span>');    
    $rows[]=array($s['tech-altro']);
//-----------------------------------    
    $rows[]=array('<b>CARATTERISTICHE ESCLUSIVE</b><br>
                    <span class="description">
                       EVIDENZIARE E DESCRIVERE GLI EVENTUALI REQUISITI TECNICI IDENTIFICATI
                         COME INDISPENSABILI CHE PRESAGISCANO  L\'ESCLUSIVITA\' DI  UNO SPECIFICO
                         PRODOTTO O UNA FORTE LIMITAZIONE DI SCELTA SUL MERCATO</span>');
    $rows[]=array($s['exclusiv']);
//-----------------------------------
    $rows[]=array('<b>REQUISITI OPZIONALI</b><br>
                    <span class="description">INDICARE E DESCRIVERE LE EVENTUALI FUNZIONI ULTERIORI NON INDISPENSABILI
                        MA UTILI (che potrebbero essere richieste in opzione)</span>');
    $rows[]=array($s['rcq-optional']);
 //-----------------------------------
    $rows[]=array('<b>MERCATO</b><br>
                    <span class="description">INDICARE PRODOTTI SUL MERCATO E RELATIVE PECULIARITA’ DI
                         CUI SI SIA A CONOSCENZA (ALLEGARE DOCUMENTAZIONE RACCOLTA)</span>');
    $rows[]=array($s['market']);
//-----------------------------------
    $rows[]=array('<b>RELAZIONE<br>
                    <span class="description">
                    ESPRIMERE EVENTUALE PARERE PREVENTIVO SULLE APPARECCHIATURE GIA\' NOTE/ESAMINATE (fra quelle sopra elencate)</span>');
    $rows[]=array($s['what_done']);
    $output.=theme('table',$header,$rows);
    unset($rows);
    return $output;
}