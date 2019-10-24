<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Learning Analytics - Eina experimental - UOC - versió 1.1</title>
<link rel="stylesheet" type="text/css" href="estilos.css" />

<script language="javascript">

function ocultarFicha(primera, segunda, tercera, cuarta, quinta, sexta, septima) {
	document.getElementById('procesado').hidden=primera;
	document.getElementById('metricasIndividuales').hidden=segunda;
	document.getElementById('clasificaciones').hidden=tercera;
	document.getElementById('nubeEtiquetas').hidden=cuarta;
	document.getElementById('frecuenciaPalabras').hidden=quinta;
	document.getElementById('feedbackCatalan').hidden=sexta;
	document.getElementById('feedbackCastellano').hidden=septima;

	return 0;
}

</script>
<script src="tagcanvas.js" type="text/javascript"></script>
<script type="text/javascript">
  window.onload = function() {
    try {
	TagCanvas.interval = 20;
	TagCanvas.textFont = 'Impact,Arial Black,sans-serif';
	//TagCanvas.textColour = '#00f';
	TagCanvas.textColour = null;
	TagCanvas.textHeight = 25;
	TagCanvas.outlineColour = '#f96';
	TagCanvas.outlineThickness = 1;
	TagCanvas.maxSpeed = 0.08;
	TagCanvas.minBrightness = 0.1;
	TagCanvas.depth = 0.92;
	TagCanvas.pulsateTo = 0.2;
	TagCanvas.pulsateTime = 0.75;
	TagCanvas.initial = [0.1,-0.1];
	TagCanvas.decel = 0.98;
	TagCanvas.reverse = true;
	TagCanvas.hideTags = true;
	TagCanvas.shadow = '#ccf';
	TagCanvas.shadowBlur = 0;
	TagCanvas.weight = true;
	TagCanvas.weightSize = 1.0;
	TagCanvas.weightMode = 'both';
	TagCanvas.weightFrom = 'peso';
  	TagCanvas.fadeIn = 800;
  	TagCanvas.weightGradient = { 0: "#f00", 1: "#ff0" };
  	TagCanvas.zoom = 1.0;
  	//TagCanvas.shape = 'hring';
    TagCanvas.Start("mycanvas","tags",{
            //textColour: '#ff0000',
            //outlineColour: '#ff00ff',
            //reverse: true,
            //depth: 0.8,
            //maxSpeed: 0.05
          });
    } catch(e) {
      // something went wrong, hide the canvas container
      document.getElementById('nube').style.display = 'none';
    }
  };
</script>

</head>

<body>
<?php
// DECLARACIÓN DE FUNCIONES
function standard_deviation($aValues)
{
    $fMean = array_sum($aValues) / count($aValues);
    //print_r($fMean);
    $fVariance = 0.0;
    foreach ($aValues as $i)
    {
        $fVariance += pow($i - $fMean, 2);

    }       
    $size = count($aValues) - 1;
    return (float) sqrt($fVariance)/sqrt($size);
}

?>

<div id="contenedor">

<img src="img/cabecera.png" width="1000" height="100" alt="" align="center"/>
<div class="titulo" align="center"><strong>- Learning Analytics -</strong></div>
<div class="titulo" align="center"><strong>Eina experimental per a l'anàlisi de la interacció comunicativa</strong></div>
<br />
<div class="titulo2" align="center"><strong>- Resultat de l'anàlisi per al període del [
<?php
$fecha1=$_GET["fecha1"];
	   $hora=date("G",$fecha1)*3600;
	   $minutos=date("i",$fecha1)*60;
	   $segundos=date("s",$fecha1);
	   $fecha1=$fecha1-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
echo date("d M Y",$fecha1);
?>
 ] al [
<?php
$fecha2=$_GET["fecha2"];
	   $hora=date("G",$fecha2)*3600;
	   $minutos=date("i",$fecha2)*60;
	   $segundos=date("s",$fecha2);
	   $fecha2=$fecha2-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
echo date("d M Y",$fecha2);
?>
 ] -</strong>
 &nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="TORNAR" style="alignment-baseline:central;" onclick="window.open('http://www.paucasals.com/missatgesUOC/index.php','_self');"/>
 </div>
<br />

<?php 

$carpeta=$_GET["carpeta"];
$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

// CREACIÓN DEL FICHERO DE NODOS Y ARISTAS PARA IMPORTARLO A GEPHI

// Creamos el fichero de salida y escribimos la cabecera
$ficheroSalida=fopen("sna.gexf","w"); 
fputs($ficheroSalida,"<?xml version='1.0' encoding='UTF-8'?>\r\n<gexf xmlns='http://www.gexf.net/1.2draft' version='1.2'>\r\n<meta lastmodifieddate='2009-03-20'>\r\n<creator>Juan Pedro Cerro Martínez</creator>\r\n        <description>Generador de fitxers GEXF a partir d'espais de comunicació de la UOC en format d'aula nova</description>\r\n</meta>\r\n<graph mode='static' defaultedgetype='directed'>\r\n<nodes>\r\n");
    
