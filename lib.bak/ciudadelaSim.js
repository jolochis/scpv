var controlCalles;
var controlCallesClic;
var lineaAforo;
var numPoint =0;
var handler;

function obtenerEventos(){
	if(hayMasEventos){
		haciendoPeticion = true;
		$.post("peticiones.php","action=getResultadosSimulador&inicio="+eventosTotalSolic+"&numero="+rangoResultados,function(r){
			if(r.errorMsj == ""){
				if(r.resultados.length > 0){
					if(eventosTotalSolic == 0){
						eventos = r.resultados;
					}
					else{
						eventosTemp = r.resultados;
					}
					
					eventosTotalSolic += r.resultados.length;
					haciendoPeticion=false;
				}
				else{
					hayMasEventos = false;
				}
			}
			else{
				alert(r.errorMsj);
			}
		},"json");
	}
}


function desactivaClic(){
	controlCallesClic.deactivate();
}

function activaClic(){
	controlCallesClic.activate();
}

function ejecutarInterfazGrafica(){
	var prosigue = true;
	
	/*if(!haciendoPeticion && eventosTemp.length == 0){
		obtenerEventos();
	}
	
	if(!(eventosEjec < eventos.length)){
		if(hayMasEventos){
			eventos = eventosTemp;
			eventosEjec = 0;
			eventosTemp = new Array();
		}
		else{
			//ejecutamos las ultimas acciones
			//que se vallan a querer realizar.
			//hay que definirlo.
			console.log("Error en el sistema: no se detectó el inicio del semaforo.");
			prosigue = false;
		}
	}
	
	
	if(prosigue && typeof eventos[eventosEjec] != "undefined"){
		if(eventos[eventosEjec].tipoObjeto == VEHICULO){
			if(typeof vehiculos[eventos[eventosEjec].idObjeto] != "undefined"){
				vehiculos[eventos[eventosEjec].idObjeto].lonlat = OSMLatLong(eventos[eventosEjec].longObj,eventos[eventosEjec].latObj);
			}
			else{
				vehiculos[eventos[eventosEjec].idObjeto] = new OpenLayers.Marker(OSMLatLong(eventos[eventosEjec].longObj,eventos[eventosEjec].latObj),vehiculoIcon.clone());
				//console.log("Se ha creado el auto: "+eventos[eventosEjec].idObjeto)
			}
			vehiculosLayer.addMarker(vehiculos[eventos[eventosEjec].idObjeto]);
		}
		else if(eventos[eventosEjec].tipoObjeto == SEMAFORO){
			if(typeof semaforos[eventos[eventosEjec].idObjeto] != "undefined"){
				semaforosLayer.removeMarker(semaforos[eventos[eventosEjec].idObjeto]);
				if(semaforos[eventos[eventosEjec].idObjeto].icon.url == SEMAFORO_VERDE){
					semaforos[eventos[eventosEjec].idObjeto].icon = semaforoAmbarIcon.clone();
				}
				else if(semaforos[eventos[eventosEjec].idObjeto].icon.url == SEMAFORO_AMBAR){
					semaforos[eventos[eventosEjec].idObjeto].icon = semaforoRojoIcon.clone();
				}
				else if(semaforos[eventos[eventosEjec].idObjeto].icon.url == SEMAFORO_ROJO){
					semaforos[eventos[eventosEjec].idObjeto].icon = semaforoVerdeIcon.clone();
				}
				else{
					console.log("Error en el sistema: no se detectó el inicio del semaforo."+semaforos[eventos[eventosEjec].idObjeto].icon.url);
				}
			}
			else{
				if(eventos[eventosEjec].idObjeto == 1){
					semaforos[eventos[eventosEjec].idObjeto] = new OpenLayers.Marker(OSMLatLong(eventos[eventosEjec].longObj,eventos[eventosEjec].latObj),semaforoRojoIcon.clone());
				}
				else{
					semaforos[eventos[eventosEjec].idObjeto] = new OpenLayers.Marker(OSMLatLong(eventos[eventosEjec].longObj,eventos[eventosEjec].latObj),semaforoVerdeIcon.clone());
				}
				//console.log("Se ha creado el semaforo: "+eventos[eventosEjec].idObjeto)
			}
			semaforosLayer.addMarker(semaforos[eventos[eventosEjec].idObjeto]);
		}
		else{
			console.log("Error en el Sistema: No fué detectado el tipo de Objeto.");
		}
		document.getElementById("prueba").innerHTML = "Semaforos: "+semaforosLayer.markers.length+" - Vehiculos: "+vehiculosLayer.markers.length+" - Eventos solicitados: "+eventosTotalSolic+" - Evento: "+eventosEjec+" Tiempo: "+eventos[eventosEjec].tiempo;
		eventosEjec++;
	}*/
}

function verificaClic(tipoLinea,lineas,coorx,coory){

}


