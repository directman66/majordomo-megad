<?php

 $record=SQLSelectOne("SELECT * FROM megaddevices WHERE ID='".(int)$id."'");

 
 $stateData=getURL('http://'.$record['IP'].'/'.$record['PASSWORD'].'/?cmd=all', 0);

 $states=explode(';', $stateData);

 $total=count($states);
 for($i=0;$i<$total;$i++) {
  

     $prop=SQLSelectOne("SELECT * FROM megadproperties WHERE DEVICE_ID='".$record['ID']."' AND NUM='".$i."'");

     $type=(int)$prop['TYPE'];

     if (!$prop['ID']) {
      continue;
     }

     //echo $type.' '.$states[$i]."<br/>";
     $old_value=$prop['CURRENT_VALUE'];

     if ($states[$i]) {
      if ($type==0) {
       $tmp=explode('/', $states[$i]);
       if ($tmp[0]=='ON') {
        $prop['CURRENT_VALUE']=1;
       } else {
        $prop['CURRENT_VALUE']=0;
       }
       if ($tmp[1]) {
        $prop['COUNTER']=$tmp[1];
       }
      } elseif ($type==1) {
       if ($states[$i]=='ON') {
        $prop['CURRENT_VALUE']=1;
       } else {
        $prop['CURRENT_VALUE']=0;
       }
      } else {
       $prop['CURRENT_VALUE']=$states[$i];
      }
     }

     $prop['UPDATED']=date('Y-m-d H:i:s');
     SQLUpdate('megadproperties', $prop);


    if ($prop['LINKED_OBJECT'] && $prop['LINKED_PROPERTY']) {
     if ($old_value!=$prop['CURRENT_VALUE'] || $prop['CURRENT_VALUE']!=gg($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'])) {
      setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $prop['CURRENT_VALUE'], array($this->name=>'0'));
     }
    }

 }


//internal temp sensor data
$prop=SQLSelectOne("SELECT * FROM megadproperties WHERE DEVICE_ID='".$record['ID']."' AND TYPE='100'");
if ($prop['ID']) {
 $stateData=getURL('http://'.$record['IP'].'/'.$record['PASSWORD'].'/?tget=1', 0);
 $old_value=$prop['CURRENT_VALUE'];
 if ($stateData!='') {
  $prop['CURRENT_VALUE']=$stateData;
  $prop['UPDATED']=date('Y-m-d H:i:s');
  SQLUpdate('megadproperties', $prop);

    if ($prop['LINKED_OBJECT'] && $prop['LINKED_PROPERTY']) {
     if ($old_value!=$prop['CURRENT_VALUE'] || $prop['CURRENT_VALUE']!=gg($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'])) {
      setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $prop['CURRENT_VALUE'], array($this->name=>'0'));
     }
    }

 }
}