// Definimos el contador de aristas
$idArista=0; 

// Declaramos la lista de estudiantes para contar mensajes
$mensajesPorEstudiante= array();

// Declaramos la lista de estudiantes para contar mensajes de respuesta
$mensajesRespuestaPorEstudiante= array();

// Declaramos la lista de estudiantes populares para contar respuestas
$popularidadPorEstudiante= array();

// Declaramos la lista de palabras promedio por estudiante
$palabrasPorEstudiante= array();

// Declaramos el contador de mensajes
$numMensajes=0;

// Declaramos el contador de respuestas
$numRespuestas=0;

// Declaramos la lista de fechas (timestamp) de los mensajes de cada estudiante
$fechasMensajePorEstudiante=array(array());

// Declaramos el número de mensajes por día
$mensajesPorDia= array();

// Declaramos la lista con el número de enlaces por usuario
$enlacesPorEstudiante= array(); 

// Declaramos la lista con el número de ficheros adjuntos por usuario
$adjuntosPorEstudiante= array(); 

// Declaramos el fichero de salida con el Feedback de los alumnos IDIOMA CASTELLANO
$ficheroFeedbackCastellano=fopen("feedback_cas_utf8_tab.csv","w"); 
fputs($ficheroFeedbackCastellano,"Usuario".chr(9)."Feedback\r\n");

// Declaramos el fichero de salida con el Feedback de los alumnos IDIOMA CATALÁN
$ficheroFeedbackCatalan=fopen("feedback_cat_utf8_tab.csv","w"); 
fputs($ficheroFeedbackCatalan,"Usuari".chr(9)."Feedback\r\n");

?>

<div id="procesado" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px; ">
<a name="procesado"> 

<div class="titulo" align="center"><u><strong>PROCESSAT DE MISSATGES</strong></u></div>
<?php
// Nos recorremos todo el directorio para detectar nodos y hacer estadística
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes++;
	   // Leemos el primer mensaje
	   echo "<br />Tractant missatge nº: <strong>".$archivos."</strong>";
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   
	   // Computamos este mensaje al contador del estudiante
	   if ($mensajesPorEstudiante[$estudiante]>0) {
		   $mensajesPorEstudiante[$estudiante]++;
	   } else {
		   $mensajesPorEstudiante[$estudiante]=1;
	   }
	   
	   // Cogemos la fecha de envío del mensaje
	   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
	   $fecha=strtotime($fecha);
	   $fechasMensajePorEstudiante[$estudiante][sizeof($fechasMensajePorEstudiante[$estudiante])]=$fecha;

	// Incrementamos el contador de mensajes por días
	   $hora=date("G",$fecha)*3600;
	   $minutos=date("i",$fecha)*60;
	   $segundos=date("s",$fecha);
	   $fechaConvertida=$fecha-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	   
	   if ($mensajesPorDia[$fechaConvertida]==0){
		   $mensajesPorDia[$fechaConvertida]=1;
	   } else {
		   $mensajesPorDia[$fechaConvertida]++;
	   }


	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));

	   // Escribimos la línea en el fichero de nodos
	   fputs($ficheroSalida,"<node id=\"".$id."\" label=\"".$estudiante."\"></node>\r\n");

	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	
		if (!feof($mensaje)) {
			// Incrementamos el contador de respuestas
			$numRespuestas++;
			
				   // Computamos este mensaje al contador de respuestas del estudiante
	   				if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
		   				$mensajesRespuestaPorEstudiante[$estudiante]++;
	   				} else {
		   				$mensajesRespuestaPorEstudiante[$estudiante]=1;
	   				}

			
			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			echo "...és una resposta al missatge nº: ".$idRespuesta;
			
			// Incrementamos el contador de populares
			
			// Declaramos la variable de apertura temporal de ficheros respuesta
			$ficheroRespuesta=fopen("./".$carpeta."/".$idRespuesta.".mail", "r");
			// Buscamos el nombre del estudiante al que se le responde
	   		$linea = fgets($ficheroRespuesta);
	   		$estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
			// Computamos este mensaje de respuesta al estudiante
	   		if ($popularidadPorEstudiante[$estudiante]>0) {
				$popularidadPorEstudiante[$estudiante]++;
	   		} else {
		   		$popularidadPorEstudiante[$estudiante]=1;
	   		}
			fclose($ficheroRespuesta);
			
		} else {
			echo "...és un missatge NOU";
		}
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Acabamos con los nodos
fputs($ficheroSalida,"</nodes>\r\n");

// Cerramos el directorio liberando recursos
closedir($directorio);

$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio de nuevo

