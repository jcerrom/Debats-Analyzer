<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Learning Analytics - Eina experimental - UOC</title>
</head>

<body>
<?php

function cribar($dir,$fechaInicio,$fechaFin) {

$contador=0;
$directorio = opendir($dir); // Cargamos el directorio
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   echo "* ";
	   $mensaje= fopen("./debates/seleccionado/".$archivos, "r");
	   
	   $linea = fgets($mensaje);
	   // Cogemos la fecha de envío del mensaje
	   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
	   $fecha=strtotime($fecha);

	   if (($fecha<$fechaInicio) || ($fecha>($fechaFin)+86400)){
		   unlink("./debates/seleccionado/".$archivos);
		   $contador++;
	   }
	   fclose($mensaje);

	}
}
echo "<br />";
return $contador;
}



function limpiarDir($dir)
{
foreach(glob("debates/seleccionado/*") as $archivos_carpeta)
{
//si no es un directorio lo eliminamos 
if (!is_dir($archivos_carpeta))
unlink($archivos_carpeta);
} 
return 0;
}

$fichero=$_POST["fichero"];
$fecha1=strtotime(str_replace('/', '-',$_POST["fecha1"]));
$fecha2=strtotime(str_replace('/', '-',$_POST["fecha2"]));

limpiarDir($fichero);

//Creamos un objeto de la clase ZipArchive()
$enzipado = new ZipArchive();
 
//Abrimos el archivo a descomprimir
$enzipado->open("./debates/".$fichero);

echo "Descomprimint: \"".$fichero."\"<br />";

//Extraemos el contenido del archivo dentro de la carpeta especificada
$extraido = $enzipado->extractTo("./debates/seleccionado/");


/* Si el archivo se extrajo correctamente listamos los nombres de los
 * archivos que contenia de lo contrario mostramos un mensaje de error
*/
if($extraido == TRUE){
 for ($x = 0; $x < $enzipado->numFiles; $x++) {
 $archivo = $enzipado->statIndex($x);
 //echo 'Extraido: '.$archivo['name'].'</br>';
 }
 //echo $enzipado->numFiles ." archivos descomprimidos en total";
}
else {
 echo 'Ocurrió un error y el archivo no se pudó descomprimir';
}

echo "Existeixen ".cribar("./debates/seleccionado/",$fecha1,$fecha2)." missatges fora de termini que no es tindran en compte.<br/><br/>";

echo "<a href=\"http://www.paucasals.com/missatgesUOC/sna.php?carpeta=debates/seleccionado&fecha1=".$fecha1."&fecha2=".$fecha2."\">CONTINUAR...</a>";



?>


</body>
</html>