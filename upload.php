<?php
//solo se puede acceder si es una peticion post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    //llamamos a la clase multiupload
    require_once("multiupload.php");
    //array de campos file del formulario
    $files = $_FILES['userfile']['name'];
    //creamos una nueva instancia de la clase multiupload
    $upload = new Multiupload();
    //llamamos a la funcion upFiles y le pasamos el array de campos file del formulario
    $isUpload = $upload->upFiles($files);
    
}else{
    throw new Exception("Error Processing Request", 1);
}


?>

<script language="javascript">
window.open("http://www.paucasals.com/missatgesUOC","_self")
</script>
