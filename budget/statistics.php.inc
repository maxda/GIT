<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */




function statistics($filter){
     $qy=db_query(
        "SELECT count(r.rcq_status) as count,
                r.rcq_status as status,
                bg.title as budget,
                st.description as struct,
                p.description as dpt
         FROM "._BONDED_REQUESTS." bl
         INNER JOIN {node} rn ON rn.nid=bl.nid
         INNER JOIN {node} bg ON bg.nid=bl.bnid
         INNER JOIN "._REQUESTS_TABLE." r ON r.nid=rn.nid AND r.vid=rn.vid
         INNER JOIN "._STRUCT_TABLE." st ON st.id=r.struct
         INNER JOIN "._STRUCT_TABLE." p ON st.parent=p.id
         WHERE bg.type='budget' ".($filter<>''?$filter:'')." 
         GROUP BY bg.title, st.description 
         ORDER BY bg.title,p.description,st.description");

     $status=request_status_message();
     $header[]='Struttura';
     foreach($status as $key => $val) $header[]=$val;
     $bg='';
     $str='';
     $dpt='';
     $hd=reset_head();
     while($res=db_fetch_object($qy)){
        if($bg<>$res->budget||$bg==''){
            $output.='<br><br>'.$bg.'<br>';
            $bg=$res->budget;
        }
        if($dpt<>$res->dpt || $dpt==''){
            $output.=theme('table',$header,$rows,array(),$dpt);
            $rows=array();
            $dpt=$res->dpt;
        }if($hd['st']<>$res->struct || $hd['st']==''){
            $row=array();
            foreach($hd as $val)$row[]=$val;
            $rows[]=$row;
            $hd=reset_head();
            $hd['st']=$res->struct;
        }
        $hd[$res->status]=$res->count;
     }
     $row=array();
     foreach($hd as $val)$row[]=$val;
     $rows[]=$row;
     $output.=theme('table',$header,$rows,array(),$dpt);
     return $output;
}


function reset_head(){
    $hd['st']='';
    foreach(request_status_message() as $key => $val) $hd[$key]=0;
    return $hd;
}

function bar_element_tpl($percent,$color='#9FCBEF',$show_percent=FALSE,$label=NULL){
   if ($label!==NULL)$height=50; else $height=100;
   return '<div style="width: '.$percent.'%;height: 100%; overflow: hidden;  position: relative; float: left;" class="progresbar_bar" >
   <div style="height: '.$height.'%; background-color: '.$color.';">'.($show_percent?$percent.'%':'').'</div>
   <div style="text-align:right;height: '.(100-$height).'%; overflow: hidden; position: relative; float: bottom;"  >'.$label.'</div>';
}



/**
 *  built an html progres bar histogram
 * @param <array> $data :
 *          array of values or
 *          array of arrays with that keys:
 *              'data' (mandatory) value to show
 *              'color' html color of bar for that value
 *              'percent' show % value  in progress bar
 *              'label' label to write below bar
 * @param <bool> $one_bar show only one bar for all values or
 *                one bar for each value
 * @return div progress bar elemnts
 */
function progress_bar_tpl($data,$one_bar=TRUE){
//    $sum=0;
//    if ($one_bar){
//        //normalize data if not normalized
//        foreach($data as $element){
//            $sum+=is_array($element)?$element['data']:$element;
//        }
//    }
    foreach($data as $element){
        if (is_array($element)){
            $color=(isset($element['color'])?$element['color']:'#9FCBEF');
            $data=$element['data'];
            $show=(isset($element['percent'])&&$element['percent']);
            $label=(isset($element['label'])?$element['label']:NULL);
        }
        else {
            $color='#9FCBEF';
            $data=$element;
            $show=FALSE;
            $label=NULL;
        }
        $div='<div class="progBar_box" style=" background:none repeat scroll 0 0 #FFFFFF;
				  border: 1px solid;
				  float: left;
				  height: 25px;
				  margin-right: 5px;
				  padding: 1px;
				  width:30%;
				  border-color:#C1CCD3 !important;">';
        if ($one_bar)
            $out.=bar_element_tpl($data,$color,$show,$label);
        else
            $out.=$div.bar_element_tpl($data,$color,$show,$label).'</div>';
    }
    if ($one_bar) $out=$div.$out.'</div>';
    return $out;
}