// Empezamos con las aristas
fputs($ficheroSalida,"<edges>\r\n");

// Recorremos una segunda vez el directorio para guardar las aristas
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));
	   
	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }

		if (!feof($mensaje)) {

			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			// Escribimos la linea en el fichero de aristas
			fputs($ficheroSalida,"<edge source=\"".$id."\" target=\"".$idRespuesta."\" type=\"directed\" id=\"".$idArista++."\" weight=\"1\"></edge>\r\n");

		}
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Escribimos el final del archivo
fputs($ficheroSalida,"</edges>\r\n</graph>\r\n</gexf>");

// Cerramos el fichero de salida
fclose($ficheroSalida);
echo "<br /><br /># FITXER SNA CREAT (<a href='sna.gexf' target='_blank'>sna.gexf</a>)<br />";

// Cerramos el directorio liberando recursos
closedir($directorio);

?>

</div>

<?php

// VOLVEMOS A RECORRER LOS ARCHIVOS PARA COMPUTAR EL NÚMERO DE PALABRAS PROMEDIO
// Y CONTAR EL NÚMERO DE ENLACES Y ADJUNTOS QUE TIENE EL MENSAJE


// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes


$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio de nuevo

while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);
	   // Comenzamos a leer el cuerpo del mensaje
	   
	   if (!feof($mensaje)) {
		   $linea =  quoted_printable_decode(fgets($mensaje));
		   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			   $numPalabras = str_word_count($linea);
				// Sumamos el número de palabras al estudiante
	   			if ($palabrasPorEstudiante[$estudiante]>0) {
					$palabrasPorEstudiante[$estudiante]+=$numPalabras;
	   			} else {
		   			$palabrasPorEstudiante[$estudiante]=$numPalabras;
	   			}
			   $linea =  quoted_printable_decode(fgets($mensaje));
		   } 
	   }


	   // Buscamos la parte del mensaje que está en formato HTML
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,23))!="content-type: text/html") && (!feof($mensaje))) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
	   }

	   // Saltamos 2 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);

	   $linea = fgets($mensaje);
	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
		   if ((strpos(strtolower($linea),"<a ")!=false) || (strpos(strtolower($linea),"http")!=false)) {
			   // Sumamos el número de enlaces al estudiante
   				if ($enlacesPorEstudiante[$estudiante]>0) {
					$enlacesPorEstudiante[$estudiante]++;
   				} else {
	   				$enlacesPorEstudiante[$estudiante]=1;
   				}
		   }
		   $linea =  fgets($mensaje);
	   } 



	   // Buscamos si el mensaje contiene alguna otra sección que indicará si hay ficheros
 	   while (!feof($mensaje)) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
			if (utf8_encode(substr($linea,0,13))=="Content-Type:") {
			   // Sumamos el número de adjuntos al estudiante
   				if ($adjuntosPorEstudiante[$estudiante]>0) {
					$adjuntosPorEstudiante[$estudiante]++;
   				} else {
	   				$adjuntosPorEstudiante[$estudiante]=1;
   				}
				
			}
	   }

   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}


// AHORA COMENZAMOS A VISUALIZAR LOS RESULTADOS
?>

<div class="cuadroInfo" id="metricasGlobales" style="width:1000px; height:auto; padding:5px; background:#BBB; border: 1px solid black; margin:auto; position:absolute; top:180px;">

<div class="titulo" align="center"><u><strong>INDICADORS/MÈTRIQUES GLOBALS</strong></u></div>
<div align="center">
<div class="tablaMetricas" style="width:750px;">
   <table style="width:750px;">
		<tr>
        <td style="width:300px;">
        INDICADOR
        </td>
        <td style="width:450px;">
		MÈTRICA
        </td>
        </tr>
        <tr><td style="font-size:12px;">Participació en la interacció comunicativa</td>

   <td style="font-size:12px;">
<img src="img/usuarios.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
<?php
//Mostrar el número total de usuarios participantes
echo "# Usuaris participants: <strong>".count($mensajesPorEstudiante)."</strong><br/>";
?>
<img src="img/mensajes.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" />
<?php
//Mostrar el núnmero total de mensajes
echo "# Missatges totals analitzats: <strong>".$numMensajes."</strong><br/>";
?>
<img src="img/homogeneidad.png" width="20px" style="vertical-align:middle; margin:0px 3px 3px 3px;" />
<?php
$valor=1-(standard_deviation($mensajesPorEstudiante)/((max($mensajesPorEstudiante)+min($mensajesPorEstudiante))/2));
$valor*=100;
$valor=round($valor,2);
echo "# Grau d'homogeneïtat participativa: <strong>".$valor."%</strong>";
?>
</td></tr>

<tr><td style="font-size:12px; background-color:#CCC;">Foment del diàleg i de la negociació</td>
   <td style="font-size:12px; background-color:#CCC;">

