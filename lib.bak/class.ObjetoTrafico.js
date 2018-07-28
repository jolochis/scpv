var p = null;
var direcciones = new Array();
var carriles = new Array();
var chk = 0;
var lineascr;
var lineadst;
var ObjetoTrafico = function(tipo){
	this.id = 0;
	this.tipo = tipo;
	this.linea = 0;
	this.posiciones = new Array();
	this.direccion;
	this.carril;
	
	this.configuracionInicial = null;
	this.configuraciones = new Array();
	this.carrilesA = new Array();
	this.semaforoMarker = null;
	this.topeMarker = null;
	this.pautoMarker = null;
	this.desnivelStyle = OpenLayers.Feature.Vector.style;
	this.desnivelStyle["strokeColor"] = "#a91111";
	this.lineaDesnivel = null;
	this.direccionesAforos = new Array();
	this.aforoStyle = OpenLayers.Feature.Vector.style;
	this.aforoStyle["strokeColor"] = "#000761";	
	
};

ObjetoTrafico.prototype.anadirPosicion = function(lon,lat,linea){
	if(this.tipo == "semaforo"){
		this.posiciones[0] = new OpenLayers.LonLat(lon,lat);
		$("#possem span").html(this.posiciones[0]);
		//this.linea = parseInt(linea);
		if(this.linea <=0){
			this.linea = parseInt(linea);
			lineascr = this.linea;
			}
			this.linea = linea;
			lineadst = this.linea;
		//alert(this.posiciones);
		//alert(this.linea);
		var l = this.linea;
		//alert(l);
		//exLinea();
		this.actualizarSemaforo();
		$(document).ready(function(){ 
			//alert(l);
			$.ajax({ data: "linea="+l+"",
			//action: "obtenerLinea",
			type: "POST", 
			dataType: "json", 
			url: "includes/carriles.php", 
			success: function(data){ 
				   restultss(data); 
				   } 
			}); 
		  }); 
	}
	function restultss(data) {
     $("#carril").html('').show();
     $.each(data,function(index,value) {
       //$("#carriles").append("");
       $("#carril").append(value);
       //$("#carriles").append("</div>");
     });
 }
 	if(this.tipo == "tope"){
		this.posiciones[0] = new OpenLayers.LonLat(lon,lat);
		$("#possem span").html(this.posiciones[0]);
		//this.linea = parseInt(linea);
		if(this.linea <=0){
			this.linea = parseInt(linea);
			lineascr = this.linea;
			}
			this.linea = linea;
			lineadst = this.linea;
		var l = this.linea;
		//alert(l);
		//exLinea();
		this.actualizarTope();
		$(document).ready(function(){ 
			//alert(l);
			$.ajax({ data: "linea="+l+"",
			//action: "obtenerLinea",
			type: "POST", 
			dataType: "json", 
			url: "includes/carriles.php", 
			success: function(data){ 
				   restultst(data); 
				   } 
			}); 
		  }); 
	}
	function restultst(data) {
     $("#carrilt").html('').show();
     $.each(data,function(index,value) {
       //$("#carriles").append("");
       $("#carrilt").append(value);
       //$("#carriles").append("</div>");
     });
 }
 	if(this.tipo == "pauto"){
		this.posiciones[0] = new OpenLayers.LonLat(lon,lat);
		$("#possem span").html(this.posiciones[0]);
		//this.linea = parseInt(linea);
		if(this.linea <=0){
			this.linea = parseInt(linea);
			lineascr = this.linea;
			}
			this.linea = linea;
			lineadst = this.linea;
		var l = this.linea;
		//alert(l);
		//exLinea();
		this.actualizarpAuto();
		$(document).ready(function(){ 
			//alert(l);
			$.ajax({ data: "linea="+l+"",
			//action: "obtenerLinea",
			type: "POST", 
			dataType: "json", 
			url: "includes/carriles.php", 
			success: function(data){ 
				   restultspa(data); 
				   } 
			}); 
		  }); 
	}
	function restultspa(data) {
	 //$("#carrilespa").html('').show();
     $("#carrilpa").html('').show();
     $.each(data,function(index,value) {
       //$("#carriles").append("");
       $("#carrilpa").append(value);
       //$("#carriles").append("</div>");
     });
 }
	if(this.tipo == "aforo"){
		if(typeof this.posiciones[0] != "undefined"){
			this.posiciones[this.posiciones.length-1].push(new OpenLayers.LonLat(lon,lat));
			
				
			if(this.linea <=0){
				this.linea = linea;
				lineascr = this.linea;
			}
			this.linea = linea;
			lineadst = this.linea;
			//alert(this.linea);
			this.actualizarAforo();
			if (p == null){
				p = this.posiciones[this.posiciones.length-1].push(new OpenLayers.LonLat(lon,lat));
				var l = this.linea; 
				$(document).ready(function(){ 
				//alert(l);
				$.ajax({ data: "linea="+l+"",
				//action: "obtenerLinea",
				type: "POST", 
				dataType: "json", 
				url: "includes/carriles.php", 
				success: function(data){ 
				   restultsa(data); 
				   } 
			}); 
		  });
		  }else
		  {
			  linea2=this.linea;
			  //alert(linea2);
		  }

		}
		else{
			alert("No has establecido una ruta.");
		}
			function restultsa(data) {
     $("#carrila").html('').show();
     $.each(data,function(index,value) {
       //$("#carriles").append("");
       $("#carrila").append(value);
       //$("#carriles").append("</div>");
     });
 }
	}
	if(this.tipo == "desnivel"){
		
		this.posiciones[this.posiciones.length] = new OpenLayers.LonLat(lon,lat);
		if(this.posiciones.length == 1){
			//this.linea = linea;
					if(this.linea <=0){
			this.linea = parseInt(linea);
			lineascr = this.linea;
			}
		}
			this.linea = linea;
			lineadst = this.linea;
		this.actualizarDesnivel();
	}
	
	if(this.tipo == "generico"){
		if($("#formgen").val() == "punto"){
			//para punto.
			//this.linea = parseInt(linea);
			if(this.linea <=0){
				this.linea = linea;
				lineascr = this.linea;
				alert(lineascr);
			}
			this.linea = linea;
			lineadst = this.linea;
			this.posiciones[0] = new OpenLayers.LonLat(lon,lat);
		}
		else{
			this.posiciones[this.posiciones.length] = new OpenLayers.LonLat(lon,lat);
			if(this.posiciones.length == 1){
				//this.linea = linea;
				if(this.linea <=0){
				this.linea = linea;
				lineascr = this.linea;
				}
			}			
			this.linea = linea;
			lineadst = this.linea;
		}
	}
};

