simulaciones = {
	idp:0,
	idu:0,
	medicion:""
	};

simulaciones.initBehavior=function(){
	simulaciones.simulacionesDialog();
	
	
	$("#dialog-simulacion .btn-success").on("click",
		function(){
			init = document.getElementById("simconfiginit").value;
			end = document.getElementById("simconfigend").value;
			
			$.post("antragen.php",{action:"sendSimulation",
				ti:init,tf:end,pj:900913},
				function(data){
					if(data.m=="error"){						
					}
					else{
					}
				}
			);
			$("#dialog-simulacion").modal("hide");
		}
	);
	
	$("#sendsimulacion").click(
		function(){
			//verificar si hay objetos
			$("#dialog-simulacion").modal("show");
			}
	);
	
	$("#actualizarsim").click(
		function(){
			simulaciones.getDataSimulator(
				document.getElementById("namesegment").value);
			}
	);
	
	$("#modomedicion").click(
		function(){
			simulaciones.getSimulation();
		});
};


simulaciones.getSimulation=function(){
	var mensaje="";
	$.post("antragen.php",
		{action:"getSimulationFile",pj:"900913"},
		function(data){
			if(data.m!="error"){
				propuestas.menuModoMedicion(true);
				ciudadelaSim.addSimulation(data);
				/*$.post("peticiones.php",{action:"getFeatures",features:data},
					function(data){
						ciudadelaSim.addSimulation(data);
						
				},"json");*/
			}
			else 
				mensaje = "Archivo Simulacion No existe";
		$("#mbox").html(mensaje);
		},"json");
};


simulaciones.getDataSimulator=function(segment,distance){
	init = document.getElementById("siminit").value ? 
		document.getElementById("siminit").value : "0";
	end = document.getElementById("simend").value ?
		document.getElementById("simend").value:10;
	
	data  = {action:"getInfo",ti:init,tf:end,s:segment,d:distance};
			
/*	$("#namesegment").val(segment);
 * 		if(segment=="S4"){
				
					$("#flujo").val(5);
					$("#densidad").val(3);
				
			}
			else if(segment=="S6"){
				
					$("#flujo").val(4);
					$("#densidad").val(0);
				
			}
			else if(segment=="S3"){
				
					$("#flujo").val(10);
					$("#densidad").val(5);
				
			}*/
	
	$.post("antragen.php",data,
		function(data){
				$("#namesegment").val(segment);
				$("#flujo").val(data.flujo);
				$("#densidad").val(data.densidad);
				propuestas.enableElement("#actualizarsim");
			}
		);
	
};

simulaciones.sendSimulacion=function(){
	$.post("peticiones.php",
		{action:"consultaSimulacion",
		miny:box[0],minx:box[1],
		maxy:box[2],maxx:box[3],
		u:simulaciones.idu,p:simulaciones.idp,
		pj:"900913"},
		function(data){
			$.post("peticiones.php",{action:"getFeatures",json:data},
			function(data){
				addSimulation(data);
				
				},"json");
			/*for(key in segment){
				//alert(segment[key]['@attributes']['type']);
				if(segment[key]['@attributes']['type']=="Segment")
				{
					
					feature.features={
						"type":"Feature",
						"geometry":"LineString("+
							segment[key]['@attributes']['points']+")",
						"properties":{
							"name":segment[key]['@attributes']['id'],
							"demsity":100}
						};
				}
			}*/
			
			},"json");
};


simulaciones.simulacionesDialog=function(){
	/*$("#dialog-simulacion").dialog({
		autoOpen:false,
		modal:true,
		resizable:false,
		height:$("#dialog-simulacion").attr("height"),
		width:$("#dialog-simulacion").attr("width"),
		title:"Solicitud Simulacion",
		closeText:"X",
		buttons:{
			"Aceptar":function(){
				init = document.getElementById("simconfiginit").value;
				end = document.getElementById("simconfigend").value;
				
				$.post("antragen.php",{action:"sendSimulation",
					ti:init,tf:end,pj:900913},
					function(data){
						if(data.m=="error"){
							}
						else{
							
							}
							
						}
					);
			},
			Cancel:function(){
				$(this).dialog("close");
				}
			},
		close:function(){
			},
		});*/
};