function activarInteraccionUsuario(c){

	var uselectStyle = new OpenLayers.Style({
		'cursor' : 'pointer',
		'strokeOpacity' : 0.5,
		'strokeColor' : "BLUE",
		'strokeWidth' : 10
	});

	var selectStyle = new OpenLayers.Style({
		'cursor' : 'pointer',
		'strokeOpacity' : 0,
		'strokeColor' : "BLUE",
		'pointRadius' : 0,
		'strokeWidth' : 10
	});
	
	var intersectStyle = new OpenLayers.Style({
		'cursor' : 'pointer',
		'strokeOpacity' : 0.5,
		'strokeColor' : "PURPLE",
		'pointRadius' : 0,
		'strokeWidth' : 10
	});
	
	var intersectPointStyle = new OpenLayers.Style({
		'cursor' : 'pointer',
		'strokeOpacity' : 0.5,
		'strokeColor' : "RED",
		'strokeWidth' : 10,
		'pointRadius' : 8,
		'label' : '_ Intersección ',
		'labelAlign' : 'lt',
		//'fontColor' : 'white',
		'fontSize' : 15
	});
	
	
	if($(c).val()=="Iniciar"){	
		calles = new OpenLayers.Layer.Vector("Calles",{styleMap: selectStyle});
		seleccion =   new OpenLayers.Layer.Vector("Seleccion",{styleMap: uselectStyle});
		interSeleccion =   new OpenLayers.Layer.Vector("interSeleccion",{styleMap: intersectStyle});
		interPointSeleccion = new OpenLayers.Layer.Vector("interPointSeleccion",{styleMap: intersectPointStyle});
		
		//calles.setZIndex(203);
		capaDesniveles = new OpenLayers.Layer.Vector("Desnivel");
		capaAforos = new OpenLayers.Layer.Vector("Aforos");
		capaTopes = new OpenLayers.Layer.Markers("Topes");
		capapAuto = new OpenLayers.Layer.Markers("Paradas de Autobus");
		handler = OpenLayers.Handler.Path;
		lineaAforo = new OpenLayers.Control.DrawFeature(capaAforos, handler,{callbacks : {"point": pointHandler},displayClass:"lineaAforo"});
		mapa.addControl(lineaAforo);
		
		//capaAsem = new OpenLayers.Layer.Vector("Sem");
		mapa.addLayers([calles,semaforosLayer,capaAforos,capaDesniveles,capaTopes,capapAuto,seleccion,interSeleccion,interPointSeleccion]);
		
		capaDesniveles.setZIndex(112);
		capaAforos.setZIndex(111);
		capaTopes.setZIndex(201);
		capapAuto.setZIndex(202);
		semaforosLayer.setZIndex(200);
		
		//capaAsem.setZIndex(200);
		//empezamos a interactuar con el usuario recibiendo propuestas.
		//hacemos una peticion con los segmentos en la caja y los guardamos en un arreglo.
		mapa.addControl(new OpenLayers.Control.MousePosition());
		caja = mapa.getExtent().toArray(true);
		
		$.post("peticiones.php","action=getSegmentosEnCaja&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(d){
			if(!d.hayError){
				var jsondecode = new OpenLayers.Format.GeoJSON();
				
				var geojson_format = new OpenLayers.Format.GeoJSON({
					'internalProjection': new OpenLayers.Projection("EPSG:900913"),
					'externalProjection': new OpenLayers.Projection("EPSG:4326")
				});
				
				var oldFeature;
				var oldIntersections = new Array();
				var oldPoints = new Array();
				var clicBegin=null;
				var clicLine=null;
				var numClic=0;
				var oldLine;
				var interSelected = null;
				var clicValido;
				
				calles.addFeatures(jsondecode.read(d.features));
				
				mapa.events.register("mousemove",mapa,function(e){
					mousecoords = mapa.getLonLatFromPixel(e.xy);
				});
				
				
				controlCalles = new OpenLayers.Control.SelectFeature(calles,{
					toggle : false,
					multiple : false,
					hover : false,
					clickout : false,
					onUnselect : function(uf){
						seleccion.destroyFeatures(oldFeature);
						//controlCallesClic.activate();
						interSeleccion.destroyFeatures(oldIntersections);
						interPointSeleccion.destroyFeatures(oldPoints);
						clicBegin = null;
						numClic=0;
					},
					
					onSelect : function(f){
						
						caja = mapa.getExtent().toArray(true);
					
						$.post("peticiones.php","action=getInterseccionPuntos&idosm="+f.data.idlinea+
						"&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(inter){
							oldFeature = jsondecode.read(inter.seleccion.features);
							seleccion.addFeatures(oldFeature);
							oldLine=f.data.linea;
						
							oldIntersections = jsondecode.read(inter.lineas.features);
							oldPoints = jsondecode.read(inter.puntos.features);
							interSeleccion.addFeatures(oldIntersections);
							interPointSeleccion.addFeatures(oldPoints);

						},"json")
						.success(function(){controlCallesClic.activate();})
						.error(function(){alert("Error de la función: Interseccion" );});
						oldLine=f.data.linea;
						//.success(function(){controlCallesClic.activate();});
						//controlCallesClic.activate();

					}
					
				});
				
				calles.events.on({
					featureselected : function(f){
						//controlCalles.activate();
						controlCallesClic.deactivate();
						
						lineaAforo.activate();
					}
				});
				
				/*capaAforos.events.on({
					featureadded : function(f){
						if(numPoint>2)
							alert(handler.getSketch());	
					}
				});*/
				
				
				
				//opcional a revisar
				/*function founcion(f){
						alert(""+f);
					}
				lineaAforo.events.register('featureadded', lineaAforo, founcion);*/
				

				controlCallesClic = new OpenLayers.Control.SelectFeature([seleccion,interSeleccion],{
					clickFeature : function(clic){
						if(typeof nuevoObjeto != "undefined"){
						
							$.post("peticiones.php","action=compruebaPunto&x1="+mousecoords.lon+"&y1="+mousecoords.lat+"&linea="+oldLine+"&clic="+clic.data.point+"",
							function(valido){
								clicValido=valido.valido;
								
								if(clic.data.point)
								{
									alert("Punto:"+clic.data.point);
									if(clic.id != interSelect.id)
										interSelect = {
											"id" : clic.id,
											"point" : valido.interseccion
										};
								}
							},"json")
							.error(function(){alert("error punto "+clic.data.puntos);});
							
							if(clic.point = "t")
								alert("paso interseccion");
							else
								alert("no ha pasado por la interseccion");
							
							if(clic.data.point)
							{
								alert("Punto:"+clic.data.point);
								interSelect = {
									"id" : clic.id,
									"point" : true
								};
							}
							
							if(clicValido="t"){
								if(clicBegin){
							
									if(numClic<3)
									{
										$.post("peticiones.php","action=compruebaLinea&x1="+clicBegin.x+"&y1="+clicBegin.y+"&x2="
										+mousecoords.lon+"&y2="+mousecoords.lat+"&linea="+oldLine+"",
										function(simil){
											
	//										alert("Valido: "+simil.igual);
	
											if(simil.igual=='t'){
												numClic++;
												clicBegin= 
												{
													"x" : mousecoords.lon,
													"y" : mousecoords.lat
												};
												nuevoObjeto.anadirPosicion(mousecoords.lon,mousecoords.lat,clic.data.idlinea);
											}
											else
												alert("Fuera de la calle");
										},"json")
										.error(function(){alert("Error Comprueba");});
									}
									else
										alert("Máximo 3 puntos para un aforo");
								}
								else{
									nuevoObjeto.anadirPosicion(mousecoords.lon,mousecoords.lat,clic.data.idlinea);
									clicBegin= 
									{
										"x" : mousecoords.lon,
										"y" : mousecoords.lat
									};
								}
							}
							else
								alert("El punto queda fuera de la calle,aplica Zoom+");
						
						}
						else{
							alert(' No has especificado que objeto quieres insertar:');
						}
					}
				
				});
				
				mapa.addControl(new OpenLayers.Control.MousePosition());	
				mapa.addControl(controlCalles);
				mapa.addControl(controlCallesClic);
				controlCalles.activate();
			}
			else{
				alert("No hay segmentos guardados en esta area.");
			}
		},"json");
		

		estaProponiendo = true;
		$(c).val("Terminar");
		$.post("peticiones.php","action=guardarPropuesta&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(){});

	}
	else if($(c).val()=="Terminar"){
		//Dejar de recibir propuestas del usuario
		desactivaClic();
		estaProponiendo = false;
		//mapa.removelayer([calles,semaforosLayer,capaAforos,capaDesniveles]);
		mapa.removeLayer(calles,capaAforos,capaDesniveles);
		mapa.removeLayer(capaAforos);
		mapa.removeLayer(capaDesniveles);
		mapa.removeLayer(capaTopes);
		mapa.removeLayer(capapAuto);
		$("#semop,#topeop,#pautop,#dniop,#afrop,#confgen").hide();
		$("#duser").show();
		//$(#opcionPropuesta = 'Elija una opción:')
		$(c).val("Iniciar");		
		//hay que vaciar todas las variables y datos javascript que se
		//cargaron durante la interaccion.
	}	
}


function pointHandler(newPoint){
	numPoint ++;
	console.log("Handler "+numPoint+"lineaAforo "+lineaAforo);
	capaAforos.addFeatures(newPoint);
	if(numPoint>3){
		lineaAforo.finishSketch();
			alert(handler.getSketch());
		}
}

function validaInterseccion(anterior){
}

function OSMLatLong(lng,lat){
	return new OpenLayers.LonLat(lng,lat).transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913"));
}