ObjetoTrafico.prototype.actualizarSemaforo = function(){
	console.log("Añadiendo semaforo en "+this.posiciones[0].lon+","+this.posiciones[0].lat);
	
	if(typeof this.semaforoMarker != "undefined"){ this.semaforoMarker = new OpenLayers.Marker(this.posiciones[0],nuevoSemaforoIcon.clone())
	semaforosLayer.addMarker(this.semaforoMarker);
	}
	//if(typeof this.semaforoMarker != "defined"){ semaforosLayer.addMarker(this.semaforoMarker) }
	
	else{
	    semaforosLayer.removeMarker(this.semaforoMarker) 
	    	//if(this.semaforoMarker == Marker){semaforosLayer.removeMarker(this.semaforoMarker)
		};
		
		//exLinea();
};
ObjetoTrafico.prototype.actualizarTope = function(){
	console.log("Añadiendo tope en "+this.posiciones[0].lon+","+this.posiciones[0].lat);
	
	if(typeof this.topeMarker != "undefined"){ this.topeMarker = new OpenLayers.Marker(this.posiciones[0],nuevoTopeIcon.clone())
	capaTopes.addMarker(this.topeMarker);
	}
	//if(typeof this.semaforoMarker != "defined"){ semaforosLayer.addMarker(this.semaforoMarker) }
	
	else{
	    capaTopes.removeMarker(this.topeMarker); 
	    	//if(this.semaforoMarker == Marker){semaforosLayer.removeMarker(this.semaforoMarker)
		};
		
		//exLinea();
};
ObjetoTrafico.prototype.actualizarpAuto = function(){
	console.log("Añadiendo parada de autobus en "+this.posiciones[0].lon+","+this.posiciones[0].lat);
	
	if(typeof this.pautoMarker != "undefined"){ this.pautoMarker = new OpenLayers.Marker(this.posiciones[0],nuevopAutoIcon.clone())
	capapAuto.addMarker(this.pautoMarker);
	}
	//if(typeof this.semaforoMarker != "defined"){ semaforosLayer.addMarker(this.semaforoMarker) }
	
	else{
	    capapAuto.removeMarker(this.pautoMarker); 
	    	//if(this.semaforoMarker == Marker){semaforosLayer.removeMarker(this.semaforoMarker)
		};
		
		//exLinea();
};

