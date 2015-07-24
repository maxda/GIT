<?php

function statisticsCND() {
    $headers=array(
        array('data'=> 'descrizione',
              'field'=>'c.desc',
              'style'=>'width:20%;'), 
        array('data'=> 'codice',
              'field'=>'c.id'),
        array('data'=>'CIVAB',
              'field'=>'c.CIVAB'),
        array('data'=>'media preventiva',
              'field'=>'avg_val',
              'align'=>'right'), 
        array('data'=>'Dev. standard',
              'field'=>'std_val',
              'style'=>'width:5%;',
              'align'=>'right'),
        array('data'=>'Valore massimo',
              'field'=>'max_val',
              'align'=>'right'),
        array('data'=>'Valore minimo',
              'field'=>'min_val',
              'align'=>'right'), 
        array('data'=>'Nr.',
              'field'=>'count_val',
              'style'=>'width:2%;',
              'align'=>'right'),
        array('data'=>'media acquisto',
              'field'=>'avg_cost',
              'align'=>'right'),
        array('data'=>'Dev. Standard',
              'field'=>'std_cost',
              'align'=>'right')
        );
    
    $qy ="SELECT c.desc as description, id, CIVAB, 
          AVG(value/qta) AS avg_val, 
          ROUND(STD(value/qta)/AVG(value/qta)*100) AS std_val,
          MAX(value/qta) AS max_val,
          MIN(value/qta) AS min_val, 
          COUNT(value) AS count_val,
          AVG(cost/qta) AS avg_cost,
          ROUND(STD(cost/qta)/AVG(cost/qta)*100) AS std_cost
          FROM  requests r 
          INNER JOIN node n ON r.nid=n.nid AND r.vid=n.vid 
          INNER JOIN cnd c ON r.cud=c.id
        WHERE r.value > 2 
        GROUP BY c.id ".tablesort_sql($headers);
    $res=db_query($qy);
    while ($rw= db_fetch_array($res)){
        $row[]=$rw['description'];
        $row[]=$rw['id'];
        $row[]=$rw['CIVAB'];
        $row[]=array('data'=>currencyFormat($rw['avg_val']),'align'=>'right');
        if($rw['std_val']<=30) $color='green';
        else if($rw['std_val']>30 && $rw['std_val']<=70) $color='black';
        else if($rw['std_val']>70 && $rw['std_val']<=100) $color='orange';
        else $color='red';
        $row[]=array('data'=>'&plusmn'.$rw['std_val'].' %','align'=>'right','style'=>'color:'.$color);
        $row[]=array('data'=>currencyFormat($rw['max_val']),'align'=>'right');
        $row[]=array('data'=>currencyFormat($rw['min_val']),'align'=>'right');
        $row[]=array('data'=>$rw['count_val'],'align'=>'right');
        $row[]=array('data'=>currencyFormat($rw['avg_cost']),'align'=>'right');
        if($rw['std_cost']<=30) $color='green';
        else if($rw['std_cost']>30 && $rw['std_cost']<=70) $color='black';
        else if($rw['std_cost']>70 && $rw['std_cost']<=100) $color='orange';
        else $color='red';
        $row[]=array('data'=>'&plusmn'.$rw['std_cost'].'%','align'=>'right','style'=>'color:'.$color);
        
        $rows[]=array('data'=>$row,'style'=>($rw['id']=='Z99'?'text-decoration:line-through;':''));
        unset($row);
    }
    return theme('table',$headers,$rows,NULL,NULL,$colgroup);
    
}