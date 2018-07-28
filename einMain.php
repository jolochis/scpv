<?php
	function setInput($type,$value,$id,$label){
		echo "<tr>";
		if(isset($label))
		echo"
			<td><label for='{$id}'>{$label}</label></td>";
		echo"
		<td><input type='{$type}' 
			required='required' 
			value='{$value}'
			id='{$id}' name='{$id}'>
		</td>
		</tr>";
	}

	function initForm($type,$id){
		echo
		"<div id={$type}>
			<form id={$id} name={$id}>
				<table>
		";
	}

	function endForm(){
		echo
		"</table>
		</form>
		</div>
		";
	}
	function time_init_end(){
		echo"<tr>
			<td><label for='time_init'>Inicio:</td>
			<td><input type='text' id='time_init'></td>
		</tr>";
		echo"<tr>
			<td><label for='time_end'>End:</td>
			<td><input type='text' id='time_end'></td>
		</tr>";
	}

	echo'<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Sistema de Tráfico Urbano</title>
		<link rel="stylesheet" href="estilo/style.css" type="text/css" />
		<link rel="stylesheet" href="estilo/google.css" type="text/css" />
		<link rel="stylesheet" href="estilo/ciudadelasim.css" type="text/css" />
		<script type="text/javascript" src="lib/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="lib/class.Maposm.js"/>
		<script type="text/javascript" src="lib/jquery.msgBox.js"></script>
		<script type="text/javascript" src="lib/OpenLayers-2.13.1/OpenLayers.js"></script>
		<script type="text/javascript" src="lib/class.ConfiguracionObjeto.js"></script>
		<script type="text/javascript" src="lib/ejecuta.js"></script>
		<script type="text/javascript" src="lib/class.Aforos.js"></script>
		<script type="text/javascript" src="lib/class.Carriles.js"></script>
		<link href="styles/msgBoxLight.css" rel="stylesheet" type="text/css"/>
	</head>';
	echo 
	"<div id='objectdata'>
	<fieldset name='data'>
		<legend>Informacion de Objetos</legend>";
		
	//Datos para semáforos
	initForm("data_semaphore","semaphores_form");
	time_init_end();
	setInput("text","","greenTime","Verde");
	setInput("text","","yellowTime","Amarilla");
	setInput("text","","redTime","Roja");
	setInput("button","Guardar","save_semaphore",null);
	endForm();
	
	//Datos para Aforo
	initForm("data_aforo","aforo_form");
	time_init_end();
	setInput("button","Guardar","save_aforo",null);
	endForm();
	
	//Datos para topes
	initForm("data_bump","bump_form");
	setInput("button","Guardar","save_bump",null);
	endForm();
	echo"</fieldset>"
?>
