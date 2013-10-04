<?php

if(isset($_POST['EnviarMail']))
{
	session_start();
	//error_reporting(E_ALL);
	error_reporting(E_STRICT);
	
	date_default_timezone_set('America/Los Angeles');
	
	require_once('class.phpmailer.php');
	//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
	
	$mail = new PHPMailer();
	
	$Resultado = "";
	$Estatus = $_POST['Estatus'];
	$iUsuario = $_SESSION['iUsuario'];
	$Supervisor = $_SESSION['Nombre']." ".$_SESSION['Apellidos'];
	$Servidor = $_POST['Servidor'];
	$Canales = $_POST['Canales'];
	
	$Usuario = 'lrgomez@firstkontact.com';
	$Password = 'FK2013LG';
	$UsuarioNombre = 'Servicio DAHDI';
	$MailDestino = 'lrgomez@firstkontact.com';
	
	$Query = "";
	$Evento = "";
	
	if($Estatus == "Enviar")
	{
		$Subject = 'Reinicio de servicio DAHDI';
		$Body = "<strong>".$Supervisor.": </strong>reiniciara servicio DAHDI de Servidor <strong>".$Servidor."</strong> en 5 min.<br><br>Canales Activos: <strong>".$Canales."</strong>";
		$Evento = $Supervisor.": Reiniciara servicio DAHDI de Servidor ".$Servidor;
	}
	else 
	{
		$Subject = 'Reinicio de servicio DAHDI - Cancelado';
		$Body = "<strong>".$Supervisor.": </strong>Cancelo reinicio de servicio DAHDI de Servidor <strong>".$Servidor."</strong>";
		$Evento = $Supervisor.": Cancelo Reinicio de servicio DAHDI de Servidor ".$Servidor;
	}
	
	$Query = "INSERT INTO eventos (Evento,iUsuario) VALUES('".$Evento."','".$iUsuario."')";
	$Lector = Ejecuta_Query($Query);
	
	$Body = eregi_replace("[\]",'',$Body);
	
	$mail->IsSMTP(); // telling the class to use SMTP
	//$mail->Host       = "mail.yourdomain.com"; // SMTP server
	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
	                                           // 1 = errors and messages
	                                           // 2 = messages only
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
	$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
	$mail->Username   = $Usuario;  // GMAIL username
	$mail->Password   = $Password;            // GMAIL password
	
	$mail->SetFrom($Usuario, $UsuarioNombre);
	
	//$mail->AddReplyTo("lrgomez@firstkontact.com","Luis Gomez");
	
	$mail->Subject    = $Subject;
	
	$mail->AltBody    = "Para ver el mensaje debe de tener un cliente de correos con HTML activado"; // optional, comment out and test
	
	$mail->MsgHTML($Body);
	
	$mail->AddAddress($MailDestino, "Luis Gomez");
	//$mail->AddAddress("mcuevas@firstKontact.com","Magaly Cuevas");
	//$mail->AddAddress("ihernandez@firstkontact.com", "Ivan Hernandez");
	
	if(!$mail->Send()) 
	{
	  $Resultado.= "Error: " . $mail->ErrorInfo;
	} 
	else 
	{
	  $Resultado = "Enviado!";
	}
	
	echo $Resultado;
}

function Ejecuta_Query ($Query)
{
    $Conexion = mysql_connect("localhost","root","fk2013") or die ("Error de Conexion: ".mysql_error());
    mysql_select_db("canales");
    $result= mysql_query($Query,$Conexion);

    mysql_close($Conexion);
    return $result;
}

?>