<?php
header("content-type: text/html");
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$Logeado = IsLoggedIn();

echo $Logeado;

// Funcion que verifica si ya hay sesion de usuario.
function IsLoggedIn() 
{
    $Regresa = "";
    session_start();
    
    if (!isset($_SESSION['Usuario']) || $_SESSION['Usuario'] = '') 
    {
        $Regresa = '0';
    } 
    else 
    {
        $Regresa = $_SESSION['Nombre']." ".$_SESSION['Apellidos'];
    }
    
    return $Regresa;
}