ObjetoTrafico.prototype.actualizarAforo = function(){
	console.log("Se puso el punto "+this.posiciones[this.posiciones.length-1][this.posiciones[this.posiciones.length-1].length-1].lon+","+this.posiciones[this.posiciones.length-1][this.posiciones[this.posiciones.length-1].length-1].lat+" en la ruta "+this.posiciones.length);
	
				//var al = this.linea;
				//alert(al);
				
	if(this.posiciones[this.posiciones.length-1].length >= 2){
		//dibuja el segmento.
		if(typeof this.direccionesAforos[this.posiciones.length-1] != "undefined") capaAforos.removeFeatures([this.direccionesAforos[this.posiciones.length-1]]);
		this.direccionesAforos[this.posiciones.length-1] = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.LineString(this.posicionesToPoint(this.posiciones[this.posiciones.length-1])),
				{},
				this.aforoStyle
		);
		
		capaAforos.addFeatures([this.direccionesAforos[this.posiciones.length-1]]);
	}
};

ObjetoTrafico.prototype.actualizarDesnivel = function(){
	console.log("Poniendo un punto de desnivel en: "+this.posiciones[this.posiciones.length-1].lon+","+this.posiciones[this.posiciones.length-1].lat)
	if(this.posiciones.length >= 2){
		//dibuja el segmento.
		//if(typeof this.lineaDesnivel != null) capaDesniveles.removeFeatures([this.lineaDesnivel]);
		this.lineaDesnivel = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.LineString(this.posicionesToPoint(this.posiciones)),
				{},
				this.desnivelStyle
		);
		
		capaDesniveles.addFeatures([this.lineaDesnivel]);
	}
};

ObjetoTrafico.prototype.posicionesToPoint = function(posiciones){
	var puntos=new Array();
	for(var i in posiciones){
		puntos[i] = new OpenLayers.Geometry.Point(posiciones[i].lon,posiciones[i].lat);
	}
	return puntos;
};

ObjetoTrafico.prototype.agregarDireccionAforo = function(x){
	var trazar = false;
	
	if(this.posiciones.length > 0){
		if(this.posiciones[this.posiciones.length-1].length >=2){
			this.posiciones[this.posiciones.length] = [ this.posiciones[this.posiciones.length-1][0] ];
			trazar = true;
		}
		else{
			alert("No has trazado la dirección actual del aforo.");
		}
	}
	else{
		this.posiciones[0] = new Array();
		trazar = true;
	}
	
	if(trazar){
		//$(x).before('<p>Linea '+(this.posiciones.length)+': Porcentaje del flujo total: <input size="8" type="text" name="fseg_'+this.posiciones.length+'" id="fseg_'+this.posiciones.length+'" /></p>');
		console.log("Se empezará a trazar la ruta "+this.posiciones.length);
	}
};

ObjetoTrafico.prototype.limpiarCapas = function(){
	capaAforos.removeAllFeatures();
	capaDesniveles.removeAllFeatures();
	//semaforosLayer.removeAllFeatures();
	//capaTopes.removeAllFeatures();
	//capapAuto.removeAllFeatures();
	semaforosLayer.markers = new Array();
	capaTopes.markers = new Array();
	capapAuto.markers = new Array();
}

ObjetoTrafico.prototype.setLinea = function(linea){
	this.linea = parseInt(linea);
		
}