<img src="img/responder.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo "# Missatges de resposta totals: <strong>".$numRespuestas."</strong><br/>";
?>
<img src="img/dialogo.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo "# Nivell de diàleg (respostes vs. missatges): <strong>".(round($numRespuestas/($numMensajes-1),4)*100)."%</strong>";
?>

</td></tr>

<tr><td style="font-size:12px;">Estil comunicatiu i llenguatge utilitzat</td>
   <td style="font-size:12px;">
   <img src="img/nube_etiquetas.gif" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
 # Contingut textual (per al núvol d'etiquetes): <a href="tagcloud.txt" target="_blank">tagcloud.txt</a><br/>
   <img src="img/extension.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
 # Extensió mitjana en la comunicació: <strong>
   <?php
   echo round(array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante),0);
   ?>   
    paraules</strong>
   </td></tr>
   
<tr><td style="font-size:12px; background-color:#CCC;">Tipus de comunicació</td>
<td style="font-size:12px; background-color:#CCC;">
<img src="img/expandir.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
<?php
//Mostrar el nivell de dispersió del debat
echo "# Grau de dispersió: <strong>";
	$valor=round((($numMensajes-1)/$numMensajes)-($numRespuestas/$numMensajes),2);
		if ($valor>0.55){
		echo "Conversa dispersa";
	} elseif ($valor>=0.45) {
		echo "Conversa equilibrada";
	} else {
		echo "Conversa concentrada";
	}
echo " (".$valor*100 ."%)</strong><br>";
?>
   <img src="img/grafo_nodos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 

# Graf de nodes .gexf (GEPHI): <a href="sna.gexf" target="_blank">sna.gexf</a>
</td></tr>

</table>
</div>



<strong>INDICADOR: Constància i regularitat en la interacció grupal</strong><br />
MÈTRICA: Distribució temporal i grupal de missatges

<!-- Ahora presentamos la gráfica de distribución grupal de mensajes por día -->

<div class="tablaGrafica" id="mensajesPorDias" style="height:350px; position:relative; overflow-x: auto;">
<table  border="0px;" cellspacing="0px;" cellpadding="0px;" style="font-family: Verdana, Geneva, sans-serif;
	font-size:10px;">
<tr>
<?php
ksort($mensajesPorDia);
$fechaActual=$fecha1;
while ($fechaActual<=$fecha2) {
	echo "<td width=\"30px\" height=\"250px\" align=\"center\" valign=\"bottom\">";
	echo $mensajesPorDia[$fechaActual]."<br />";
	echo "<img src=\"img/cuadro.png\" width=\"20px\" height=\"";
	echo $mensajesPorDia[$fechaActual]*250/max($mensajesPorDia);
	echo "\">";
	echo "</td>";
	$fechaActual=$fechaActual+86400; // Sumamos un día en segundos
	   $hora=date("G",$fechaActual)*3600;
	   $minutos=date("i",$fechaActual)*60;
	   $segundos=date("s",$fechaActual);
	   $fechaActual=$fechaActual-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
}
?>
</tr>
<tr>
<?php
$fechaActual=$fecha1;
while ($fechaActual<=$fecha2) {
	echo "<td width=\"30px\" align=\"center\" valign=\"bottom\">";
	echo "<img src=\"img/marcador.png\" width=\"30px\">";
	echo "</td>";
	$fechaActual=$fechaActual+86400; // Sumamos un día en segundos
	   $hora=date("G",$fechaActual)*3600;
	   $minutos=date("i",$fechaActual)*60;
	   $segundos=date("s",$fechaActual);
	   $fechaActual=$fechaActual-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
}
?>
</tr>
<tr>
<?php
$fechaActual=$fecha1;
while ($fechaActual<=$fecha2) {
	echo "<td width=\"30px\" align=\"center\" valign=\"bottom\">";
	echo date("d M",$fechaActual);
	echo "</td>";
	$fechaActual=$fechaActual+86400; // Sumamos un día en segundos
	   $hora=date("G",$fechaActual)*3600;
	   $minutos=date("i",$fechaActual)*60;
	   $segundos=date("s",$fechaActual);
	   $fechaActual=$fechaActual-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
}
?>
</tr>
</table> 
</div>



</div>
</div>


<div id="metricasIndividuales" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">
<a name="individuales"> 

