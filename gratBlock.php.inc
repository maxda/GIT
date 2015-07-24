<?php
/* block modue file
*/


function gratBlock($op = 'list', $delta = 0, $edit = array()){

	switch ($op){
						// What kind of information to retrieve about the block or blocks. Possible values:
						//$delta Which block to return (not applicable if $op is 'list'). Although it is most commonly an integer starting at 0, this is not mandatory. For instance, aggregator.module uses string values for $delta
						//$edit If $op is 'save', the submitted form data from the configuration form.

    	case 'list':   	//A list of all blocks defined by the module.
    		$block[0]['info']='Ricerca ID';
    		$block[1]['info']='Ultimi inseriti';
    		return $block;
    	break;
	    case 'configure': 	//Configuration form for the block.
    	break;
	    case 'save': 		//Save the configuration options.
	    break;
    	case 'view': 		//Process the block when enabled in a region in order to view its contents.
    		return blockForms($delta);
    	break;
    }




/*
Return value

    * If $op is 'list': An array of block descriptions. Each block description is an associative array, with the following key-value pairs:
          o 'info': (required) The human-readable name of the block.
          o 'cache': A bitmask of flags describing how the block should behave with respect to block caching. The following shortcut bitmasks are provided as constants in block.module:
                + BLOCK_CACHE_PER_ROLE (default): The block can change depending on the roles the user viewing the page belongs to.
                + BLOCK_CACHE_PER_USER: The block can change depending on the user viewing the page. This setting can be resource-consuming for sites with large number of users, and should only be used when BLOCK_CACHE_PER_ROLE is not sufficient.
                + BLOCK_CACHE_PER_PAGE: The block can change depending on the page being viewed.
                + BLOCK_CACHE_GLOBAL: The block is the same for every user on every page where it is visible.
                + BLOCK_NO_CACHE: The block should not get cached.
          o 'weight', 'status', 'region', 'visibility', 'pages': You can give your blocks an explicit weight, enable them, limit them to given pages, etc. These settings will be registered when the block is first loaded at admin/block, and from there can be changed manually via block administration. Note that if you set a region that isn't available in a given theme, the block will be registered instead to that theme's default region (the first item in the _regions array).
          o If $op is 'configure': optionally return the configuration form.
          o If $op is 'save': return nothing.
          o If $op is 'view': return an array which must define a 'subject' element and a 'content' element defining the block indexed by $delta.
*/

}

function blockForms($block){
	switch ($block) {
		case 0:
			return array('subject'=>'Cerca riferimento:',
						 'content'=> drupal_get_form('searchGratID'),
					);
		break;
		case 1:
			$result = db_query("SELECT nid, title,changed FROM {node} ORDER BY changed DESC LIMIT 6");

			  $output = '<div class="container-small">';
			  $output .= '<ul id="list">';
			  while ($node = db_fetch_object($result)) {
			  $output .=  '<li>'.format_date($node->changed,'custom','d-m-Y').' '.nl($node->nid).'</li>';
				}
			  $output .= '</ul>'.l('altro...','node/');
			  $output .= '</div>';
  			return array('subject'=>'Ultimi inseriti:','content'=>$output);
		break;
	}
}

//form di ricerca tramite ID
function searchGratID(&$form_state){
	$form['ID']=array(
		'#type'=>'textfield',
		'#default_value'=>'',
		'#size'=>20,
		'#description'=>'Inserisci il numero di riferimento oppure un testo per una ricerca automatica',
		'#autocomplete_path'=>'id/autocomplete',
//		'#attributes'=>array('title'=>'Inserire il testo da ricercare o l\'identificativo del documento da ricercare'),
	);
	$form['search']=array(
		'#type'=>'submit',
		'#value'=>'Cerca',
		'#attributes'=>array('title'=>'Apre il documento ricercato (se trovato)'),
		);
//	$form['#validate']=array('searchGratID_validate');
	$form['#submit']=array('searchGratID_submit');
	return $form;
}

function searchGratID_submit($form, &$form_state){
  if(is_numeric($form_state['values']['ID'])) $form_state['redirect'] = 'node/'. trim($form_state['values']['ID']);
}


