<?php
	//@Author: Marco Marin 21/07/2020
    // Information:
   // Developer friend, when I wrote this code,
   // only God and I knew what we were doing, now only God knows.

   //Generate xml file for electronic invoice  ej: 8903236678 38
include_once("../models/Conexion.php");
 //input data: ID number, invoice number, xml name
 function generarXml($id_cliente,$id_factura,$nom_xml) {
 	//DB connection
 	$conex = Conexion::getInstancia();
 	//Query sql tax bases (%)
    $sql2="SELECT SISTENOMBR,SISTEVALOR from SISTEMA";
      //Sql2 query execution
    $stid2=oci_parse($conex, $sql2);
    oci_execute($stid2);
    $result=array();
    while (($row = oci_fetch_array($stid2, OCI_BOTH)) != false) {
	      $result[]=$row;
	}
 	    //Query sql data xml
	$sql = "SELECT CLIENTE.CLIENCODIG,CLIENTE.CLIENTIPID,CONCAT('FV',FACTURA.FACTUCODIG) AS NUM_FACTURA,TO_CHAR(FACTURA.factufecge,'YYYY-MM-DD') AS Fecha,TO_CHAR(FACTURA.factufecge,'HH:MM:SS') AS Hora,ValiTipClient (CLIENTE.CLIENTIPID) as clientipo,
	        CLIENTE.CLIENNOMBR,CLIENTE.CLIENDIREC,CLIENTE.CLIENDEPAR,CLIENTE.CLIENLOCAL,(SELECT DEPARTAMEN.deparnombr FROM DEPARTAMEN WHERE DEPARTAMEN.deparcodig=CLIENTE.cliendepar) AS DEPARTAMENTO,
	        FACTURA.factuvalne,FACTURA.factuvalor,ValidaIva(CLIENTE.cliencodig) AS IVA,FACTURA.factuvaliv ,FACTURA.factuvalfu,FACTURA.factuvalic,FACTURA.factuvalri, ValiMatMer (CLIENTE.CLIENTIPO,CLIENTE.cliencodig) AS MatMerc, ValiIdClient(CLIENTE.CLIENTIPID,CLIENTE.cliencodig) AS IdClientJu,
	        ValiIdClientTwo(ValiTipClient(CLIENTE.CLIENTIPID),CLIENTE.cliencodig) AS IdClientNa, ValIdIva(CLIENTE.cliencodig) AS Id_iva, ValiNomRut(CLIENTE.CLIENTIPO,CLIENTE.cliencodig) AS Nom_Rut,
	        FACTUDETAL.FACDECANTI AS Cantidad, FACTUDETAL.facdevalto, (SELECT INVENTARIO.invennombr FROM INVENTARIO WHERE INVENTARIO.invencodig=FACTUDETAL.facdeartic AND rownum = 1)  AS Desc_producto, FACTUDETAL.facdevalun AS Val_unitario, FACTUDETAL.facdeartic,
            ValidaPagos(FACTURA.FACTUCODIG) as MetPago,ValidafecPagos(FACTURA.factucodig) as FecMetPago, ValidaImpuestos(".($result[3][1]/100).",FACTUDETAL.facdevalto,FACTURA.factuvalfu) as RTEF , ValidaImpuestos(".($result[2][1]/100).",FACTUDETAL.facdevalto,FACTURA.factuvalic) as RTICA,
            ValidaImpuestos(".($result[1][1]/100).",FACTUDETAL.facdevalto,FACTURA.factuvalri) as RTIVA ,ValidaImpuestos(".($result[0][1]/100).",FACTUDETAL.facdevalto,FACTURA.factuvaliv) as IVARES, CLIENEMAIL
	        FROM CLIENTE,FACTURA,FACTUDETAL WHERE  FACTURA.factuclien=CLIENTE.cliencodig AND FACTUDETAL.facdecodig=FACTURA.factucodig  AND CLIENTE.cliencodig=".$id_cliente." AND FACTURA.FACTUCODIG=".$id_factura;

    //sql1 query execution
	$stid = oci_parse($conex, $sql);
	oci_execute($stid);
	$foo = array();
	while (($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
	      $foo[]=$row;
	}

	//print_r($foo);
	//create an object of the XMLWriter class.
	$objetoXML = new XMLWriter();

    //Select the path, name and extension that will be given to the XML file
    $dir=realpath('./archivosXML');
    $suffix = null;
    $path = pathinfo($dir.'/'.$nom_xml.'.xml');
     $filePath = $path['dirname'] . '/' . $path['filename'];
     if (!is_null($suffix)) {
         $filePath .= self::SEPERATOR . $suffix;
     }
     if (empty($path['extension'])) {
         $filePath .= self::XML_EXT;
     } else {
         $filePath .= '.' . $path['extension'];
     }
    //end path

	// Estructura básica del XML
	$objetoXML->openURI($filePath);
	$objetoXML->setIndent(true);
	$objetoXML->setIndentString("\t");
	$objetoXML->startDocument('1.0', 'utf-8');
	// Inicio del nodo raíz
	$objetoXML->startElement("FACTURA");
		  $objetoXML->startElement("ENC"); //elemento Encabezado

			    $objetoXML->startElement("ENC_1"); //elemento tipo de Documento comercial
		        $objetoXML->text('INVOIC');
		        $objetoXML->endElement(); //tipo de Documento comercial

		        $objetoXML->startElement("ENC_2"); //elemento NIT
		        $objetoXML->text('901254851');
		        $objetoXML->endElement(); //fin NIT

		        $objetoXML->startElement("ENC_3"); //elemento numero Documento cliente
		        $objetoXML->text((string)$foo[0][0]);
		        $objetoXML->endElement(); //fin numero Documento cliente

		        $objetoXML->startElement("ENC_4"); //elemento
		        $objetoXML->text('UBL 2.1');
		        $objetoXML->endElement(); //fin

		        $objetoXML->startElement("ENC_5"); //elemento
		        $objetoXML->text('DIAN 2.1');
		        $objetoXML->endElement(); //fin

		        $objetoXML->startElement("ENC_6"); //elemento Numero de factura
		        $objetoXML->text((string)$foo[0][2]);
		        $objetoXML->endElement(); //fin Numero de factura

		        $objetoXML->startElement("ENC_7"); //elemento fecha emision
		        $objetoXML->text((string)$foo[0][3]);
		        $objetoXML->endElement(); //fin fecha emision

		        $objetoXML->startElement("ENC_8"); //elemento hora emision
		        $objetoXML->text((string)$foo[0][4]."-05:00");
		        $objetoXML->endElement(); //fin hora emision

		        $objetoXML->startElement("ENC_9"); //elemento
		        $objetoXML->text('01');
		        $objetoXML->endElement(); //fin

		        $objetoXML->startElement("ENC_10"); //elemento
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin

		        $objetoXML->startElement("ENC_11"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("ENC_12"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("ENC_13"); //elemento centro de costos
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin centro de costos

		        $objetoXML->startElement("ENC_14"); //elemento codigo contable
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin codigo contable

		        $objetoXML->startElement("ENC_15"); //elemento codigo contable
		        $objetoXML->text(count($foo));
		        $objetoXML->endElement(); //fin codigo contable

		        $objetoXML->startElement("ENC_16"); //elemento fecha vencimiento
		        $objetoXML->text((string)$foo[0][3]);
		        $objetoXML->endElement(); //fin fecha vencimiento

		        $objetoXML->startElement("ENC_17"); //elemento URL archivos anexos
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin URL archivos anexos

		        $objetoXML->startElement("ENC_18"); //elemento URL para pago
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin URL para pago

		        $objetoXML->startElement("ENC_19"); //elemento unidad de negocio
		        $objetoXML->text('1');
		        $objetoXML->endElement(); //fin unidad de negocio

		        $objetoXML->startElement("ENC_20"); //elemento Ambiente (prueba=2)
		        $objetoXML->text('2');
		        $objetoXML->endElement(); //fin Ambiente

		        $objetoXML->startElement("ENC_21"); //elemento tipo operacion
		        $objetoXML->text('10');
		        $objetoXML->endElement(); //fin tipo operacion

		        $objetoXML->startElement("ENC_22"); //elemento fecha pago de impuestos
		        $objetoXML->text('2020-09-21');
		        $objetoXML->endElement(); //fin fecha pago de impuestos

	      $objetoXML->endElement(); //fin Encabezado

	      $objetoXML->startElement("EMI"); //elemento Emisor

	            $objetoXML->startElement("EMI_1"); //elemento tipo identificacion
		        $objetoXML->text('1');
		        $objetoXML->endElement(); //fin tipo identificacion

		        $objetoXML->startElement("EMI_2"); //elemento identificacion del emisor
		        $objetoXML->text('901254851');
		        $objetoXML->endElement(); //fin identificacion del emisor

		        $objetoXML->startElement("EMI_3"); //elemento identificacion fiscal
		        $objetoXML->text('31');
		        $objetoXML->endElement(); //fin identificacion fiscal

		        $objetoXML->startElement("EMI_4"); //elemento regimen
		        $objetoXML->text('48');
		        $objetoXML->endElement(); //fin regimen

		        // $objetoXML->startElement("EMI_5"); //elemento Num identificacion interna
		        // $objetoXML->text('');
		        // $objetoXML->endElement(); //fin Num identificacion interna

		        $objetoXML->startElement("EMI_6"); //elemento razon social
		        $objetoXML->text('TIENDA DEPORTIVA CHIGUIRO S.A.S');
		        $objetoXML->endElement(); //fin razon social

		        $objetoXML->startElement("EMI_7"); //elemento Nombre comercial
		        $objetoXML->text('TIENDA DEPORTIVA CHIGUIRO');
		        $objetoXML->endElement(); //fin Nombre comercial

		        $objetoXML->startElement("EMI_8"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_9"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_10"); //elemento direccion
		        $objetoXML->text('CL 14 8 54 LC 225 P 2 CC ZAMORACO');
		        $objetoXML->endElement(); //fin Direccion

		        $objetoXML->startElement("EMI_11"); //elemento codigo del departamento
		        $objetoXML->text('76');
		        $objetoXML->endElement(); //fin codigo del departamento

		        $objetoXML->startElement("EMI_12"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_13"); //elemento ciudad
		        $objetoXML->text('CALI');
		        $objetoXML->endElement(); //fin ciudad

		        $objetoXML->startElement("EMI_14"); //elemento codigo postal
		        $objetoXML->text('76001');
		        $objetoXML->endElement(); //fin codigo postal

		        $objetoXML->startElement("EMI_15"); //elemento codigo pais
		        $objetoXML->text('CO');
		        $objetoXML->endElement(); //fin codigo pais

		        $objetoXML->startElement("EMI_16"); //elemento codigo localizacion EAN
		        $objetoXML->text('CODIGO EAN');
		        $objetoXML->endElement(); //fin codigo localizacion EAN

		        $objetoXML->startElement("EMI_17"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_18"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_19"); //elemento nombre departamento
		        $objetoXML->text('VALLE DEL CAUCA');
		        $objetoXML->endElement(); //fin nombre departamento

		        $objetoXML->startElement("EMI_20"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("EMI_21"); //elemento nombre pais
		        $objetoXML->text('Colombia');
		        $objetoXML->endElement(); //fin nombre pais

		        $objetoXML->startElement("EMI_22"); //elemento digito de identificacion
		        $objetoXML->text('1');
		        $objetoXML->endElement(); //fin digito de identificacion

		        $objetoXML->startElement("EMI_23"); //elemento codigo municipio
		        $objetoXML->text('76001');
		        $objetoXML->endElement(); //fin codigo municipio

		        $objetoXML->startElement("EMI_24"); //elemento nombre registrado en RUT
		        $objetoXML->text('TIENDA DEPORTIVA CHIGUIRO');
		        $objetoXML->endElement(); //fin nombre registrado en RUT

		        $objetoXML->startElement("EMI_25"); //elemento codigo actividad economica
		        $objetoXML->text('4771;4772;4774');
		        $objetoXML->endElement(); //fin codigo actividad economica

		      $objetoXML->startElement("TAC"); //elemento Pais
	                $objetoXML->startElement("TAC_1"); //elemento codigo actividad economica
			        $objetoXML->text('O-48');
			        $objetoXML->endElement(); //fin codigo actividad economica
		      $objetoXML->endElement(); //fin Pais

		      $objetoXML->startElement("DFE"); //elemento Direccion fiscal
		            $objetoXML->startElement("DFE_1"); //elemento codigo municipio
			        $objetoXML->text('76001');
			        $objetoXML->endElement(); //fin codigo municipio

			        $objetoXML->startElement("DFE_2"); //elemento codigo departamento
			        $objetoXML->text('76');
			        $objetoXML->endElement(); //fin codigo departamento

			        $objetoXML->startElement("DFE_3"); //elemento codigo pais
			        $objetoXML->text('CO');
			        $objetoXML->endElement(); //fin codigo pais

			        $objetoXML->startElement("DFE_4"); //elemento codigo postal
			        $objetoXML->text('76001');
			        $objetoXML->endElement(); //fin codigo postal

			        $objetoXML->startElement("DFE_5"); //elemento nombre pais
			        $objetoXML->text('COLOMBIA');
			        $objetoXML->endElement(); //fin nombre pais

			        $objetoXML->startElement("DFE_6"); //elemento nombre departamento
			        $objetoXML->text('VALLE DEL CAUCA');
			        $objetoXML->endElement(); //fin nombre departamento

			        $objetoXML->startElement("DFE_7"); //elemento nombre ciudad
			        $objetoXML->text('CALI');
			        $objetoXML->endElement(); //fin nombre ciudad

			        $objetoXML->startElement("DFE_8"); //elemento direccion
			        $objetoXML->text('CL 14 8 54 LC 225 P 2 CC ZAMORACO');
			        $objetoXML->endElement(); //fin direccion

		      $objetoXML->endElement(); //fin Direccion fiscal

		      $objetoXML->startElement("ICC"); //elemento info camara de comercio

		            $objetoXML->startElement("ICC_1"); //elemento numero matricula mercantil
			        $objetoXML->text('1041140-16');
			        $objetoXML->endElement(); //fin numero matricula mercantil

			        // $objetoXML->startElement("ICC_2"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_3"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_4"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_5"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_6"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_7"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        // $objetoXML->startElement("ICC_8"); //elemento Nulo
			        // $objetoXML->text('');
			        // $objetoXML->endElement(); //fin Nulo

			        $objetoXML->startElement("ICC_9"); //elemento prefijo factura
			        $objetoXML->text('FV');
			        $objetoXML->endElement(); //fin prefijo factura

		      $objetoXML->endElement(); //fin info camara de comercio

		      $objetoXML->startElement("CDE"); //elemento info contacto emisor

		            $objetoXML->startElement("CDE_1"); //elemento tipo contacto
			        $objetoXML->text('1');
			        $objetoXML->endElement(); //fin tipo contacto

			        $objetoXML->startElement("CDE_2"); //elemento nombre y cargo contacto
			        $objetoXML->text('ANDREA SERRATO REPRESENTANTE LEGAL');
			        $objetoXML->endElement(); //fin nombre y cargo contacto

			        $objetoXML->startElement("CDE_3"); //elemento telefono contacto
			        $objetoXML->text('8880002 - 3135542582');
			        $objetoXML->endElement(); //fin telefono contacto

			        $objetoXML->startElement("CDE_4"); //elemento correo contacto
			        $objetoXML->text('andrea.serrato@hotmail.com');
			        $objetoXML->endElement(); //fin correo contacto

			        $objetoXML->startElement("CDE_5"); //elemento Nulo
			        $objetoXML->text('');
			        $objetoXML->endElement(); //fin Nulo

			        $objetoXML->startElement("CDE_6"); //elemento Nulo
			        $objetoXML->text('');
			        $objetoXML->endElement(); //fin Nulo

		      $objetoXML->endElement(); //fin info contacto emisor

		      $objetoXML->startElement("GTE"); //elemento impuestos

		            $objetoXML->startElement("GTE_1"); //elemento identificador de tributo
			        $objetoXML->text('01');
			        $objetoXML->endElement(); //fin identificador de tributo

			        $objetoXML->startElement("GTE_2"); //elemento nombre tributo
			        $objetoXML->text('IVA');
			        $objetoXML->endElement(); //fin nombre tributo

		      $objetoXML->endElement(); //fin impuestos

		  $objetoXML->endElement(); //fin Emisor(EMI)

	      $objetoXML->startElement("ADQ"); //elemento informacion del cliente

	            $objetoXML->startElement("ADQ_1"); //elemento tipo persona
		        $objetoXML->text((string)$foo[0][5]);
		        $objetoXML->endElement(); //fin tipo persona

		        $objetoXML->startElement("ADQ_2"); //elemento numero documento
		        $objetoXML->text((string)$foo[0][0]);
		        $objetoXML->endElement(); //fin numero documento

		        $objetoXML->startElement("ADQ_3"); //elemento tipo documento
		        $objetoXML->text((string)$foo[0][1]);
		        $objetoXML->endElement(); //fin tipo documento

		        $objetoXML->startElement("ADQ_4"); //elemento regimen
		        $objetoXML->text((string)$foo[0][13]);
		        $objetoXML->endElement(); //fin regimen

		        $objetoXML->startElement("ADQ_5"); //elemento numero de identificacion
		        $objetoXML->text((string)$foo[0][0]);
		        $objetoXML->endElement(); //fin numero de identificacion

		        $objetoXML->startElement("ADQ_6"); //elemento razon social
		        $objetoXML->text((string)$foo[0][6]);
		        $objetoXML->endElement(); //fin razon social

		        $objetoXML->startElement("ADQ_7"); //elemento nombre comercial
		        $objetoXML->text((string)$foo[0][6]);
		        $objetoXML->endElement(); //fin nombre comercial

		        $objetoXML->startElement("ADQ_8"); //elemento nombres
		        $objetoXML->text((string)$foo[0][6]);
		        $objetoXML->endElement(); //fin nombres

		        $objetoXML->startElement("ADQ_9"); //elemento apellidos
		        $objetoXML->text((string)$foo[0][6]);
		        $objetoXML->endElement(); //fin apellidos

		        $objetoXML->startElement("ADQ_10"); //elemento direccion cliente
		        $objetoXML->text((string)$foo[0][7]);
		        $objetoXML->endElement(); //fin direccion cliente

		        $objetoXML->startElement("ADQ_11"); //elemento codigo departamento cliente
		        $objetoXML->text((string)$foo[0][8]);
		        $objetoXML->endElement(); //fin codigo departamento cliente

		        $objetoXML->startElement("ADQ_12"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); //fin Nulo

		        $objetoXML->startElement("ADQ_13"); //elemento nombre ciudad cliente
		        $objetoXML->text((string)$foo[0][9]);
		        $objetoXML->endElement(); //fin ciudad cliente

		        $objetoXML->startElement("ADQ_14"); //elemento codigo postal cliente
		        $objetoXML->text((string)$foo[0][9]);
		        $objetoXML->endElement(); //fin codigo postal cliente

		        $objetoXML->startElement("ADQ_15"); //elemento codigo id pais
		        $objetoXML->text('CO');
		        $objetoXML->endElement(); //fin codigo id pais

		        $objetoXML->startElement("ADQ_16"); //elemento codigo localizacion EAN
		        $objetoXML->text('');//parametro NULO
		        $objetoXML->endElement(); //nombre codigo localizacion EAN

		        $objetoXML->startElement("ADQ_17"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); // fin Nulo

		        $objetoXML->startElement("ADQ_18"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); // fin Nulo

                $objetoXML->startElement("ADQ_19"); //elemento nombre departamento
		        $objetoXML->text((string)$foo[0][10]);
		        $objetoXML->endElement(); // fin nombre departamento

		        $objetoXML->startElement("ADQ_20"); //elemento Nulo
		        $objetoXML->text('');
		        $objetoXML->endElement(); // fin Nulo

		        $objetoXML->startElement("ADQ_21"); //elemento nombre pais
		        $objetoXML->text('COLOMBIA');
		        $objetoXML->endElement(); // fin nombre pais

                if(is_numeric($foo[0][19])) {
		        $objetoXML->startElement("ADQ_22"); //elemento digito de verificacion
		        $objetoXML->text((string)$foo[0][19]);
		        $objetoXML->endElement(); // fin digito de verificacion
                }
		        $objetoXML->startElement("ADQ_23"); //elemento codigo municipio
		        $objetoXML->text((string)$foo[0][9]);
		        $objetoXML->endElement(); // fin codigo municipio


		        $objetoXML->startElement("ADQ_24"); //elemento identificacion adquiriente
		        $objetoXML->text((string)$foo[0][20]);
		        $objetoXML->endElement(); // fin identificacion adquiriente

		        // $objetoXML->startElement("ADQ_25"); //elemento identificador tipo documento
		        // $objetoXML->text((string)$foo[0][1]);
		        // $objetoXML->endElement(); // fin identificador tipo documento

          //       if(!@(string)$foo[0][15]==""){
		        // $objetoXML->startElement("ADQ_26"); //elemento DV del NIT
		        // $objetoXML->text((string)$foo[0][15]);
		        // $objetoXML->endElement(); // fin DV del NIT
                //}

		      $objetoXML->startElement("TCR"); //elemento informacion tributaria

		            $objetoXML->startElement("TCR_1"); //elemento
			        $objetoXML->text('O-99');
			        $objetoXML->endElement(); //fin

		      $objetoXML->endElement(); //fin informacion tributaria

		      $objetoXML->startElement("ILA"); //elemento informacion legal del cliente

	                if(!@(string)$foo[0][22]==""){
		            $objetoXML->startElement("ILA_1"); //elemento Nombre resgistrado RUT cliente
			        $objetoXML->text((string)$foo[0][22]);
			        $objetoXML->endElement(); //fin Nombre resgistrado RUT cliente
	                }
			        $objetoXML->startElement("ILA_2"); //elemento identificacion cliente
			        $objetoXML->text((string)$foo[0][0]);
			        $objetoXML->endElement(); //fin identificacion cliente

			        $objetoXML->startElement("ILA_3"); //elemento tipo documento fiscal
			        $objetoXML->text((string)$foo[0][1]);
			        $objetoXML->endElement(); //fin tipo documento fiscal

	                if(!@(string)$foo[0][19]==""){
			        $objetoXML->startElement("ILA_4"); //elemento codigo verificacion
			        $objetoXML->text((string)$foo[0][19]);
			        $objetoXML->endElement(); //fin codigo verificacion
	                }
		      $objetoXML->endElement(); //fin informacion legal del cliente

              $objetoXML->startElement("DFA"); //elemento Direccion fiscal del adquiriente

                    $objetoXML->startElement("DFA_1"); //elemento codigo identificador del pais
			        $objetoXML->text('CO');
			        $objetoXML->endElement(); //fin codigo identificador del pais

			        $objetoXML->startElement("DFA_2"); //elemento codigo del departamento adquiriente
			        $objetoXML->text((string)$foo[0][8]);
			        $objetoXML->endElement(); //fin codigo del departamento adquiriente

			        $objetoXML->startElement("DFA_3"); //elemento codigo postal adquiriente
			        $objetoXML->text((string)$foo[0][9]);
			        $objetoXML->endElement(); //fin codigo postal adquiriente

			        $objetoXML->startElement("DFA_4"); //elemento codigo del municipio adquiriente
			        $objetoXML->text((string)$foo[0][9]);
			        $objetoXML->endElement(); //fin codigo del municipio adquiriente

			        $objetoXML->startElement("DFA_5"); //elemento Nombre del pais adquiriente
			        $objetoXML->text('COLOMBIA');
			        $objetoXML->endElement(); //fin Nombre del pais adquiriente

			        $objetoXML->startElement("DFA_6"); //elemento Nombre del departamento adquiriente
			        $objetoXML->text((string)$foo[0][10]);
			        $objetoXML->endElement(); //fin Nombre del departamento adquiriente

			        $objetoXML->startElement("DFA_7"); //elemento Nombre de la ciudad adquiriente
			        $objetoXML->text((string)$foo[0][9]);
			        $objetoXML->endElement(); //fin Nombre de la ciudad adquiriente

			        $objetoXML->startElement("DFA_8"); //elemento direccion adquiriente
			        $objetoXML->text((string)$foo[0][9]);
			        $objetoXML->endElement(); //fin direccion adquiriente

              $objetoXML->endElement(); //fin Direccion fiscal del adquiriente

		      $objetoXML->startElement("ICR"); //elemento informacion camara de comercio cliente

	                if(!@(string)$foo[0][18]==""){
		            $objetoXML->startElement("ICR_1"); //elemento numero matricula mercantil
			        $objetoXML->text((string)$foo[0][18]);
			        $objetoXML->endElement(); //fin numero matricula mercantil
	                }
		      $objetoXML->endElement(); //fin informacion camara de comercio cliente

		      $objetoXML->startElement("CDA"); //elemento informacion adquiriente

                    $objetoXML->startElement("CDA_1"); //elemento tipo contacto
			        $objetoXML->text('1');
			        $objetoXML->endElement(); //fin tipo contacto

			        $objetoXML->startElement("CDA_2"); //elemento nombre y cargo contacto
			        $objetoXML->text('N/A');
			        $objetoXML->endElement(); //fin nombre y cargo contacto

			        $objetoXML->startElement("CDA_3"); //elemento telefono contacto
			        $objetoXML->text('N/A');
			        $objetoXML->endElement(); //fin telefono contacto

                    if(!@(string)$foo[0][34]==""){
			        $objetoXML->startElement("CDA_4"); //elemento correo contacto
			        $objetoXML->text((string)$foo[0][34]);
			        $objetoXML->endElement(); //fin correo contacto
			       }
			       else{
			       	$objetoXML->startElement("CDA_4"); //elemento correo contacto
			        $objetoXML->text("example@example.com");
			        $objetoXML->endElement(); //fin correo contacto
			       }

		      $objetoXML->endElement(); //fin informacion adquiriente

		      $objetoXML->startElement("GTA"); //elemento detalles tributarios cliente

	                if(!@(string)$foo[0][21]==""){
		            $objetoXML->startElement("GTA_1"); //elemento identificador del tributo
			        $objetoXML->text((string)$foo[0][21]);
			        $objetoXML->endElement(); //fin dentificador del tributo
	                }
			        $objetoXML->startElement("GTA_2"); //elemento nombre del tributo
			        $objetoXML->text('IVA');
			        $objetoXML->endElement(); //fin nombre del tributo

		      $objetoXML->endElement(); //fin detalles tributarios cliente

		  $objetoXML->endElement(); //fin informacion del cliente(ADQ)

	      $objetoXML->startElement("TOT"); //elemento Valor de factura

	            $objetoXML->startElement("TOT_1"); //elemento Total bruto
		        $objetoXML->text((string)$foo[0][12]);
		        $objetoXML->endElement(); //fin total bruto

		        $objetoXML->startElement("TOT_2"); //elemento Moneda
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin Moneda

                //validar si se aplican retenciones
                if(!@$foo[0][15]=="0" && $foo[0][14]==""){
                	//base imponible: cantidad sobre la que se calculan los impuestos.
			        $objetoXML->startElement("TOT_3"); //elemento valor base imponible
			        $objetoXML->text('0');
			        $objetoXML->endElement(); //fin valor base imponible
		        } else {
			        $objetoXML->startElement("TOT_3"); //elemento valor base imponible
			        $objetoXML->text((string)$foo[0][12]);
			        $objetoXML->endElement(); //fin valor base imponible
		        }

		        $objetoXML->startElement("TOT_4"); //elemento moneda base imponible
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda base imponible

		        $objetoXML->startElement("TOT_5"); //elemento valor a pagar de factura
		        $objetoXML->text((string)$foo[0][11]);
		        $objetoXML->endElement(); //fin valor a pagar de factura

		        $objetoXML->startElement("TOT_6"); //elemento moneda del total de la factura
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda del total de la factura

		        $objetoXML->startElement("TOT_7"); //elemento total valor bruto + tributos
		        $objetoXML->text((string)$foo[0][11]);
		        $objetoXML->endElement(); //fin total valor bruto + tributos

		        $objetoXML->startElement("TOT_8"); //elemento moneda valor bruto + tributos
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda valor bruto + tributos

		        $objetoXML->startElement("TOT_9"); //elemento descuento total
		        $objetoXML->text('0');
		        $objetoXML->endElement(); //fin descuento total

		        $objetoXML->startElement("TOT_10"); //elemento moneda descuentos
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda descuentos

		        $objetoXML->startElement("TOT_11"); //elemento cargo total
		        $objetoXML->text('0');
		        $objetoXML->endElement(); //fin cargo total

		        $objetoXML->startElement("TOT_12"); //elemento moneda cargo total
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda cargo total

		        $objetoXML->startElement("TOT_13"); //elemento Anticipo total
		        $objetoXML->text('0');
		        $objetoXML->endElement(); //fin anticipo total

		        $objetoXML->startElement("TOT_14"); //elemento moneda Anticipo total
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda moneda Anticipo total

		        $objetoXML->startElement("TOT_15"); //elemento redondeo valor
		        $objetoXML->text('0');
		        $objetoXML->endElement(); //fin redondeo valor

		        $objetoXML->startElement("TOT_16"); //elemento moneda redondeo valor
		        $objetoXML->text('COP');
		        $objetoXML->endElement(); //fin moneda moneda redondeo valor

		   $objetoXML->endElement(); //fin valor de factura (tot)

                //validar si existe iva
                if(!@$foo[0][14]=="0"){
		        $objetoXML->startElement("TIM"); //elemento Total impuestos

			        $objetoXML->startElement("TIM_1"); //elemento impuesto retenido o retencion
			        $objetoXML->text('false');
			        $objetoXML->endElement(); //fin impuesto retenido o retencion

			        $objetoXML->startElement("TIM_2"); //elemento Valor del tributo Suma todos los IMP_4
			        $objetoXML->text((string)$foo[0][14]);
			        $objetoXML->endElement(); //fin valor del tributo

			        $objetoXML->startElement("TIM_3"); //elemento moneda valor tributo
			        $objetoXML->text('COP');
			        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->startElement("IMP"); //elemento Tipo impuesto

				        $objetoXML->startElement("IMP_1"); //elemento identificador del tributo
				        $objetoXML->text('01');//01=IVA
				        $objetoXML->endElement(); //fin identificador del tributo

				        $objetoXML->startElement("IMP_2"); //elemento Base impunible
				        $objetoXML->text((string)$foo[0][12]);
				        $objetoXML->endElement(); //fin moneda Base impunible

				        $objetoXML->startElement("IMP_3"); //elemento moneda de la base
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda de la base

				        $objetoXML->startElement("IMP_4"); //elemento valor del tributo
				        $objetoXML->text((string)$foo[0][14]);
				        $objetoXML->endElement(); //fin  valor tributo

				        $objetoXML->startElement("IMP_5"); //elemento moneda valor tributo
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda valor tributo

				        $objetoXML->startElement("IMP_6"); //elemento moneda valor tributo
				        $objetoXML->text((string)$result[0][1]);//19% tarifa iva
				        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->endElement(); //fin Tipo impuesto

		        $objetoXML->endElement(); //fin total impuestos
		        }// fin if validar IVA

		        //validar si existe retencion en la fuente(RTEF)
                if(!@$foo[0][15]=="0"){
		        $objetoXML->startElement("TIM"); //elemento Total impuestos

			        $objetoXML->startElement("TIM_1"); //elemento impuesto retenido o retencion
			        $objetoXML->text('false');
			        $objetoXML->endElement(); //fin impuesto retenido o retencion

			        $objetoXML->startElement("TIM_2"); //elemento Valor del tributo Suma todos los IMP_4
			        $objetoXML->text((string)$foo[0][15]);
			        $objetoXML->endElement(); //fin valor del tributo

			        $objetoXML->startElement("TIM_3"); //elemento moneda valor tributo
			        $objetoXML->text('COP');
			        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->startElement("IMP"); //elemento Tipo impuesto

				        $objetoXML->startElement("IMP_1"); //elemento identificador del tributo
				        $objetoXML->text('06');//06=retefuente
				        $objetoXML->endElement(); //fin identificador del tributo

				        $objetoXML->startElement("IMP_2"); //elemento Base impunible
				        $objetoXML->text((string)$foo[0][12]);
				        $objetoXML->endElement(); //fin moneda Base impunible

				        $objetoXML->startElement("IMP_3"); //elemento moneda de la base
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda de la base

				        $objetoXML->startElement("IMP_4"); //elemento valor del tributo
				        $objetoXML->text((string)$foo[0][15]);
				        $objetoXML->endElement(); //fin  valor tributo

				        $objetoXML->startElement("IMP_5"); //elemento moneda valor tributo
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda valor tributo

				        $objetoXML->startElement("IMP_6"); //elemento moneda valor tributo
				        $objetoXML->text((string)$result[3][1]);//2.5% tarifa RTEF
				        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->endElement(); //fin Tipo impuesto

		        $objetoXML->endElement(); //fin total impuestos
		        }// fin if validar RTEF

		        //validar si existe RTEICA
                if(!@$foo[0][16]=="0"){
		        $objetoXML->startElement("TIM"); //elemento Total impuestos

			        $objetoXML->startElement("TIM_1"); //elemento impuesto retenido o retencion
			        $objetoXML->text('false');
			        $objetoXML->endElement(); //fin impuesto retenido o retencion

			        $objetoXML->startElement("TIM_2"); //elemento Valor del tributo Suma todos los IMP_4
			        $objetoXML->text((string)$foo[0][16]);
			        $objetoXML->endElement(); //fin valor del tributo

			        $objetoXML->startElement("TIM_3"); //elemento moneda valor tributo
			        $objetoXML->text('COP');
			        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->startElement("IMP"); //elemento Tipo impuesto

				        $objetoXML->startElement("IMP_1"); //elemento identificador del tributo
				        $objetoXML->text('07');//07=reteica
				        $objetoXML->endElement(); //fin identificador del tributo

				        $objetoXML->startElement("IMP_2"); //elemento Base impunible
				        $objetoXML->text((string)$foo[0][12]);
				        $objetoXML->endElement(); //fin moneda Base impunible

				        $objetoXML->startElement("IMP_3"); //elemento moneda de la base
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda de la base

				        $objetoXML->startElement("IMP_4"); //elemento valor del tributo
				        $objetoXML->text((string)$foo[0][16]);
				        $objetoXML->endElement(); //fin  valor tributo

				        $objetoXML->startElement("IMP_5"); //elemento moneda valor tributo
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda valor tributo

				        $objetoXML->startElement("IMP_6"); //elemento moneda valor tributo
				        $objetoXML->text((string)$result[2][1]);//2.5% tarifa RTEF
				        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->endElement(); //fin Tipo impuesto

		        $objetoXML->endElement(); //fin total impuestos
		        }// fin if validar RTEICA

		        //validar si existe reteiva
                if(!@$foo[0][17]=="0"){
		        $objetoXML->startElement("TIM"); //elemento Total impuestos

			        $objetoXML->startElement("TIM_1"); //elemento impuesto retenido o retencion
			        $objetoXML->text('false');
			        $objetoXML->endElement(); //fin impuesto retenido o retencion

			        $objetoXML->startElement("TIM_2"); //elemento Valor del tributo Suma todos los IMP_4
			        $objetoXML->text((string)$foo[0][17]);
			        $objetoXML->endElement(); //fin valor del tributo

			        $objetoXML->startElement("TIM_3"); //elemento moneda valor tributo
			        $objetoXML->text('COP');
			        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->startElement("IMP"); //elemento Tipo impuesto

				        $objetoXML->startElement("IMP_1"); //elemento identificador del tributo
				        $objetoXML->text('05');//05=RTEIVA
				        $objetoXML->endElement(); //fin identificador del tributo

				        $objetoXML->startElement("IMP_2"); //elemento Base impunible
				        $objetoXML->text((string)$foo[0][12]);
				        $objetoXML->endElement(); //fin moneda Base impunible

				        $objetoXML->startElement("IMP_3"); //elemento moneda de la base
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda de la base

				        $objetoXML->startElement("IMP_4"); //elemento valor del tributo
				        $objetoXML->text((string)$foo[0][17]);
				        $objetoXML->endElement(); //fin  valor tributo

				        $objetoXML->startElement("IMP_5"); //elemento moneda valor tributo
				        $objetoXML->text('COP');
				        $objetoXML->endElement(); //fin moneda valor tributo

				        $objetoXML->startElement("IMP_6"); //elemento moneda valor tributo
				        $objetoXML->text((string)$result[1][1]);//2.5% tarifa RTEF
				        $objetoXML->endElement(); //fin moneda valor tributo

			        $objetoXML->endElement(); //fin Tipo impuesto

		        $objetoXML->endElement(); //fin total impuestos
		        }// fin if validar RTEIVA

	      $objetoXML->startElement("DRF"); //elemento Numeracion de factura

	            $objetoXML->startElement("DRF_1"); //elemento numero de autorizacion
		        $objetoXML->text('18764004241125');
		        $objetoXML->endElement(); //fin numero de autorizacion

		        $objetoXML->startElement("DRF_2"); //elemento fec ini periodo de autorizacion
		        $objetoXML->text('2020-09-15');
		        $objetoXML->endElement(); //fin fec ini periodo de autorizacion

		        $objetoXML->startElement("DRF_3"); //elemento fec fin periodo de autorizacion
		        $objetoXML->text('2021-09-15');
		        $objetoXML->endElement(); //fin fec fin periodo de autorizacion

		        $objetoXML->startElement("DRF_4"); //elemento prefijo rango de numeracion
		        $objetoXML->text('FV');
		        $objetoXML->endElement(); //fin prefijo rango de numeracion

		        $objetoXML->startElement("DRF_5"); //elemento rango numeracion (minimo)
		        $objetoXML->text('1');
		        $objetoXML->endElement(); //fin rango numeracion (minimo)

		        $objetoXML->startElement("DRF_6"); //elemento rango numeracion (maximo)
		        $objetoXML->text('15000');
		        $objetoXML->endElement(); //fin nombre del tributo (maximo)

	      $objetoXML->endElement(); //fin Numeracion de factura

	      $objetoXML->startElement("MEP"); //elemento Medios de pago

                $objetoXML->startElement("MEP_1"); //elemento Medio de pago
		        $objetoXML->text('ZZZ');
		        $objetoXML->endElement(); //fin Medio de pago

		        $objetoXML->startElement("MEP_2"); //elemento Metodo de pago
		        $objetoXML->text((string)$foo[0][28]);
		        $objetoXML->endElement(); //fin Metodo de pago

                //fecha: si es contado no se requiere fecha
                if(!@$foo[0][29]==""){
		        $objetoXML->startElement("MEP_2"); //elemento fecha de pago
		        $objetoXML->text((string)$foo[0][29]);
		        $objetoXML->endElement(); //fin fecha de pago
		        } //fin fecha

		  $objetoXML->endElement(); //fin informacion Medios de pago

          for($i=0;$i<count($foo);$i++){//recorrer arreglos internos
	      $objetoXML->startElement("ITE"); //elemento Items del documento (productos)

	           $objetoXML->startElement("ITE_1"); //elemento Numero de linea
		       $objetoXML->text($i+1);// contador
		       $objetoXML->endElement(); //fin Numero de linea

		       $objetoXML->startElement("ITE_2"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_3"); //elemento Cantidad del producto o servicio
		       $objetoXML->text((string)$foo[$i][23]);
		       $objetoXML->endElement(); //fin Cantidad del producto o servicio

		       $objetoXML->startElement("ITE_4"); //elemento Unidad de medida
		       $objetoXML->text('94'); //(94=unidad)
		       $objetoXML->endElement(); //fin Unidad de medida

		       $objetoXML->startElement("ITE_5"); //elemento valor total de la linea
		       $objetoXML->text((string)$foo[$i][24]); //cantidad * precio - descuentos
		       $objetoXML->endElement(); //fin valor total de la linea

		       $objetoXML->startElement("ITE_6"); //elemento Moneda valor total linea
		       $objetoXML->text('COP');
		       $objetoXML->endElement(); //fin Moneda valor total linea

		       $objetoXML->startElement("ITE_7"); //elemento Valor articulo o servicio
		       $objetoXML->text((string)$foo[$i][26]);//precio unitario
		       $objetoXML->endElement(); //fin Valor articulo o servicio

		       $objetoXML->startElement("ITE_8"); //elemento Moneda valor articulo o servicio
		       $objetoXML->text('COP');
		       $objetoXML->endElement(); //fin Moneda valor articulo o servicio

		       $objetoXML->startElement("ITE_9"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_10"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_11"); //elemento Descripcion del articulo
		       $objetoXML->text((string)$foo[$i][25]);
		       $objetoXML->endElement(); //fin Descripcion del articulo

		       $objetoXML->startElement("ITE_12"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_13"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_14"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       // $objetoXML->startElement("ITE_15"); //elemento Nulo
		       // $objetoXML->text('');
		       // $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_16"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_17"); //elemento Nulo
		       $objetoXML->text("");
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_18"); //elemento Nulo
		       $objetoXML->text("");
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_19"); //elemento Total de ITEM
		       $objetoXML->text((string)$foo[$i][24]);
		       $objetoXML->endElement(); //fin Total de ITEM

		       $objetoXML->startElement("ITE_20"); //elemento Moneda total del ITEM
		       $objetoXML->text('COP');
		       $objetoXML->endElement(); //fin Moneda total del ITEM

		       $objetoXML->startElement("ITE_21"); //elemento Valor a pagar del ITEM
		       $objetoXML->text((string)$foo[$i][24]);
		       $objetoXML->endElement(); //fin Valor a pagar del ITEM

		       $objetoXML->startElement("ITE_22"); //elemento Moneda del Valor a pagar del ITEM
		       $objetoXML->text('COP');
		       $objetoXML->endElement(); //fin Moneda del Valor a pagar del ITEM

		       // $objetoXML->startElement("ITE_23"); //elemento Nulo
		       // $objetoXML->text('');
		       // $objetoXML->endElement(); //fin Nulo

		       // $objetoXML->startElement("ITE_24"); //elemento Nulo
		       // $objetoXML->text('');
		       // $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_25"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_26"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("ITE_27"); //elemento Cantidad real aplica precio
		       $objetoXML->text((string)$foo[$i][23]);
		       $objetoXML->endElement(); //fin Cantidad real aplica precio

		       $objetoXML->startElement("ITE_28"); //elemento Unidad de medida cant articulo
		       $objetoXML->text('94');
		       $objetoXML->endElement(); //fin Unidad de medida cant articulo

		       $objetoXML->startElement("ITE_29"); //elemento Nulo
		       $objetoXML->text('');
		       $objetoXML->endElement(); //fin Nulo

		       $objetoXML->startElement("IAE"); //elemento informacion identificacion productos

		           $objetoXML->startElement("IAE_1"); //elemento Codigo de producto
		           $objetoXML->text((string)$foo[$i][27]);
		           $objetoXML->endElement(); //fin codigo de prducto

		           $objetoXML->startElement("IAE_2"); //elemento Codigo del estandar segun DIAN
		           $objetoXML->text('999');
		           $objetoXML->endElement(); //fin codigo del estandar

		       $objetoXML->endElement(); //fin informacion identificacion productos

                 //Validar si existe IVA
                if (!@$foo[0][14]=="0"){
		       $objetoXML->startElement("TII"); //elemento Total impuestos del item (productos)
			       $objetoXML->startElement("TII_1"); //elemento Valor del tributo
			       $objetoXML->text((string)$foo[$i][33]);
			       $objetoXML->endElement(); //fin Valor del tributo

			       $objetoXML->startElement("TII_2"); //elemento Moneda valor del tributo
			       $objetoXML->text('COP');
			       $objetoXML->endElement(); //fin Moneda valor del tributo

			       $objetoXML->startElement("TII_3"); //elemento indica si el impuesto es retenido o retencion
			       $objetoXML->text('false');
			       $objetoXML->endElement(); //fin impuesto es retenido o retencion

			       $objetoXML->startElement("IIM"); //elemento Impuestos

	                   $objetoXML->startElement("IIM_1"); //elemento Identificador del tributo
				       $objetoXML->text('01'); //01 IVA
				       $objetoXML->endElement(); //fin Identificador del tributo

				       $objetoXML->startElement("IIM_2"); //elemento Valor del tributo
				       $objetoXML->text((string)$foo[$i][33]);
				       $objetoXML->endElement(); //fin Valor del tributo

				       $objetoXML->startElement("IIM_3"); //elemento Moneda del Valor del tributo
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin Moneda del Valor del tributo

				       $objetoXML->startElement("IIM_4"); //elemento Base sobre la que se calcula valor dle tributo
				       $objetoXML->text((string)$foo[$i][24]);
				       $objetoXML->endElement(); //fin Base sobre la que se calcula valor dle tributo

				       $objetoXML->startElement("IIM_5"); //elemento Moneda de la base
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin moneda de la base

				       $objetoXML->startElement("IIM_6"); //elemento tarifa del tributo
				       $objetoXML->text((string)$result[0][1]);//iva = 19%
				       $objetoXML->endElement(); //fin moneda de la base

			       $objetoXML->endElement(); //fin Impuestos


               $objetoXML->endElement(); //fin Total impuestos del item (productos)
               }// fin if

               //Validar si existe ReteFuente
                   if(!@$foo[0][15]=="0"){
            $objetoXML->startElement("TII"); //elemento Total impuestos del item (productos)
			       $objetoXML->startElement("TII_1"); //elemento Valor del tributo
			       $objetoXML->text((string)$foo[$i][30]);
			       $objetoXML->endElement(); //fin Valor del tributo

			       $objetoXML->startElement("TII_2"); //elemento Moneda valor del tributo
			       $objetoXML->text('COP');
			       $objetoXML->endElement(); //fin Moneda valor del tributo

			       $objetoXML->startElement("TII_3"); //elemento indica si el impuesto es retenido o retencion
			       $objetoXML->text('false');
			       $objetoXML->endElement(); //fin impuesto es retenido o retencion

                   $objetoXML->startElement("IIM"); //elemento Impuestos

	                   $objetoXML->startElement("IIM_1"); //elemento Identificador del tributo
				       $objetoXML->text('06'); //06 ReteIva
				       $objetoXML->endElement(); //fin Identificador del tributo

				       $objetoXML->startElement("IIM_2"); //elemento Valor del tributo
				       $objetoXML->text((string)$foo[$i][30] );
				       $objetoXML->endElement(); //fin Valor del tributo

				       $objetoXML->startElement("IIM_3"); //elemento Moneda del Valor del tributo
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin Moneda del Valor del tributo

				       $objetoXML->startElement("IIM_4"); //elemento Base sobre la que se calcula valor dle tributo
				       $objetoXML->text((string)$foo[$i][24]);
				       $objetoXML->endElement(); //fin Base sobre la que se calcula valor dle tributo

				       $objetoXML->startElement("IIM_5"); //elemento Moneda de la base
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin moneda de la base

				       $objetoXML->startElement("IIM_6"); //elemento tarifa del tributo
				       $objetoXML->text((string)$result[3][1]);//rtef = 2.5%
				       $objetoXML->endElement(); //fin moneda de la base

			       $objetoXML->endElement(); //fin Impuestos

                 $objetoXML->endElement(); //fin Total impuestos del item (productos)RTEF
                   }// fin if retefuente

                   //Validar si existe Reteica
                   if(!@$foo[0][16]=="0"){
            $objetoXML->startElement("TII"); //elemento Total impuestos del item (productos)
			       $objetoXML->startElement("TII_1"); //elemento Valor del tributo
			       $objetoXML->text((string)$foo[$i][31]);
			       $objetoXML->endElement(); //fin Valor del tributo

			       $objetoXML->startElement("TII_2"); //elemento Moneda valor del tributo
			       $objetoXML->text('COP');
			       $objetoXML->endElement(); //fin Moneda valor del tributo

			       $objetoXML->startElement("TII_3"); //elemento indica si el impuesto es retenido o retencion
			       $objetoXML->text('false');
			       $objetoXML->endElement(); //fin impuesto es retenido o retencion

                   $objetoXML->startElement("IIM"); //elemento Impuestos

	                   $objetoXML->startElement("IIM_1"); //elemento Identificador del tributo
				       $objetoXML->text('06'); //06 ReteIva
				       $objetoXML->endElement(); //fin Identificador del tributo

				       $objetoXML->startElement("IIM_2"); //elemento Valor del tributo
				       $objetoXML->text((string)$foo[$i][31] );
				       $objetoXML->endElement(); //fin Valor del tributo

				       $objetoXML->startElement("IIM_3"); //elemento Moneda del Valor del tributo
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin Moneda del Valor del tributo

				       $objetoXML->startElement("IIM_4"); //elemento Base sobre la que se calcula valor dle tributo
				       $objetoXML->text((string)$foo[$i][24]);
				       $objetoXML->endElement(); //fin Base sobre la que se calcula valor dle tributo

				       $objetoXML->startElement("IIM_5"); //elemento Moneda de la base
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin moneda de la base

				       $objetoXML->startElement("IIM_6"); //elemento tarifa del tributo
				       $objetoXML->text((string)$result[2][1]);//rtef = 2.5%
				       $objetoXML->endElement(); //fin moneda de la base

			       $objetoXML->endElement(); //fin Impuestos

                 $objetoXML->endElement(); //fin Total impuestos del item (productos)RTEICA
                   }// fin if reteica

                   //Validar si existe Reteiva
                   if(!@$foo[0][17]=="0"){
            $objetoXML->startElement("TII"); //elemento Total impuestos del item (productos)
			       $objetoXML->startElement("TII_1"); //elemento Valor del tributo
			       $objetoXML->text((string)$foo[$i][32]);
			       $objetoXML->endElement(); //fin Valor del tributo

			       $objetoXML->startElement("TII_2"); //elemento Moneda valor del tributo
			       $objetoXML->text('COP');
			       $objetoXML->endElement(); //fin Moneda valor del tributo

			       $objetoXML->startElement("TII_3"); //elemento indica si el impuesto es retenido o retencion
			       $objetoXML->text('false');
			       $objetoXML->endElement(); //fin impuesto es retenido o retencion

                    $objetoXML->startElement("IIM"); //elemento Impuestos

	                   $objetoXML->startElement("IIM_1"); //elemento Identificador del tributo
				       $objetoXML->text('07'); //07 reteIca
				       $objetoXML->endElement(); //fin Identificador del tributo

				       $objetoXML->startElement("IIM_2"); //elemento Valor del tributo
				       $objetoXML->text((string)$foo[$i][32]);
				       $objetoXML->endElement(); //fin Valor del tributo

				       $objetoXML->startElement("IIM_3"); //elemento Moneda del Valor del tributo
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin Moneda del Valor del tributo

				       $objetoXML->startElement("IIM_4"); //elemento Base sobre la que se calcula valor dle tributo
				       $objetoXML->text((string)$foo[$i][23]);
				       $objetoXML->endElement(); //fin Base sobre la que se calcula valor dle tributo

				       $objetoXML->startElement("IIM_5"); //elemento Moneda de la base
				       $objetoXML->text('COP');
				       $objetoXML->endElement(); //fin moneda de la base

				       $objetoXML->startElement("IIM_6"); //elemento tarifa del tributo
				       $objetoXML->text((string)$result[1][1]);//rtiva = 15%
				       $objetoXML->endElement(); //fin moneda de la base

			       $objetoXML->endElement(); //fin Impuestos

                 $objetoXML->endElement(); //fin Total impuestos del item (productos)RTEVA
                   }// fin if reteiva

	      $objetoXML->endElement(); //fin Items del documento
	      }// fin for

    $objetoXML->endElement(); // Final del nodo raíz, "FACTURA"

	$objetoXML->endDocument();// Final del documento

	return 1;
}

//execute method
if(isset($_GET['idF']) && isset($_GET['idC'])){
	$idfactura=$_GET['idF'];
	$idcliente=$_GET['idC'];


    if (generarXml($idcliente,$idfactura,"FACT_".$idfactura)){
        require_once 'ConsumoWS.php'; //required file
        //send variable by get
          header("Location: ConsumoWS.php?namefile=FACT_".$idfactura."&numfactura=".$idfactura);
    	//echo "Se genero Xml";
    }
    else {
    	echo "Ocurrio un error";
    }
    //echo $idfactura."<br>";
    //echo $idcliente."<br>";
  }

//generarXml(8903236678,38,"archivo");