<div class="titulo" align="center"><u><strong>INDICADORS I MÈTRIQUES INDIVIDUALS</strong></u></div>
<br />
<div align="left"><table width="1070px" align="center" style="margin:10px; padding:0px; border:1px; text-align:center; background-color:#0F0; border-spacing:0px;	border:1px solid #000000; border-width:1px 1px 1px 1px;">
                    <tr>
                        <td style="width:255px; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            <strong>INDICADORS DE REFERÈNCIA</strong>
                        </td>
                        <td colspan="2" style="width:185px; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            Participació en la interacció comunicativa
                        </td>
                        <td colspan="2" style="width:165px; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            Foment del diàleg i de la negociació
                        </td>
                        <td style="width:80px; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            Estil comunicatiu
                        </td>
                        <td style="width:220px; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            Constància i regularitat en la interacció grupal
                        </td>
                        <td style="width:170x; border:1px solid #000000; border-width:1px 1px 1px 1px;">
                            Intercanvi d'informació
                        </td>
                    </tr>
				</table></div>

<div class="tablaMetricas" >
                <table width="1090px">
                    <tr>
                        <td style="width:250px;">
                            Usuari
                        </td>
                        <td style="width:75px;">
                            Missatges totals
                        </td>
                        <td style="width:100px;">
                            Nivell de participació
                        </td>
                        <td style="width:75px;">
                            Respostes
                        </td>
                        <td style="width:75px;">
                            Popularitat
                        </td>
                        <td style="width:75px;">
                            Paraules promig
                        </td>
                        <td style="width:210px;">
                            Distribució temporal de missatges individuals
                        </td>
                        <td style="width:75px;">
                            Adjunts publicats
                        </td>
                        <td style="width:75px;">
                            Enllaços externs
                        </td>
                    </tr>
            
<?php

// Listar la estadística por estudiante
ksort($mensajesPorEstudiante);
foreach ($mensajesPorEstudiante as $estudiante => $total) {
    echo "<tr><td><strong>".$estudiante."</strong></td><td>".$total;
	
	//Añadimos flechas de promedio
	$promedio=array_sum($mensajesPorEstudiante)/count($mensajesPorEstudiante);
	if ($total<$promedio) {
		echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
	} else {
			if ($total>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
				
	}

	echo "</td><td>";
	$valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
		if ($valor>1){
			echo "Molt participatiu";
		} elseif ($valor>=0.5) {
			echo "Participatiu";
		} elseif ($valor>0) {
			echo "Poc participatiu";
		} else {
			echo "No participa";
		}
	
	echo "</td><td>".$mensajesRespuestaPorEstudiante[$estudiante];

	//Añadimos flechas de promedio
	if ($mensajesRespuestaPorEstudiante[$estudiante]!=0) {
		$promedio=array_sum($mensajesRespuestaPorEstudiante)/count($mensajesRespuestaPorEstudiante);
		if ($mensajesRespuestaPorEstudiante[$estudiante]<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if ($mensajesRespuestaPorEstudiante[$estudiante]>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}
	}

	echo "</td><td>".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."%</td>";
	
	echo "<td>".round($palabrasPorEstudiante[$estudiante]/$total,0);
	
	//Añadimos flechas de promedio
		$promedio=array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante);
		if (round($palabrasPorEstudiante[$estudiante]/$total,0)<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if (round($palabrasPorEstudiante[$estudiante]/$total,0)>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}
	
	

// Calcular y llistar la distribució temporal de missatges

echo "<td>";

		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;
		echo "<img src=\"img/amplitud.png\" width=\"25px\" title=\"Finestra temporal des del primer al darrer missatge.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ".round($periodo,1)." dies";
		echo " <img src=\"img/dinamizar.png\" width=\"20px\" title=\"Dates de publicació:\n---------------------------\n";
		
		sort($fechasMensajePorEstudiante[$estudiante]);
		$contador=0;
		while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
			echo date("d M Y G:i:s",$fechasMensajePorEstudiante[$estudiante][$contador])."\n";
			$contador++;
		}
		
		echo "\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
if (sizeof($fechasMensajePorEstudiante[$estudiante])==1) {
	echo "Missatge únic";
}
else {
	if (sizeof($fechasMensajePorEstudiante[$estudiante])<=3) {
	echo "Pocs missatges";
	}
	else {
		
		$contador=1;
		unset($fechas);
		$fechas=array();
		while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
			$fechas[$contador-1]=($fechasMensajePorEstudiante[$estudiante][$contador]-$fechasMensajePorEstudiante[$estudiante][$contador-1])/86400;
			$contador++;
		}
		$dinamització=standard_deviation($fechas);

		if ($dinamització<2) {
			echo "Molt distribuït";
		}
		else {
			if ($dinamització<4) {
			echo "Distribuït";
		}
		else {
			echo "Poc distribuït";
		}
		}
		
}

}

echo "</td><td>".$adjuntosPorEstudiante[$estudiante]."</td><td>".$enlacesPorEstudiante[$estudiante];

echo "</td></tr>";




}
?>

                </table>
            </div>

</div> 
 
<div id="clasificaciones" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">
<a name="clasificaciones"> 

<div class="titulo" align="center"><u><strong>CLASSIFICACIONS PER MÈTRIQUES (de major a menor)</strong></u></div>

<?php

