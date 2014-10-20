<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        require_once './clases/Subir.php';
        $archivo = new Subir('archivo');
        $archivo->setNombre($_POST["nombre"]);
        if(isset($_POST["accion"])){
            $archivo->setAccion($_POST["accion"]);
        }
        $archivo->subir();
        ?>
    </body>
</html>
