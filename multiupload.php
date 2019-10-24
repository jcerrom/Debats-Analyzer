<?php
class Multiupload
{
 
    /**
    *sube archivos al servidor a través de un formulario
    *@access public
    *@param array $files estructura de array con todos los archivos a subir
    */
    public function upFiles($files = array())
    {
        //inicializamos un contador para recorrer los archivos
        $i = 0;
 
        //recorremos los input files del formulario
        foreach($files as $file) 
        {
            //si se está subiendo algún archivo en ese indice
            if($_FILES['userfile']['tmp_name'][$i])
            {
                    //comprobamos si el archivo ha subido
                    if(move_uploaded_file($_FILES['userfile']['tmp_name'][$i],"debates/".$_FILES['userfile']['name'][$i]))
                    {
                        //echo "subida correctamente";
                        //aqui podemos procesar info de la bd referente a este archivo
                    } 
                
            //si ese input file no ha sido cargado con un archivo
            }else{
                //echo "sin fichero";
            }
            //echo "<br />";
            //en cada pasada por el loop incrementamos i para acceder al siguiente archivo
            $i++;     
        }   
    }
 
 
}
?>