// AHORA COMENZAMOS A VISUALIZAR LOS RESULTADOS

// Listar el número de mensajes por estudiante
echo "<br /># NOMBRE DE MISSATGES LLIURATS PER USUARI";
echo "<br />__________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($mensajesPorEstudiante);
foreach ($mensajesPorEstudiante as $estudiante => $total) {
    echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$total."</td></tr>";
}
?>
</table>

<?php

// Listar el número de mensajes de RESPUESTA por estudiante
echo "<br /># NOMBRE DE MISSATGES DE RESPOSTA LLIURATS PER USUARI";
echo "<br />______________________________________________________<br />";
?>
<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($mensajesRespuestaPorEstudiante);
foreach ($mensajesRespuestaPorEstudiante as $estudiante => $total) {
    echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$total."</td></tr>";
}
?>
</table>

<?php
// Listar el número de palabras promedio por estudiante
echo "<br /># NOMBRE PROMIG DE PARAULES PER USUARI";
echo "<br />________________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
foreach ($mensajesPorEstudiante as $estudiante => $total) {
    $promedio=round($palabrasPorEstudiante[$estudiante]/$total,0);
	$promedioPalabrasPorEstudiante[$estudiante]=$promedio;
}

$contador=0;
arsort($promedioPalabrasPorEstudiante);
foreach ($promedioPalabrasPorEstudiante as $estudiante => $total) {
	echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$total."</td></tr>";
}
?>
</table>

<?php

// Listar el nivel de participación comunicativa por cada estudiante
echo "<br /># NIVELL DE PARTICIPACIÓ EN LA INTERACCIÓ COMUNICATIVA PER USUARI";
echo "<br />___________________________________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($mensajesPorEstudiante);
foreach ($mensajesPorEstudiante as $estudiante => $total) {
    $valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
	echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$valor." (";
	if ($valor>1){
		echo "Molt participatiu";
	} elseif ($valor>=0.5) {
		echo "Participatiu";
	} elseif ($valor>0) {
		echo "Poc participatiu";
	} else {
		echo "No participa";
	}
	echo ")</td></tr>";
}
?>

</table>

<?php
// Mostrar la lista de popularidad de cada estudiante
echo "<br /># POPULARITAT PER CADA USUARI";
echo "<br />________________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($popularidadPorEstudiante);
foreach ($popularidadPorEstudiante as $estudiante => $total) {
    echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".round(100*$total/$numRespuestas,2)."%</td></tr>";
}

?>
</table>


<?php
// Mostrar la lista de archivos adjuntos publicados de cada estudiante
echo "<br /># ARXIUS ADJUNTS PER CADA USUARI";
echo "<br />________________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($adjuntosPorEstudiante);
foreach ($adjuntosPorEstudiante as $estudiante => $total) {
    echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$total."</td></tr>";
}

?>
</table>

<?php
// Mostrar la lista de enlaces externos publicados de cada estudiante
echo "<br /># ENLLAÇOS EXTERNS PUBLICATS PER CADA USUARI";
echo "<br />________________________________________________<br />";
?>

<table style="font-family:Verdana, Geneva, sans-serif; 	text-align:left;
	padding:4px;
	font-size:11px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000; "
>

<?php
$contador=0;
arsort($enlacesPorEstudiante);
foreach ($enlacesPorEstudiante as $estudiante => $total) {
    echo "<tr><td align='right'>".++$contador." | </td><td>".$estudiante."</td><td>".$total."</td></tr>";
}

?>
</table>


</div> 
 
 
<div id="nubeEtiquetas" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">

<div class="titulo" align="center"><br /><u><strong>NÚVOL D'ETIQUETES AMB LES 75 PARAULES CLAU MÉS FREQÜENTS</strong></u></div>
<br />

<?php
$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

// Creamos el fichero de salida y escribimos la cabecera
$ficheroSalida=fopen("tagcloud.txt","w"); 

// Definimos el array con la lista de palabras
$palabras = array();

$numMensajes=0;

// Definimos los delimitadores
$delimitadores=array(",",":","|","-","&",";","?","¿","*","!","¡",".","/","\\","'","\"");

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);
	   // Comenzamos a leer el cuerpo del mensaje
	   
	   if (!feof($mensaje)) {
		   $linea =  quoted_printable_decode(fgets($mensaje));
		   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			  $linea=str_replace($delimitadores," ",$linea);
			  $lista = explode(" ", utf8_encode($linea)); 
			  foreach ($lista as $palabra) {
			  	$palabra=mb_strtoupper($palabra,'UTF-8');
				if (strlen($palabra)>4) {
					if ($palabras[$palabra]==0) {
					  $palabras[$palabra]=1;
					} else {
					  $palabras[$palabra]++;
					}
				}
			  }
				
			   fputs($ficheroSalida,$linea."\r\n");
			   //$palabras+=$linea;
			   
			   $linea =  quoted_printable_decode(fgets($mensaje));
		   } 
	   }
	   else {
		   echo "<blockquote>No conté cos del missatge</blockquote>";
	   }
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Cerramos el fichero de salida
fclose($ficheroSalida);

