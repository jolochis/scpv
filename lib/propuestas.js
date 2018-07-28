propuestas={
	current:"",
	};
	var selectedPropuesta;
	var listPropuestas={};
	var radiouser;

	propuestas.eventOnList = function(){
		$("#listaPropuestas").change(function(){
			selectedPropuesta = $(this).val();
			propuestas.current = {
				p:listPropuestas[selectedPropuesta].id,
				u:listPropuestas[selectedPropuesta].iduser
			};
		});
	};

	propuestas.finishPropuesta=function(){
		newuser.changeActivity(false);
		newuser.changePropuesta(false);
		ciudadelaSim.endPropuesta();
	};

	propuestas.initBehaviorPropuestas = function(){
		
		$("#npropuesta").click(
			function(){
				propuestas.menuNuevaPropuesta(true);
				propuestas.createPropuesta($(this));
			}
		);
		
		$("#mobjeto").click(function(){
				propuestas.disableElement("#mobjeto");
				nuevoObjeto.actualizaObjeto(
				selectedFeature.o,
				selectedFeature.c,
				selectedFeature.t);
			}
		);
		
		$("#eobjeto").click(function(){
			if(selectedFeature.o)
				nuevoObjeto.eliminaObjeto(selectedFeature.o);
			selectedLayer.destroyFeatures(selectedFeature.f);
			selectedFeature = null;
			propuestas.desableElement(".cseleccion");
			propuestas.desableElement(".save");
		});
		
		
		$("#mpropuesta").click(
			function(){
				propuestas.menuCargarPropuesta(false);
				propuestas.menuInicio(false);
				//propuestas.menuEditarPropuesta(true);
				propuestas.menuEditandoPropuesta(true);
				ciudadelaSim.initLayers();}
		);
		
		
		$("#endpropuesta").click(
			function(){
				propuestas.finishPropuesta();
				}
		);
		
		$("#endedicion").click(
			function(){
				propuestas.finishPropuesta();
				}
		);
		
		$("#cseleccion").click(
			function(){
				$("#opcionPropuesta").val(1);
				propuestas.cancelarEdicion();
				}
		);
		
		$("#dialog-propuestas .btn-success").on("click",
			function(){
				radiouser = $("input[name='userradio']:checked").val();
				newuser.changePropuesta(true);
				newuser.changeActivity(true);
				if(propuestas.current.p){
					ciudadelaSim.initLoadPropuesta(
						propuestas.current.p,
						propuestas.current.u);
					propuestas.menuCargarPropuesta(true);
					$("#dialog-propuestas").modal("hide");
				}	
			}
		);
		
		$("#lpropuestas").bind("click",
			function(){
				radiouser = $("input[name='userradio']:checked").val();
				propuestas.getList();
				$("#dialog-propuestas").modal("show");}
		);
		
		this.eventOnList();
		
		$(".userradio").click(function(){
			radiouser = $("input[name='userradio']:checked").val();
			//alert(radiouser);
			propuestas.getList();
		});
		
	};

	propuestas.getList = function(){
		
		$.post("antragen.php",{
			action:"listaPropuestas",other:radiouser},
			function(data){
				if(data.m=="ok"){
					listPropuestas = data.status;}
				else{
					listPropuestas={};
					//alert(data.status);
					}
				propuestas.llenaLista();
			}).
			error(function(){
				alert("Error en el servidor");
			});
	};

	propuestas.llenaLista = function(){
		var option;
		var list=document.getElementById("listaPropuestas");
		var p=list.parentNode;
		p.removeChild(list);
		var parent = $("<select name='listaPropuestas' id='listaPropuestas' style='width:100%'>");
		parent.appendTo(p);
		//$("<option>",{value:0,text:""}).appendTo(parent);
		if(listPropuestas.length>0){
			for(key in listPropuestas){
				option = listPropuestas[key];				
				texto = option.uname+"-" +option.pname+" ("+option.fecha+")";
				parent.append(
					$("<option>",{value:key, text:texto})
				);
			}
		}	
		propuestas.eventOnList();
	};
	/*
	 * item = elemento a ocultar, debe contener '#' si es id o '.' si es clase
	 */
	propuestas.disableElement=function(item){
		$(item).prop("disabled",true);
		$(item).addClass("ui-state-disabled");
	};
	
	propuestas.enableElement=function(id){
		$(id).prop("disabled",false);
		$(id).removeClass("ui-state-disabled");
	};

	/*
	 * @menuscr menu que llama a este menu
	 * >=1 = cargarPropuesta;
	 * <=0 = nuevaPropuesta;
	 */
	propuestas.menuModoMedicion=function(type,menusrc){
		
		if(type){
			$(".benutzer").hide();
			$(".simulacion").show();
			$("#modomedicion").hide();
			$("#endpropuesta").show();
			propuestas.disableElement("#actualizarsim");
		}
		else{
			if(menuscr>0) propuestas.menuEditandoPropuesta(true);
			else propuestas.menuNuevaPropuesta(true);
			$(".simulacion").hidden();
			propuestas.disableElement("#actualizarsim");
		}
	};
	
	
	propuestas.editandoObjeto=function(type){
		propuestas.enableElement(".save");
	};
	
	
	
	propuestas.agregandoObjeto=function(){
	
		propuestas.enableElement("#cseleccion");
		propuestas.enableElement("#endpropuesta");
	
	};
	
	//Muestra le menú para crear nuevevas propuestas
	propuestas.menuNuevaPropuesta=function(type){
		if(type){
			//$(".benutzer").hide();
			$("#opcionPropuesta").show();
			$("#endpropuesta").show();//propuestas.disableElement("#endpropuesta");
			$("#lpropuestas").hide();
			$("#sendsimulacion").show();//propuestas.disableElement("#sendsimulacion");
			$("#opcionPropuesta").show();//propuestas.disableElement("#opcionPropuesta");
			$("#modomedicion").show();//propuestas.disableElement("#modomedicion");
			$("#cseleccion").show(); //propuestas.disableElement("#cseleccion");
			propuestas.disableElement(".save");
			propuestas.disableElement("#mobjeto");
			propuestas.disableElement("#eobjeto");
			propuestas.enableElement("#opcionPropuesta");
			}
		else{
			propuestas.menuInicio(true);
			propuestas.disableElement("#opcionPropuesta");
			propuestas.disableElement("#cseleccion");
			propuestas.disableElement(".save");
			}
	};
	
	
	propuestas.menuNuevaPropuestaEdited=function(){
		propuestas.menuNuevaPropuesta(true);
		propuestas.enableElement("#endpropuesta");
		propuestas.enableElement("#sendsimulacion");
	};
	
	propuestas.menuEditandoPropuesta=function(type){
		propuestas.menuNuevaPropuesta(type);
		if(type){
			$("#endpropuesta").hide();
			$("#endedicion").show();
			$("#optiontraffic").show();
			//$(".configuration").hide();
			propuestas.disableElement("#endpropuesta");
			}
		else{
			propuestas.disableElement("#endpropuesta");
			propuestas.menuCargarPropuesta(true);
			}
	};

	propuestas.menuCargarPropuesta=function(type){
		if(type){
			$(".benutzer").hide();
			$("#lpropuestas").hide();
			$(".modify").show();
			$("#mpropuesta").show();
			$("#endedicion").show();
			$("#sendsimulacion").show();
			$("#opcionPropuesta").show();propuestas.disableElement("#opcionPropuesta");
			$("#modomedicion").show();
			$("#cseleccion").show(); propuestas.disableElement("#cseleccion");
			propuestas.disableElement(".save");
			propuestas.disableElement("#mobjeto");
			propuestas.disableElement("#eobjeto");
			
			if(newuser && newuser.getStatus()){
				propuestas.enableElement("#mpropuesta");
				}
			else{
				propuestas.disableElement("#mpropuesta");
				}
		}
		else{
			$(".benutzer").hide();
			$("#lpropuestas").show();
			$(".modify").show();
			$(".simulacion").show();
			$("#simformulario").hide();
			propuestas.disableElement(".save");
			propuestas.disableElement("#mobjeto");
			propuestas.disableElement("#eobjeto");
			propuestas.disableElement("#mpropuesta");
		}
	};

	
	propuestas.cancelarEdicion=function(){
		propuestas.menuEditandoPropuesta(true);
		if(selectedFeature)
		{
			selectedLayer.destroyFeatures(selectedFeature.f);
			selectedFeature=null;
		}
		if(nuevoObjeto.tipo=="aforo"){
			aforos.finish();
			controlCalles.activate();
			controlCallesClic.deactivate();
		}
	};
	
	propuestas.menuInicio=function(type){
		if(type){
			$(".benutzer").hide();
			$("#lpropuestas").show();
			$("#npropuesta").show();
			if(newuser.getStatus())
				propuestas.enableElement("#npropuesta");
			else
				propuestas.disableElement("#npropuesta");
			}
		else{
			$("#npropuesta").hide();
			propuestas.disableElement("#npropuesta");
			$(".benutzer").hide();
			}
	};


	propuestas.createPropuesta=function(item){
		newuser.changePropuesta(false);
		newuser.changeActivity(true);
		ciudadelaSim.initNewPropuesta($(this));
	};

	
