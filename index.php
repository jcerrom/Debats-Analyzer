<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Learning Analytics - Eina experimental - UOC - versió 1.1</title>
<link rel="stylesheet" type="text/css" href="estilos.css" />


</head>

<body>

<div id="contenedor">

<img src="img/cabecera.png" width="800" height="100" alt="" align="center"/>
<div class="titulo" align="center"><strong>- Learning Analytics -</strong></div>
<div class="titulo" align="center"><strong>Eina experimental per a l'anàlisi de la interacció comunicativa</strong></div>
<p align="center">Investigadors/es: Montse Guitert, Teresa Romeu i Juan Pedro Cerro</p>
<br />


<div class="cuadroInfo" id="eleccionCarpeta" style="width:800px; height:auto; padding:5px; background:#BBB; border: 1px solid black; position:absolute; top:180px; 	font-size:12px;
	font-family:Verdana;
	font-weight:normal;
	color:#000000;
">

<div class="titulo" align="center"><u><strong>AFEGIR UN NOU ESPAI DE CONVERSA PER AL SEU ANÀLISI</strong></u></div>
<br />
    <form action="upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
         <label>* Selecciona el fitxer comprimit amb els missatges:</label>
        <input type="file" name="userfile[]" /><br /><br />
        <div align="center"><input type="submit" value="Carregar al servidor" /> </div>
    </form>
<br />

</div>


<div id="listadoCarpetas" class="cuadroInfo" style="width:800px; height:auto; padding:5px; background:#6CC; border: 1px solid black; position:absolute; top:330px;">

<div class="titulo" align="center"><u><strong>CONVERSES CARREGADES AL SERVIDOR</strong></u></div><br />

<form action="descomprimir.php" method="post" enctype="application/x-www-form-urlencoded" name="debates">
<p style="margin:0px 0px 10px 10px;">1.- Indica el període de temps a analitzar: de <input type="texbox" name="fecha1" placeholder="dd/mm/aaaa" required /> a <input type="textbox" name="fecha2" placeholder="dd/mm/aaaa" required /></p>
<p style="margin:0px 0px 10px 10px;">2.- Selecciona la conversa a analitzar (.ZIP) i prem el botó: 
<input type="submit" value="ANALITZAR CONVERSA" required />
</p>

                <table style="margin:0px 0px 10px 20px;">

<?php 

$directorio = opendir("./debates/"); // Cargamos el directorio

// VAMOS A INSPECCIONAR LOS DIRECTORIOS CON LOS DEBATES CARGADOS

while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)  && (strtolower(end(explode(".",$archivos)))=="zip")) //Verificamos si es o no un directorio y un ZIP
    {
		echo "<tr><td style=\"width:300px;\"> <input style=\"vertical-align:middle; margin:4px 4px 4px 4px;\" type=\"radio\" name=\"fichero\" value=\"".$archivos."\" required> ".$archivos."</td><td style=\"width:100px;\"><a href=\"eliminar_fichero.php?fichero=".$archivos."\">- Esborrar -</a></td></tr>";
	}
}
?>

</table>
</form>

</div>


</div>

</body>
</html>