// Cerramos el directorio liberando recursos
closedir($directorio);

?>

<a name="nube"> 

<p align="center">
<strong>Canviar tipus de núvol a: Cilíndric <a href="#nube" onclick=" TagCanvas.Start('mycanvas','tags',{weightSize:1.0,shape:'hcylinder'});">100%</a> |
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.75,shape:'hcylinder'});">75%</a> |
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.5,shape:'hcylinder'});">50%</a> |
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.25, shape:'hcylinder'});">25%</a>
 &nbsp;&nbsp;&nbsp; Esfèric <a href="#nube" onclick=" TagCanvas.Start('mycanvas','tags',{weightSize:1.0,shape:'sphere'});">100%</a> | 
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.75,shape:'sphere'});">75%</a> | 
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.5,shape:'sphere'});">50%</a> | 
<a href="#nube" onclick="TagCanvas.Start('mycanvas','tags',{weightSize:0.25, shape:'sphere'});">25%</a>
</strong></p>

<div id="nube" align="center">
 <canvas width="800" height="500" id="mycanvas" style="background-color:#000;">
  <p>Anything in here will be replaced on browsers that support the canvas element</p>
 </canvas>

</div>
<br />
<div id="tags">
<ul>
<?php
arsort($palabras);
if (count($palabras)>75) {
	array_splice($palabras,75,count($palabras)-75);
}
foreach ($palabras as $palabra => $valor) {
  	$palabra=mb_strtolower($palabra,'UTF-8');
	echo "<li><a href=\"#nube\" peso=\"$valor\">".$palabra."</a></li>";
}
?>
</ul>
</div>

</div>

<div id="frecuenciaPalabras" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">
<a name="palabras"> 

<div class="titulo" align="center"><br /><u><strong>LLISTAT AMB LES 75 PARAULES CLAU MÉS FREQÜENTS</strong></u></div>
<br />

<div align="center">
<div class="tablaMetricas" style="width:375px;" align="center">
                <table style="width:375px;">
                    <tr>
                        <td style="width:75px;">
                            Posició
                        </td>
                        <td style="width:200px;">
                            Paraula
                        </td>
                        <td style="width:100px;">
                            Freqüència
                        </td>
                    </tr>
<?php
$contador=0;
foreach ($palabras as $palabra => $valor) {
	echo "<tr><td>".++$contador."</td><td>$palabra</td><td>$valor</td></tr>";
}


?>
</table>

</div>
</div>
</div>

<!-- A CONTINUACIÓN, CREAMOS LOS FICHEROS CON EL FEEDBACK DE CADA USUARIO EN DOS IDIOMAS 
		TAMBIÉN MOSTRAMOS DOS CAPAS CON EL FEEDBACK EN DOS IDIOMAS -->


<div id="feedbackCatalan" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">
<a name="palabras"> 

<div class="titulo" align="center"><br /><u><strong>FEEDBACK PER AL USUARIS EN IDIOMA CATALÀ</strong></u></div>
<div class="titulo" align="center"><br /><u><strong>Descarregar el feedback en català: <a href="feedback_cat_utf8_tab.csv">feedback_cat_utf8_tab.csv</a></strong></u></div>
<br />

<div align="center">
<div class="tablaMetricas" style="width:1000px;" align="center">
                <table style="width:1000px;">
                    <tr>
                        <td style="width:250px;">
                            Usuari
                        </td>
                        <td style="width:750px;">
                            Feedback
                        </td>
                    </tr>
<?php

ksort($mensajesPorEstudiante);
foreach ($mensajesPorEstudiante as $estudiante => $total) {
		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;

echo "<tr><td>".$estudiante."</td><td>";
$linea_cat= "A l'espai de comunicació has realitzat un total de ".$total." aportacions";

if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
	$linea_cat=$linea_cat.", de les quals ".$mensajesRespuestaPorEstudiante[$estudiante]." eren respostes a fils ja oberts";
}

$linea_cat= $linea_cat.". La teva participació es va portar a terme durant un període de ".round($periodo,1)." dies. Has utilitzat una mitjana de ".round($palabrasPorEstudiante[$estudiante]/$total,0)." paraules, ";

if ($enlacesPorEstudiante[$estudiante]==0) {
	$linea_cat= $linea_cat."sense fer servir cap enllaç extern i ";
} else {
	$linea_cat= $linea_cat."fent servir ".$enlacesPorEstudiante[$estudiante]." enllaç/os externs i ";
}

