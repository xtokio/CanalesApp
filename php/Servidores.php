<?php
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
      
    $Regresa = "";
    $Accion = $_POST['Accion'];
    
    switch($Accion)
    {
        case "Servidor":$Regresa = Servidor();break;
        case "Servidor_Estatus":$Regresa = Servidor_Estatus();break;
        case "Canales_IDLE":$Regresa = Canales_IDLE();break;
        case "Reiniciar_Dahdi";$Regresa = Reiniciar_Dahdi();break;
        case "Reiniciar_Canal";$Regresa = Reiniciar_Canal();break;
        case "Rutas":$Regresa = Rutas();break;
        case "Rutas_Listar";$Regresa = Rutas_Listar();break;
        case "Validar_Password";$Regresa = Validar_Password();break;
    }
    echo $Regresa;
    
    function Validar_Password()
    {
        session_start();
        $Password = $_POST['Password'];        
        $iUsuario = $_SESSION['iUsuario'];
        $Total = 0;
        $Query = "SELECT COUNT(iUsuario) AS Total FROM usuarios WHERE iUsuario = '".$iUsuario."' AND Password = '".$Password."' LIMIT 1";
        $Lector = Ejecuta_Query($Query);
        while($Registro = mysql_fetch_assoc($Lector))
        {
            $Total = $Registro['Total'];
        }
        
        return $Total;
    }
    
    function Reiniciar_Dahdi()
    {
        $Estatus = exec("service asterisk stop && service dahdi restart && service asterisk start");
        //$Estatus = exec("touch /tmp/dahdi1 && touch /tmp/dahdi2 && touch /tmp/dahdi3");
        
        return $Estatus;
    }
    
    function Reiniciar_Canal()
    {
        $Canal = $_POST['Canal'];
        $Estatus_Canal = Asterisk_Connect("channel request hangup ".$Canal);
        
        return $Estatus_Canal;
    }
    
    function Servidor_Estatus()
    {
       $Tipo = $_POST['Tipo'];
       $Estatus = "";
       $Class = "";
       $Servicio = "";
       switch($Tipo)
       {
           case "CPU": $Estatus = exec("ps aux | awk {'sum+=$3;print sum'} | tail -n 1");$Class="progress-bar-success";break;
           case "RAM": $Estatus = exec("ps aux | awk {'sum+=$4;print sum'} | tail -n 1");$Class="progress-bar-warning";break;
           case "HDD": $Estatus = exec("df -h|grep -Eo '[0-9]{1,2}%'| awk {'sum+=$1;print sum'}|tail -n 1");$Class="progress-bar-danger";break;
       }
       
       return "<div class=\"progress-bar ".$Class."\" role=\"progressbar\" aria-valuenow=\"".$Estatus."\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ".$Estatus."%\"><span class=\"sr-only\">".$Estatus."% Complete (success)</span></div>";
    }
    
    function Rutas_Listar()
    {
        $Servidor = $_POST['Servidor'];
        $Ruta = "";
        $Query = "SELECT Ruta FROM rutas WHERE Servidor = '".$Servidor."' ORDER BY iRuta DESC";
        $Lector = Ejecuta_Query($Query);
        while($Registro = mysql_fetch_assoc($Lector))
        {
            $Ruta = $Registro['Ruta'];
        }
        
        return $Ruta;
    }
    
    function Rutas()
    {
        session_start();
        $Ruta = $_POST['Ruta'];
        $Servidor = $_POST['Servidor'];
        $iUsuario = $_SESSION['iUsuario'];
        
        //Validar si hago Insert o Update
        $iRuta = 0;
        $Query = "SELECT iRuta FROM rutas WHERE DATE(Fecha) = DATE(NOW()) AND Servidor = '".$Servidor."'";
        $Lector = Ejecuta_Query($Query);
        while($Registro = mysql_fetch_assoc($Lector))
        {
            $iRuta = $Registro['iRuta'];
        }
        if($iRuta > 0)
            $Query = "UPDATE rutas SET Ruta = '".$Ruta."' WHERE iRuta = ".$iRuta;
        else
            $Query = "INSERT INTO rutas(Ruta,Servidor,iUsuario) VALUES('".$Ruta."','".$Servidor."','".$iUsuario."')";
        
        //Validar para no insertar/actualizar en blanco
        if($Ruta != "")
            $Lector = Ejecuta_Query($Query);
        
        return $iRuta;
    }
    
    function Canales_IDLE()
	{
		$Arreglo = Asterisk_Connect("pri show channels");
		$Renglon = 0;
		$Columna = 0;
		$i = 0;
		$IDLE = 0;
        $Activos = 0;
						
		foreach ($Arreglo as $Linea) 
		{
			if($Renglon > 3)
            {                
                $Celdas = explode(" ", $Linea);
                foreach ($Celdas as $Celda) 
                {
                    if($Celda !="")
                    {	
                        switch($i)
                        {
                            case 3:
                            {
                                if($Celda == "Yes")
                                    $IDLE++;
                            }
                            case 4:
                            {
                                $Arreglo = explode(" ",$Celda);
                                if($Arreglo[0] == "Connect")
                                    $Activos++;
                                break;
                            }
                        }
                        $i++;
                    }
                }
                $i = 0;
            }
            $Renglon++;
		}
				
		return $IDLE.":".$Activos;
	}
    
    function Servidor()
	{
		$Arreglo = Asterisk_Connect("pri show channels");
		$Renglon = 0;
		$Columna = 0;
		$i = 0;
		
		$Linea_Actual = "";
		$Celda_Actual = "";
		$Celda_Class = "";
		
		$Array_Linea = array();
		$Array_Celda = array();
		
		foreach ($Arreglo as $Linea) 
		{
			if($Renglon > 3)
            {                
                $Celdas = explode(" ", $Linea);
                foreach ($Celdas as $Celda) 
                {
                    if($Celda !="")
                    {	
                        switch($i)
                        {
                            //case 0:$Celda_Actual.="Canal ".$Celda;break;
                            case 1:$Celda_Actual.="Canal: ".$Celda." Servidor 4";break;
                            //case 2:$Celda_Actual.="Chan2:".$Celda;break;
                            case 3:
                                {
                                    //if($Celda == "Yes")
                                    $Celda_Class="<span class=\"label label-success";
                                    $Celda_Actual.=" Idle: ".$Celda;break;
                                }
                            case 4:
                            {
                                $Celda_Actual.=" Level: ".$Celda." ";
                                
                                if($Celda == "Alerting" || $Celda == "Proceeding")
                                    $Celda_Class="<span class=\"label label-warning";
                                break;
                                //case 5:$Celda_Actual.="Call:".$Celda;break;
                            }
                            case 6:
                            {
                                if($Celda_Class == "<span class=\"label label-success")
                                    $Celda_Class="<span class=\"label label-primary";
                                $Celda_Actual.="<a class=\"Canal\">".$Celda."</a>";break;
                            }
                        }
                        $i++;
                    }
                }
                $i = 0;
                $Linea_Actual.=$Celda_Class."\">".$Celda_Actual."</span><br>";
                $Celda_Actual = "";
                $Celda_Class = "<span class=\"label ";
            }
            $Renglon++;
		}
				
		return $Linea_Actual;
	}
	
	function Asterisk_Connect($Comando)
	{
		/* Detalles de Conexion */
		$manager_host = "127.0.0.1";
		$manager_user = "admin";
		$manager_pass = "fk2012";
		
		/* Puerto Default */
		$manager_port = "5038";
		
		/* Conexion timeout */
		$manager_connection_timeout = 30;
		
		$Arreglo = new ArrayObject;
		
		/* Conexion con el manager */
		$fp = fsockopen($manager_host, $manager_port, $errno, $errstr, $manager_connection_timeout);
		if (!$fp) 
		{
		    echo "Error al conectarse con el manager: $errstr (Error Number: $errno)\n";
		} 
		else 
		{
		    $login = "Action: login\r\n";
		    $login .= "Username: $manager_user\r\n";
		    $login .= "Secret: $manager_pass\r\n";
		    $login .= "Events: Off\r\n";
		    $login .= "\r\n";
		    fwrite($fp,$login);
			
		    $manager_version = fgets($fp);
		
		    $cmd_response = fgets($fp);
		
		    $response = fgets($fp);
		
		    $blank_line = fgets($fp);
			
			if (substr($response,0,9) == "Message: ") 
		    {
		    	 /* Obtuvimos Respuesta */
		        $loginresponse = trim(substr($response,9));
		        if (!$loginresponse == "Authentication Accepted") 
		        {
		            echo "<br>-- No se pudo hacer log in: $loginresponse\n";
		            fclose($fp);
		            exit(0);
		        }
				else 
				{
		            $checkpeer = "Action: Command\r\n";
		            $checkpeer .= "Command: $Comando\r\n";
		            $checkpeer .= "\r\n";
		            fwrite($fp,$checkpeer);
		            $line = trim(fgets($fp));
					
		            while($line != "--END COMMAND--")
		            {
		            	$Arreglo[] = $line;
		            	$line = trim(fgets($fp));
		            }
		           fclose($fp);
				}
			}
			else 
		    {
		        echo "Respuesta inesperada: $response\n";
		        fclose($fp);
		    }		
		}

		return $Arreglo;		
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