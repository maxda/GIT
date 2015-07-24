<?php
/* template per la griglia degli item di collaudo
 * campi:
 * $item_rows[][]: griglia dei campi 
 * $headers : intestazioni griglia
 * 
 */
?>


<table id="tested-items">
    <tr>
        <?php foreach ($headers as $hd): ?>
            <th><?php print $hd ?></th>
        <?php endforeach; ?>
    </tr>
    <?php
    $odd = TRUE;
    foreach ($item_rows as $id => $row):
        ?>
        <tr id="item_ID_<?php print $id ?>" class="<?php
        print ($odd) ? 'odd' : 'even';
        $odd = !$odd;
        ?>">
            <?php foreach ($row as $field): ?>
                <td class="edit-field"><?php print $field ?></td>
        <?php endforeach; ?>
        </tr>
<?php endforeach; ?>
</table>