if ($adjuntosPorEstudiante[$estudiante]==0){
	$linea_cat= $linea_cat."cap";
} else {
	$linea_cat= $linea_cat."utilitzant ".$adjuntosPorEstudiante[$estudiante];
}

$linea_cat= $linea_cat." arxiu/s adjunt/s. A més, en base a les respostes rebudes a les teves aportacions, has aconseguit assolir un ";

if (round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)==0) {
	$linea_cat= $linea_cat."0";
} else {
	$linea_cat= $linea_cat.round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2);
}

$linea_cat= $linea_cat."% de popularitat dins de la discussió.\r\n";

echo $linea_cat."</td></tr>";
fputs($ficheroFeedbackCatalan,quoted_printable_decode($estudiante.chr(9).$linea_cat));
}

?>
</table>

</div>
</div>
</div>




<div id="feedbackCastellano" class="cuadroInfo" style="width:100%; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:941px;">
<a name="palabras"> 

<div class="titulo" align="center"><br /><u><strong>FEEDBACK PER AL USUARIS EN IDIOMA CASTELLÀ</strong></u></div>
<div class="titulo" align="center"><br /><u><strong>Descarregar el feedback en castellà: <a href="feedback_cas_utf8_tab.csv">feedback_cas_utf8_tab.csv</a></strong></u></div>
<br />

<div align="center">
<div class="tablaMetricas" style="width:1000px;" align="center">
                <table style="width:1000px;">
                    <tr>
                        <td style="width:250px;">
                            Usuari
                        </td>
                        <td style="width:750px;">
                            Feedback
                        </td>
                    </tr>
<?php

ksort($mensajesPorEstudiante);
foreach ($mensajesPorEstudiante as $estudiante => $total) {
		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;

echo "<tr><td>".$estudiante."</td><td>";
$linea_cas= "En el espacio de comunicación has efectuado un total de ".$total." aportacion/es";

if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
	$linea_cas=$linea_cas. ", de las cuales ".$mensajesRespuestaPorEstudiante[$estudiante]." eran respuestas a hilos ya abiertos";
}

$linea_cas= $linea_cas.". Tu participación se llevó a cabo en un periodo de ".round($periodo,1)." días. Has usado un promedio de ".round($palabrasPorEstudiante[$estudiante]/$total,0)." palabras, ";

if ($enlacesPorEstudiante[$estudiante]==0) {
	$linea_cas= $linea_cas."sin usar";
} else {
	$linea_cas= $linea_cas."usando ".$enlacesPorEstudiante[$estudiante];
}
$linea_cas= $linea_cas." enlace/s externos y ";

if ($adjuntosPorEstudiante[$estudiante]==0){
	$linea_cas= $linea_cas."ningún";
} else {
	$linea_cas= $linea_cas."empleando ".$adjuntosPorEstudiante[$estudiante];
}

$linea_cas= $linea_cas." archivo/s adjunto/s. Además, en base a las respuestas recibidas a tus aportaciones, has conseguido alcanzar un ";

if (round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)==0) {
	$linea_cas= $linea_cas."0";
} else {
	$linea_cas= $linea_cas.round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2);
}

$linea_cas= $linea_cas."% de popularidad dentro de la discusión.\r\n";

echo $linea_cas."</td></tr>";
fputs($ficheroFeedbackCastellano,quoted_printable_decode($estudiante.chr(9).$linea_cas));
}

?>
</table>

</div>
</div>
</div>












<?php









fclose($ficheroFeedbackCastellano);
fclose($ficheroFeedbackCatalan);

?>



<div id="contenedorMenu" style="width:1200px; height:auto; padding:5px; margin:auto; position:absolute; top:900px;">
<div id="menu" > 
<div id="tabs">
<ul>
    <li></li>
    <li><a href="#nulo" onclick="ocultarFicha(false,true,true,true,true,true,true);"><span>Processat...</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,false,true,true,true,true,true);"><span>Indicadors i mètriques individuals</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,true,false,true,true,true,true);"><span>Classificacions per mètriques</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,true,true,false,true,true,true);"><span>Núvol d'etiquetes</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,true,true,true,false,true,true);"><span>Paraules freqüents</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,true,true,true,true,false,true);"><span>Feedback Català</span></a></li>
    <li><a href="#nulo" onclick="ocultarFicha(true,true,true,true,true,true,false);"><span>Feedback Castellà</span></a></li>
  </ul>
</div>
</div>


</div>

</div>

<script language="javascript">

	document.getElementById('procesado').hidden=false;
	document.getElementById('metricasIndividuales').hidden=true;
	document.getElementById('clasificaciones').hidden=true;
	document.getElementById('nubeEtiquetas').hidden=true;
	document.getElementById('frecuenciaPalabras').hidden=true;
	document.getElementById('feedbackCastellano').hidden=true;
	document.getElementById('feedbackCatalan').hidden=true;

</script>

</body>
</html>