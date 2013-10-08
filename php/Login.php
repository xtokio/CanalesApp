<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$Regresa = "";

$Accion = $_GET['Accion'];

switch($Accion)
{
    case "Login":$Regresa = Login();break;
    case "Logout":$Regresa = Logout();break;
}

$ObjetoJSON->message = $Regresa;
echo $_GET['callback']. '('. json_encode($ObjetoJSON) . ')';

// Funcion que nos regresa todos los usuarios
function Login() 
{
    // Se verifica que el usuario exista en la base de datos
    $Query = "SELECT COUNT(iUsuario) AS Total FROM usuarios WHERE Usuario = '" . $_GET['Usuario'] . "'";
    $Lector = Ejecuta_Query($Query);

    $rows = array();

    while ($Registro = mysql_fetch_assoc($Lector)) 
    {
        $rows[] = $Registro['Total'];
    }

    if ($rows[0] > '0') 
    {
        // Se crea el query para leer al usuario de la base de datos
        $Query = "SELECT * FROM usuarios WHERE Usuario = '".$_GET['Usuario']."' AND Password = '" .$_GET['Password']."' LIMIT 1";
        $Lector = Ejecuta_Query($Query);
        $rows = array();

        while ($Registro = mysql_fetch_assoc($Lector)) 
        {
            $rows[] = $Registro;
        }
        if (count($rows) > 0)
        {
            return "success";
        } 
        else 
        {
            return "Password incorrecto";
        }
    } 
    else 
    {
        return "Usuario no existe";
    }
    
}

// Funcion que borra los valores de la sesion
function Logout() 
{
    // Inicializa la sesion
    session_start();

    // Desasigna todas las variables asignadas a la sesion
    $_SESSION = array();
    return "0";
}

// Funcion que regresa query de la base de datos
function Ejecuta_Query ($Query)
{
    $Conexion = mysql_connect("localhost","root","root22") or die ("Error de Conexion: ".mysql_error());
    mysql_select_db("canales");
    $result= mysql_query($Query,$Conexion);

    mysql_close($Conexion);
    return $result;
}
?>