ObjetoTrafico.prototype.getPosiciones = function(){
	if(this.tipo == "semaforo"){
		return "POINT("+this.posiciones[0].lon+" "+this.posiciones[0].lat+")"
	}
	if(this.tipo == "tope"){
		return "POINT("+this.posiciones[0].lon+" "+this.posiciones[0].lat+")"
	}
	if(this.tipo == "pauto"){
		return "POINT("+this.posiciones[0].lon+" "+this.posiciones[0].lat+")"
	}
	if(this.tipo == "desnivel"){
		var linea = new Array();
		for(var i=0;i<this.posiciones.length;i++){
			linea[i] = this.posiciones[i].lon+" "+this.posiciones[i].lat;
		}
		return "LINESTRING("+linea.join(",")+")";
	}
	if(this.tipo == "aforo"){
		var lineas = new Array();
		for(var x = 0;x < this.posiciones.length;x++){
			var linea = new Array();
			for(var y = 0;y < this.posiciones[x].length; y++){
				linea[y] = this.posiciones[x][y].lon+" "+this.posiciones[x][y].lat;
			}
			lineas[x] = "("+linea.join(",")+")"; 
		}
		return "MULTILINESTRING("+lineas.join(",")+")";
	}
	else{
		if($("#formgen").val() == "punto"){
			return "POINT("+this.posiciones[0].lon+" "+this.posiciones[0].lat+")"
		}
		else{
			var linea = new Array();
			for(var i=0;i<this.posiciones.length;i++){
				linea[i] = this.posiciones[i].lon+" "+this.posiciones[i].lat;
			}
			return "LINESTRING("+linea.join(",")+")";
		}
	}
}

ObjetoTrafico.prototype.guardarObjeto = function(){
	
    //contar();
	if(this.tipo == "semaforo"){
		/*ConfiguracionObjeto(estado,tiempo_inicial,tiempo_final,esInicial);*/
		this.configuracionInicial = new ConfiguracionObjeto($("#luzini").val(),$("#tinsem").val(),$("#tfinsem").val(),1);
		
		this.configuraciones = [
		                        new ConfiguracionObjeto("rojo",0,$("#lrd").val(),0),
		                        new ConfiguracionObjeto("amarillo",0,$("#lad").val(),0),
		                        new ConfiguracionObjeto("verde",0,$("#lvd").val(),0)
		                        ];
	}
		if(this.tipo == "tope"){
		this.configuracionInicial = new ConfiguracionObjeto("",0,0,0);
		//this.configuracionInicial = new ConfiguracionObjeto($("#luzini").val(),$("#tinsem").val(),$("#tfinsem").val(),1);
	}
		if(this.tipo == "pauto"){
			this.configuracionInicial = new ConfiguracionObjeto("",0,0,0);
		//this.configuracionInicial = new ConfiguracionObjeto($("#luzini").val(),$("#tinsem").val(),$("#tfinsem").val(),1);	
	}
	if(this.tipo == "desnivel"){
		this.configuracionInicial = new ConfiguracionObjeto($("#sendes").val(),0,0,1);
		this.configuraciones = [
		                        new ConfiguracionObjeto($("#tipdes").val(),0,0,0),
		                        new ConfiguracionObjeto($("#ncdes").val(),0,0,0)
		                        ];
	}
	if(this.tipo == "aforo"){
		this.configuracionInicial = new ConfiguracionObjeto($("#vehsalaf").val(),$("#tinaf").val(),$("#tfiaf").val(),1);
		for(var x = 0; x < this.posiciones.length; x++){
			this.configuraciones[x] = new ConfiguracionObjeto($("#fseg_"+(x+1)).val(),$("#vehsalaf").val(),$("#cvt").val(),0);
		}
	}
	
	if(this.tipo == "generico"){
		this.configuracionInicial = new ConfiguracionObjeto("",0,0,0); //no manda ninguna configuracion inicial, todas estan en configuraciones
		for(var x = 0; x < this.configuraciones.length; x++){
			this.configuraciones[x] = new ConfiguracionObjeto($("#estobj_"+(x+1)).val(),$("#hrin_"+(x+1)).val(),$("#hrfin_"+(x+1)).val(),$("#esInicial_"+(x+1)).attr('checked')? 1 : 0);
		}
	}
	contar();
	this.guardarDatos();
}

