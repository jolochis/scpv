var validFields = new Array();

function initBehavior(){
	benutzerDialog();
		
	$("#anmeldung").bind("click",
		function(){
			hidMesage("loginerror");
			$("#dialog-anmeldung").modal("show");			
		}
	);
	
	$("#register").bind("click",
		function(){
			hidMesage("nuevoerror");
			$("#dialog-register").modal("show");			
		}
	);
}

function showMesage(id,mensaje){
	$("#"+id).empty();
	$("#"+id).html(mensaje);
};

function hidMesage(id){
	$("#"+id).empty();	
};


//funciones de los modales de registro y de login
function benutzerDialog(){
	$("#dialog-anmeldung .btn-success").on("click",
		function(){
			identificar($(this));			
		}
	);
	
	$("#dialog-register .btn-success").on("click",
		function(){
			registrar($(this));			
		}
	);
}

function validar(data){
	var valid=true;
	for( key in data)
	{
		if(data[key].length<1){
			$("#"+key).addClass("ui-state-error");
			this.validFields[key]=data[key];
			valid=false;
			break;
		}
		else
			$("#"+key).removeClass("ui-state-error");
	}
	return valid;
}

function validaDatoNumerico(data){
	var regex = /^[0-9]+$/;
	var vl = regex.test(data);
	return vl;
}

function validateMail(email){
	var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	var vl =regex.test(email);
	return 1;
}


function identificar(form){
	var data ={};
	valid = false;
	data[$("#uname").attr("name")]=($.trim($("#uname").val()));
	data[$("#pass").attr("name")]=($.trim($("#pass").val()));
	
	if(this.validar(data)){
		data["action"]="userLogin";
		$.post("antragen.php",data,
			function(data){
				if(data.m=="error"){
					showMesage("loginerror",data.status);
					propuestas.menuInicio(false);
					valid=false;
					$("#identificado").html("Sin Identificar");
				}
				else if(data.m=="ok"){
					valid=true;
					newuser.changeStatus(true);
					propuestas.menuInicio(true);
					$("#identificado").html("BIENVENIDO "+data.name);
					$("#dialog-anmeldung").modal("hide");
				}
				
			},"json").
			error(function(data){
				top.alert("Error al Logear Usuario"+data.responseText);
				$(".benutzer").hide();
				//document.forms["login"].reset();
			});
	}
	else{
		valid=false;		
		showMesage("loginerror","Faltan datos");
	}
	form.context.form.reset();
	return valid;
};

function clear(){
	document.getElementById("nuevo").reset();
	document.getElementById("login").reset();
};


function registrar(form){
	var data = {};
	var valid = false;
	data[$("#nuname").attr("name")]=$.trim($("#nuname").val());
	data[$("#nemail").attr("name")]=$.trim($("#nemail").val());
	data[$("#npass").attr("name")]=$.trim($("#npass").val());
	
	if(this.validar(data)){
		data["action"]="userRegister";
		if(this.validateMail(data["#nemail"])){
			$.post("antragen.php",data,
				function(d){
					if(d.m=="ok"){
						showMesage("nuevobien",d.status);
						showMesage("nuevoerror","");
						$("#dialog-anmeldung").modal("hide");
					}
					else{
						showMesage("nuevoerror",d.status);
						showMesage("nuevobien","");
					}
				},"json").
			error(function(data){
				alert("Error del servidor Registro"+data.responseText);
				console.log(data.responseText);
			});
		}
		else{
			showMesage("nuevoerror","Email Invalido");			
		}
	}
	else{
		showMesage("nuevoerror","Faltan Datos");
	}
	
	form.context.form.reset();
	return valid;
};

function revidieren(parent){
	var valid = new Array();
	parent.find("input.data").each(
		function(){
			if(($.trim($(this).val())).length<1){
				$(this).addClass("ui-state-error");
				valid["data"] = "Completa los datos";
			}
			else if(!validaDatoNumerico($(this).val())){
				$(this).addClass("errorred");
				valid["format"] = "Formato de informacion incorrecto";
			}
			else
				$(this).removeClass("ui-state-error");
		});
		
	if(Object.keys(valid).length>0){
		var text="";
		for(key in valid){
			text +=valid[key]+"\n";
		}
		alert(text);
		valid=false;
	}

	return valid;
}

function clearForm(form){
	$(form).reset();
}
