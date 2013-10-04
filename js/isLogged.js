
$.post('php/isLogged.php',function(data){
    if(data == '0')
    {
       $('#Div_Contenido').load('login.html');
    }
    else
    {
        $('#Div_Contenido').load('acumulado.html');
        alert(data);
    }
});