ObjetoTrafico.prototype.nuevaConfiguracion = function(o){
	this.configuraciones[this.configuraciones.length] = null;
	$(o).before('<div><p>Configuracion '+this.configuraciones.length+' del objeto.</p><p>Estado: <input type="text" name="estobj_'+this.configuraciones.length+'" id="estobj_'+this.configuraciones.length+'" /></p><p>Hora inicio: <input type="text" name="hrin_'+this.configuraciones.length+'" id="hrin_'+this.configuraciones.length+'" /></p><p>Hora final: <input type="text" name="hrfin_'+this.configuraciones.length+'" id="hrfin_'+this.configuraciones.length+'" /></p><p>Es Configuracion Padre?: <input type="checkbox" value="1" id="esInicial_'+this.configuraciones.length+'" name="esInicial_'+this.configuraciones.length+'" /></p> </div>');
}

ObjetoTrafico.prototype.guardarDatos = function(){
	
	var confs = new Array();
	
	for(var x = 0;x<this.configuraciones.length;x++){
		confs[x] = this.configuraciones[x].getVars();
	}
	
	if(this.tipo == "generico"){
		this.tipo = $("#tiob").val();
	}
	var enviarServidor = {
			"action" : "guardarObjeto",
			"objeto" : {
				"tipo" : this.tipo,
				"lineascr" : lineascr,							
				"lineadst" : lineadst,
				"posiciones" : this.getPosiciones()
			},
			"configuracionInicial" : this.configuracionInicial.getVars(),
			"configuraciones" : confs,
			"carril" : carriles,
			"direccion" : direcciones,
			"checked" : chk
	};
	
	$.post("peticiones.php",enviarServidor,function(d){
		$("#prueba").html(d)
	},"text");
	eliminarchk();
	limpiartxt();
}


function contar(){
				//alert(document.userform.seleccion.checked);
				var elementos = document.getElementsByName("seleccion");	
				//alert("Hay " + elementos.length + " elementos con el nombre 'opción1'");	
				//texto = "";
				for (x=0;x<elementos.length;x++){
					if (document.getElementById("carril"+x).checked == true) {					
						if (document.getElementById("carril"+x).value == "Sentido normal."){
							carril=document.getElementById("carril"+x).id;
							direccion="onWay";
							direcciones[chk] = direccion;
							carriles[chk] = carril;
						}
						else{
							carril=document.getElementById("carril"+x).id;
							direccion="reverseWay";
							direcciones[chk] = direccion;
							carriles[chk] = carril;
						}
						chk++;
					}
				/*texto =  texto + document.getElementById("carril"+x).value + "-- " + document.getElementById("carril"+x).checked + "-- " + document.getElementById("carril"+x).id +"\n";    
				alert("Se han encontrado los siguientes valores en elementos 'opcion1'\n" + texto);*/
			
		}
}

function eliminarchk(){
				//alert(document.userform.seleccion.checked);
				var elementos = document.getElementsByName("seleccion");	
				//alert("Hay " + elementos.length + " elementos con el nombre 'opción1'");	
				//texto = ""; 
				if (elementos.length != 0) {
				for (x=0;x<=elementos.length+1;x++){
					idcarril= "carril"+x;
					document.getElementById("carril"+x).checked = false;
					/*texto =  texto + document.getElementById("carril"+x).value + "-- " + document.getElementById("carril"+x).checked + "-- " + document.getElementById("carril"+x).id +"\n";    
					alert("Se han encontrado los siguientes valores en elementos 'opcion1'\n" + texto);
					alert(idcarril);*/
					$("#"+idcarril).remove();
					direcciones = new Array();
					carriles = new Array();
					chk = 0;
					//if (document.getElementById("carril"+x).checked == true) {					
						
					}
					$(".ocarril").hide();
				}

			}
function limpiartxt(){
	$(":text").val('');
}

