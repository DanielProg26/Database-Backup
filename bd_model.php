<?php

namespace App\Model;

use mysqli;

class BDModel {
    
    private $db;
    private $path = "ruta donde guardaras tus respaldos";
    private $aplication = 'Nombre de tu aplicacion';
    private $description = 'Descripcion de tu base de datos';

    // Conexion
    private $database = 'nombre de tu base de datos';
    private $user = 'user';
    private $host = 'localhost';
    private $pass = '';
    
    public function __CONSTRUCT()
    {
        $this->db = new mysqli($host, $user, $pass, $database);
        
        // Check connection
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        } 
    }

    public function backup($tables){ // Ejemplo 'tabla1, tabla2'

        $date = date("d-m-Y H-i-s");
        $tables= '*';
        $return = "-- MySQL Script generado por $this->aplication \n-- $date \n-- Version: 1.0 \n-- MySQL Admin \n\n";
        $return .= "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;\nSET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\nSET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES'; \n\n\n -- ----------------------------------------------------- \n -- Schema $this->database \n -- \n -- $this->description \n -- \n\n CREATE SCHEMA IF NOT EXISTS `$this->database`;\n USE `$this->database`; \n\n ";

        if($tables == '*'):
            $tables = array();
            $result = $this->db->query('SHOW TABLES');

            if ($result->num_rows > 0):
                while($row = $result->fetch_array()):
                    array_push($tables, $row[0]);
                endwhile;
            endif;
        else:
            $tables = is_array($tables) ? $tables : explode(',',$tables);
        endif;
    
        foreach($tables as $table):
            $result = $this->db->query("SELECT * FROM $table");
            $num_fields = mysqli_num_fields($result);
            $return .= "-- ----------------------------------------------------- \n -- Table `Iuteb_asignaturas`.`$table` \n -- ----------------------------------------------------- \n\n";
            
            $return.="DROP TABLE IF EXISTS $table;";

            $query = $this->db->query("SHOW CREATE TABLE $table");
            $row2 = $query->fetch_row();
            $return.= "\n\n".$row2[1].";\n\n";
            
            for ($i = 0; $i < $num_fields; $i++):
                while($row = $result->fetch_row()):
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j < $num_fields; $j++):
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = preg_replace("~[\n]~","\\n",$row[$j]);
                            if (isset($row[$j])):
                                $return.= '"'.$row[$j].'"';
                            else: 
                                $return.= '""'; 
                            endif;
                            if ($j < ($num_fields-1)): 
                                $return.= ','; 
                            endif;
                        endfor;
                    $return.= ");\n";
                endwhile;
            endfor;
            $return.="\n";
        endforeach;

        $fechaNombre=date("d-m-Y-H-i-s");
        $filename = $this->path."bd-$fechaNombre.sql";

        $handle = fopen($filename,'w+');
        fwrite($handle,$return);
        fclose($handle);

        return array('Ruta'=>$filename);
    }

    // Restaurar la base de datos
    public function restore($filename){ // example.sql

        $texto = file_get_contents($this->path.$filename);
        $sentencia = explode(";", $texto);
        $errors = array();
        for($i = 0; $i < (count($sentencia)-1); $i++):
            $query = $this->db->query("$sentencia[$i];");
            
            if("" !== mysqli_info($this->db)):
                array_push($errors, mysqli_info($this->db));
            endif;
        endfor;

        if(count($errors) > 0): 
            foreach($errors as $error):
                if($error !== null):
                    return $errors;
                endif;
            endforeach;
        endif;

        return array('msg'=> 'Restauracion Exitosa!');

    }

    // Borramos un respaldo
    public function delete($filename){ // example.sql

        if(unlink($this->path.$filename)):
            return array('archivo' => $this->path.$filename, 'Consulta' => $result);
        else:
            return array('msg'=>'No existe el archivo');
        endif;

    }

    // Obtenemos todos los respaldos
    public function getBackups(){
        
        $directorio = opendir($this->path); 
        
        $backups = array();

        while ($archivo = readdir($directorio)):
               
            if ($archivo{0} == '.') continue;
            if ($archivo{0} == '..') continue;
            array_push($backups, $this->path.$archivo);
            
        endwhile;

        return $backups;
    }

}
