<?php
// Information:
// Developer friend, when I wrote this code,
// only God and I knew what we were doing, now only God knows.

require_once 'lib/nusoap.php'; //library
 $nom_xml=$_GET['namefile'];
 $num_factura=$_GET['numfactura'];

 //select xml file:
	$dir=realpath('./archivosXML');//directorio
    $suffix = null;
    $path = pathinfo('archivosXML/'.$nom_xml.'.xml');
     $filePath = $path['dirname'] . '/' . $path['filename'];
     if (!is_null($suffix)) {
         $filePath .= self::SEPERATOR . $suffix;
     }
     if (empty($path['extension'])) {
         $filePath .= self::XML_EXT;
     } else {
         $filePath .= '.' . $path['extension'];
     }

 //parameters:
    $xml =file_get_contents($filePath);//get xml from  archivosXML/
    $username = "11111111";//user Dian
    $password="54sdsa1s3d45asd57sd";//password Dian
    $xmlBase64 = base64_encode($xml);//base64 encode the xml
    //$prefix="NDE";//para Nota Debito
    $prefix="FV";//para factura
    $invoice=$num_factura;
    //$invoice="1";
    $idtransactions="";

    //webservice url
    $wsdl="https://ws.facturatech.co/21/index.php?wsdl";

function uploadInvoiceFile($ws,$us,$pass,$xml64){
 $client = new nusoap_client($ws,false);
 $parameters= array('username'=>$us,'password'=>$pass,'xmlBase64'=>$xml64);
 $result = $client->call("FtechAction.uploadInvoiceFile",$parameters);
 $res=0;

 if ($result['code']==201){
 $res=documentStatusFile($ws,$us,$pass,$result['transaccionID']);
 }
 else {
 echo "Code: ".$result['code'];
 echo "<br>";
 echo "Succes: ".$result['success'];
 echo "<br>";
 echo "ID: ".$result['transaccionID'];
 echo "<br>";
  echo "Error: ".$result['error'];
 }
 return $res;
}

function documentStatusFile($ws,$us,$pass,$id){
 $client = new nusoap_client($ws,false);
 $parameters= array('username'=>$us,'password'=>$pass,'transaccionID'=>$id);
 $result = $client->call("FtechAction.documentStatusFile",$parameters);
 $res=0;
 if($result['code']==201){
   echo "Exito: ".$result['success'];
   echo "<br>";
   echo "Estado: ".$result['status'];
   $res=1;
 }
 else {
  echo $result['error'];
 }
 return $res;
}

function downloadXMLFile($us,$pass,$pre,$fol,$ws){
 $client = new nusoap_client($ws,false);
 $parameters= array('username'=>$us,'password'=>$pass,'prefijo'=>$pre,'folio'=>$fol);
 $result = $client->call("FtechAction.downloadXMLFile",$parameters);

 //print_r($resultado);
 echo "Code: ".$result['code'];
 echo "<br>";
 echo "Succes: ".$result['success'];
 echo "<br>";
 echo "Error: ".$result['error'];
}

function downloadPDFFile($us,$pass,$pre,$fol,$ws){
 $client = new nusoap_client($ws,false);
 $parameters= array('username'=>$us,'password'=>$pass,'prefijo'=>$pre,'folio'=>$fol);
 $result = $client->call("FtechAction.downloadPDFFile",$parameters);

 //print_r($resultado);
  $code=$result['code'];
  $succes=$result['success'];
  $results=$result['resourceData'];
  $error=$result['error'];

  if ($code==201) {
  	$data = base64_decode($results);
    //file_put_contents('file.pdf',$data);

  	header("Content-type: application/pdf");
  	echo $data;
  }
  else{
  	echo "Exito: ".$succes."<br>"."Error: ".$error;
  }

}

function ejecutar($ws,$us,$pass,$xml64,$pre,$fol){
if(uploadInvoiceFile($ws,$us,$pass,$xml64)){
  downloadPDFFile($us,$pass,$pre,$fol,$ws);
}
}

//ejecutar
ejecutar($wsdl,$username,$password,$xmlBase64,$prefix,$invoice);
//uploadInvoiceFile($wsdl,$username,$password,$xmlBase64);
