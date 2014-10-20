<?php

class Subir {

    private $files, $input, $destino, $nombre, $accion, $maximo, $tipos, $extensiones, $crearCarpeta;
    private $errorPHP, $error;

    const IGNORAR = 0, RENOMBRAR = 1, REEMPLAZAR = 2;
    const ERROR_INPUT = -1;

    function __construct($input) {
        $this->input = $input;
        $this->destino = "subidos/";
        $this->nombre = "";
        $this->accion = Subir::IGNORAR;
        $this->maximo = 2 * 1014 * 1024;
        $this->crearCarpeta = false;
        $this->tipos = array();
        $this->extensiones = array();
        $this->errorPHP = UPLOAD_ERR_OK;
        $this->error = 0;
    }

    function getErrorPHP() {
        return $this->errorPHP;
    }

//

    function getError() {
        return $this->error;
    }

//

    function getErrorMensaje() {
        
    }

    function setCrearCarpeta($crearCarpeta) {
        $this->crearCarpeta = $crearCarpeta;
    }

    function setDestino($destino) {
        $caracter = substr($destino, -1);
        if ($caracter != "/")
            $destino.="/";
        $this->destino = $destino;
    }

//

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

//

    function setAccion($accion) {
        $this->accion = $accion;
    }

//

    function setMaximo($maximo) {
        $this->maximo = $maximo;
    }

//

    function addTipo($tipo) {
        if (is_array($tipo)) {
            $this->tipos = array_merge($this->tipos, $tipo);
        } else {
            $this->tipos[] = $tipo;
        }
    }

//

    function setExtension($extension) {
        if (is_array($extension)) {
            $this->extensiones = $extension;
        } else {
            unset($this->extensiones);
            $this->extensiones[] = $extension;
        }
    }

//

    function addExtension($extension) {
        if (is_array($extension)) {
            $this->extensiones = array_merge($this->extensiones, $extension);
        } else {
            $this->extensiones[] = $extension;
        }
    }

//

    function isInput() {
        if (!isset($_FILES[$this->input])) {
            $this->error = -1;
            return false;
        }
        return true;
    }

//

    private function isError() {
        if ($this->errorPHP != UPLOAD_ERR_OK) {
            return true;
        }
        return false;
    }

//

    private function isTamano() {
        if ($this->files["size"] > $this->maximo) {
            $this->error = -2;
            return false;
        }
        return true;
    }

//

    private function isExtension($extension) {
        if (sizeof($this->extensiones) > 0 && !in_array($extension, $this->extensiones)) {
            $this->error = -3;
            return false;
        }
        return true;
    }

//

    private function isCarpeta() {
        if (!file_exists($this->destino) && !is_dir($this->destino)) {
            $this->error = -4;
            return false;
        }
        return true;
    }

//

    private function crearCarpeta() {
        return mkdir($this->destino, Configuracion::PERMISOS, true);
    }

    private function isTamanoArray($key) {
        if ($this->files["size"][$key] > $this->maximo) {
            $this->error = -2;
            return false;
        }
        return true;
    }

    private function subirArray() {
        foreach ($this->files["name"] as $key => $value) {

            $this->errorPHP = $this->files["error"][$key];
            if (!$this->isError()) {
                if ($this->isTamanoArray($key)) {
                    $partes = pathinfo($this->files["name"][$key]);
                    $extension = $partes['extension'];
                    $nombreOriginal = $partes['filename'];
                    if ($this->isExtension($extension)) {

                        $origen = $this->files["tmp_name"][$key];
                        $destino = $this->destino . $this->nombre . "." . $extension;
                        if ($this->accion == Subir::REEMPLAZAR) {
                            if ($this->nombre === "") {
                                $destino = $this->destino . $nombreOriginal . "." . $extension;
                            } else {
                                $destino = $this->destino . $this->nombre . "." . $extension;
                            }
                            move_uploaded_file($origen, $destino);
                        } elseif ($this->accion == Subir::IGNORAR) {
                            if ($this->nombre === "") {
                                $destino = $this->destino . $nombreOriginal . "." . $extension;
                            } else {
                                $destino = $this->destino . $this->nombre . "." . $extension;
                            }
                            if (file_exists($destino)) {
                                $this->error = -5;
                            }move_uploaded_file($origen, $destino);
                        } elseif ($this->accion == Subir::RENOMBRAR) {
                            if ($this->nombre === "") {
                                $this->nombre="archivo";
                            }
                            $i = 1;
                            $destino = $this->destino . $this->nombre . "." . $extension;
                            while (file_exists($destino)) {
                                $destino = $destino = $this->destino . $this->nombre . "_$i." . $extension;
                                $i++;
                            }
                            move_uploaded_file($origen, $destino);
                        }
                        $this->error = -6;
                    }
                }
            }
        }
    }

    private function subirSolo() {
        $partes = pathinfo($this->files["name"]);
        $extension = $partes['extension'];
        $nombreOriginal = $partes['filename'];
        if (!$this->isExtension($extension)) {
            return false;
        }
        if ($this->nombre === "") {
            $this->nombre = $nombreOriginal;
        }
        $origen = $this->files["tmp_name"];
        $destino = $this->destino . $this->nombre . "." . $extension;
        if ($this->accion == Subir::REEMPLAZAR) {
            return move_uploaded_file($origen, $destino);
        } elseif ($this->accion == Subir::IGNORAR) {
            if (file_exists($destino)) {
                $this->error = -5;
                return false;
            }
            return move_uploaded_file($origen, $destino);
        } elseif ($this->accion == Subir::RENOMBRAR) {
            $i = 1;
            while (file_exists($destino)) {
                $destino = $destino = $this->destino . $this->nombre . "_$i." . $extension;
                $i++;
            }
            return move_uploaded_file($origen, $destino);
        }
        $this->error = -6;
        return false;
    }

    function subir() {
        $this->error = 0;
        if (!$this->isInput()) {
            return false;
        }
        $this->files = $_FILES[$this->input];
        if (!$this->isCarpeta()) {
            if ($this->crearCarpeta) {
                $this->error = 0; //
                if (!$this->crearCarpeta()) {
                    $this->error = -7;
                    return false;
                }
            } else {
                return false;
            }
        }
        if (is_array($this->files)) {
            $this->subirArray();
        } elseif (!is_array($this->files)) {
            $this->subirSolo();
        } else {
            echo "Ha habido algun error subiendo el archivo o no has seleccionado ninguno. <br/>";
        }
    }

}
