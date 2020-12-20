<?php
// Information:
// Developer friend, when I wrote this code,
// only God and I knew what we were doing, now only God knows.

require_once 'lib/nusoap.php'; //library
     $num_factura="";
    $username = "1111111";//user Dian
    $password = "sds55855sdsd5582sdd555";//password Dian

    $prefix="FV";//para factura
    $invoice=$num_factura;


    //webservice url
    $wsdl="https://ws.facturatech.co/21/index.php?wsdl";




function downloadPDFFile($us,$pass,$pre,$fol,$ws){
 $client = new nusoap_client($ws,false);
 $parameters= array('username'=>$us,'password'=>$pass,'prefijo'=>$pre,'folio'=>$fol);
 $result = $client->call("FtechAction.downloadPDFFile",$parameters);

 //print_r($resultado);
  $code=$result['code'];
  $succes=$result['success'];
  $results=$result['resourceData'];
  $error=$result['error'];

  if ($code=='201') {
  	$data = base64_decode($results);
    //file_put_contents('file.pdf',$data);

  	header("Content-type: application/pdf");
  	echo $data;
  }
  else{
  	echo "Exito: ".$succes."<br> Error: ".$error."<br> Debe generar la factura Electr&#243nica para poder visualizarla.";
  }

}


if(isset($_GET['idImp'])){
  $num_factura=$_GET['idFact'];
  downloadPDFFile($username,$password,$prefix,$num_factura,$wsdl);

}


