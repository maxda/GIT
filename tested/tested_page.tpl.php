<?php
/*
 * Template per la configurazione della scheda dei collaudi
 * la lista dei campi disponibili è contenuta in $variable_list
 * 
 * campi relativi al nodo non tutti editabili

  $title
  $menu
  $nid
  $vid
  $uid
  $created
  $type
  $language
  $changed
  $buttons
  $form_build_id
  $form_token
  $form_id
  $revision_information
  $author
  $options
  $comment_settings
  $attachments

 * * campi relativi ai contenuti
  $sistema_apparecchiatura
  $tipologia_sistema_apparecchiatura
  $reparto_installazione
  $tipo_acquisizione
  $numero_ordine
  $data_ordine
  $n_order
  $referente_amministrativo
  $ditta_produttrice
  $ditta_fornitrice
  $ditta_assistenza_tecnica
  $note_particolari
  $note_acquisto
  $data_consegna
  $data_1a_installazione
  $data_verifiche_EBM
  $data_collaudo
  $contatto_reparto
  $ditta
  $reparto
  $verificatore
  $responsabile_sic
  $note_sostituzione_apparecchiature
  $note_manutenzione
  $items
 */

// print "<!-- \n" . $variable_list . "\n -->";
?>

<table class="tested"><caption>
        <big><br/><span class="description idGIT">ID G.I.T.</span><span class="nid idGIT"> <?php print $nid ?></span></big>&nbsp;&nbsp;&nbsp;
        <?php print $numero_ordine ?></caption>
    <tr><th style="width: 25%"></th><th></th>
    <tr><td><?php print $sistema_apparecchiatura ?></td><td><?php print $tipologia_sistema_apparecchiatura ?></td></tr>
    <tr><td><?php print $tipo_acquisizione ?></td><td><?php print $reparto_installazione ?></td></tr>
    <tr><td style="width: 25%;vertical-align: top;">
            <small>
                <table><caption><b>date</b></caption>
                    <tr><td><?php print $data_ordine ?> <?php print $n_order ?></td></tr>
                    <tr><td><?php print $data_consegna ?></td></tr>
                    <tr><td><?php print $data_1a_installazione ?></td></tr>
                    <tr><td><?php print $data_verifiche_manutentore ?></td></tr>
                    <tr><td><?php print $data_collaudo ?></td></tr>
                    
                </table>
            </small>
        </td>
        <td style="vertical-align: top;">
            <table><caption><b>riferimenti</b></caption>
                <tr><td style="vertical-align: top;"><?php print $ditta_produttrice ?></td></tr>
                <tr><td style="vertical-align: top;"><?php print $ditta_fornitrice ?></td></tr>
                <tr><td style="vertical-align: top;"><?php print $ditta_assistenza_tecnica ?></td></tr>
                
            </table>

        </td></tr></table>
<table><caption>note</caption>
    <tr>
        <td><?php print $note_acquisto ?></td>
        <td><?php print $note_particolari ?></td>
    </tr><tr>
        <td><?php print $note_sostituzione_apparecchiature ?></td>
        <td><?php print $note_manutenzione ?></td>

    </tr>
</table>
<?php print $items ?>
<table>
    <caption>Riferimenti</caption>
    <tr>
        <td><?php print $ditta ?></td>
        <td><?php print $referente_amministrativo ?></td>
        <td><?php print $reparto ?></td></tr>
        <tr><td><b><?php print $responsabile_sic  ?></b></td>
        <td><b><?php print $verificatore  ?></b></td>

    </tr>
</table>
<?php echo $form_close ?>