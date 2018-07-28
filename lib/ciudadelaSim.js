ciudadelaSim={};
var interval;
var caja = new Array();
//control de capas
var controlCalles;
var controlCallesClic;
var simulacion;
var numPoint =0;
var controlSimulacion;
var controlCalles;
var controlGeneral;
var selectedLine;
	//variablres de los controles
	var oldFeature;
	var oldIntersections = new Array();
	var oldPoints = new Array();
	var clicBegin=null;
	var clicLine=null;
	var numClic=0;
	var oldLine;
	var interSelected = null;
	var clicValido;
	//fin variables de control
//fin de control

//capas
var aforos;
var calles;
var seleccion;
var interSeleccion;
var interPointSeleccion;
//fin capas

//estilos de formato
var simuStyle;
var uselectStyle;
var selectStyle;
var intersectStyle;
var intersectPointStyle;
var semaStyle;
var topeStyle;
//fin estilos

	ciudadelaSim.addSimulation=function(feature){

		simulacion.destroyFeatures();
		var jsondecoder = new OpenLayers.Format.GeoJSON({
			'internalProjection': new OpenLayers.Projection("EPSG:900913"),
			'externalProjection': new OpenLayers.Projection("EPSG:4326")
		});
		
		simulacion.addFeatures(jsondecoder.read(feature));
		controlSimulacion = new OpenLayers.Control.SelectFeature(simulacion,{
			clickFeature:function(f){
				simulaciones.getDataSimulator(f.data.name,f.data.distance);
			}
		});
		mapa.addControl(controlSimulacion);
		
		controlSimulacion.activate();
		controlCalles.deactivate();
		controlCallesClic.deactivate();
	};


	ciudadelaSim.clearCapas=function(){
		aforos.borrarTodo();
		calles.destroyFeatures();
		seleccion.destroyFeatures();
		interSeleccion.destroyFeatures();
		interPointSeleccion.destroyFeatures();
	};
	

	ciudadelaSim.loadLayersControls = function(){
		calles = new OpenLayers.Layer.Vector("Calles",{styleMap: selectStyle});
		seleccion = new OpenLayers.Layer.Vector("Calle Seleccionada",{styleMap: uselectStyle});
		interSeleccion = new OpenLayers.Layer.Vector("Calle Vecina",{styleMap: intersectStyle});
		interPointSeleccion = new OpenLayers.Layer.Vector("Intersecion",{styleMap: intersectPointStyle});
		simulacion = new OpenLayers.Layer.Vector("Simulacion",{styleMap: simuStyle});
		capaDesniveles = new OpenLayers.Layer.Vector("Desnivel");
		capaTopes = new OpenLayers.Layer.Vector("Topes",{styleMap: topeStyle});
		capaSemaforos = new OpenLayers.Layer.Vector("Semaforos",{styleMap: semaStyle});
		capapAuto = new OpenLayers.Layer.Markers("Paradas de Autobus");
	
		//Aforos
		aforos = new Aforos(mapa);
		aforos.snapAddLayer(seleccion);
		aforos.snapAddLayer(interSeleccion);
		aforos.finish();
		//aforos.initAforos(mapa);
		
		mapa.addLayers([capaSemaforos,simulacion,calles,capaDesniveles,capaTopes,capapAuto,seleccion,interSeleccion,interPointSeleccion]);
		capaDesniveles.setZIndex(1);
		//capaAforos.setZIndex(111);
		interSeleccion.setZIndex(1);
		seleccion.setZIndex(0);
		capaTopes.setZIndex(3);
		capapAuto.setZIndex(4);
		capaSemaforos.setZIndex(6);
		capaAforos.setZIndex(5);
		mapa.setLayerIndex(seleccion,0);
		mapa.setLayerIndex(interSeleccion,1);
		mapa.setLayerIndex(capaAforos,10);
		mapa.setLayerIndex(capaSemaforos,11);
		//mapa.setLayerIndex(semaforosLayer,12);
		//capaAsem.setZIndex(200);
		//empezamos a interactuar con el usuario recibiendo propuestas.
		//hacemos una peticion con los segmentos en la caja y los guardamos en un arreglo.
		mapa.addControl(new OpenLayers.Control.MousePosition());
		caja = mapa.getExtent().toArray(true);
		
		//Controles de Seleccion de objeto
				
		calles.events.on({
			featureselected : function(f){
				//controlCalles.activate();
				//controlCallesClic.deactivate();
				//aforos.init();
			}
		});
		
		/**
		 * Revisar
		 * 
		 * ClickFeature se encarga de mostrar el recuadro de configuración de objetos
		 */
		
		controlCalles = new OpenLayers.Control.SelectFeature([calles,capaSemaforos,capaTopes],{
			toggle : false,
			multiple : false,
			hover : false,
			clickout : false,
			clickFeature: function(clic){
				$("#cov-dialog").modal("show");
				/*selectedLayer = clic.layer;
				if(selectedLayer == calles && clic.data.idlinea){
					ciudadelaSim.seleccionCalles(clic,clic.geometry);
				}
				else if(selectedLayer == capaSemaforos){//calles
					selectedFeature = {feature:clic};
					ciudadelaSim.muestraDatos(clic);
				}*/
			},
			onUnselect : function(uf){
				/*seleccion.destroyFeatures(oldFeature);
				//controlCallesClic.activate();
				interSeleccion.destroyFeatures(oldIntersections);
				interPointSeleccion.destroyFeatures(oldPoints);
				clicBegin = null;
				numClic=0;*/
			},
			
			onSelect : function(f){
					console.log(f);
					//.success(function(){controlCallesClic.activate();});
					//controlCallesClic.activate();
			}
				
		});
						
			
		controlCallesClic = new OpenLayers.Control.SelectFeature(
			[interSeleccion,seleccion],{
			clickFeature:function(clic){
				selectedLayer = clic.layer;
				aforos.addPoint(clic.geometry,clic.data.idlinea);
				/*if(clic.data.id){
					selectedFeature = {feature:clic};
					ciudadelaSim.muestraDatos(clic);
				}
				else if(selectedLayer == seleccion||
					selectedLayer == interSeleccion)
					ciudadelaSim.seleccionCalles(clic,oldLine);
				*/
				}
		});
			//finseleccion objeto
			//mapa.addControl(controlGeneral);
		mapa.addControl(new OpenLayers.Control.MousePosition());	
		mapa.addControl(controlCallesClic);
		mapa.addControl(controlCalles);
		controlCallesClic.deactivate();
			//fin de controles de seleccion
	};
	

	ciudadelaSim.initLoadPropuesta=function(idp,idu){
		/*MODIFICAR, que se carguen las calles de acuerdo a las
		 * coordenadas de la propuesta, actualmente lo hace con los del mapa*/
		
		$.post("antragen.php",{action:"getCuadrante",p:idp},
			function(data){
				if(data.m=="ok"){
					caja[0] = data.south;
					caja[1] = data.east;
					caja[2] = data.north;
					caja[3] = data.west;
					
					var newLat = ((data.north - data.south)/2); 
					var newLon = ((data.west - data.east)/2);
					mapa.setCenter(new OpenLayers.LonLat(data.west-newLon,data.north-newLat),18);
					ciudadelaSim.loadLayers();
					controlCallesClic.activate();
					controlCalles.deactivate();
					//calles.addMarker(new OpenLayers.Marker.Box(caja));
				}
			}
		);
		
		//caja = mapa.getExtent().toArray(true);
		$.post("antragen.php",{action:"getObjects",p:idp,u:idu},
			function(data){
				if(data.m=="ok"){
					
					var jsondecoder = new OpenLayers.Format.GeoJSON();
					capaTopes.addFeatures(jsondecoder.read(data.topes.features));
					capaSemaforos.addFeatures(jsondecoder.read(data.semaforos.features));
					aforos.addAforo(jsondecoder.read(data.aforos.features));
					
					if(newuser.getStatus()){
						propuestas.menuCargarPropuesta(true);
					}
					else
						propuestas.menuCargarPropuesta(false);
						
				}
				else{
					ciudadelaSim.clearCapas();
					$("#optiontraffic").hide();
					$("#endpropuesta").hide();
					$("#mpropuesta").hide();
				}
					
				$("#mbox").html("Propuesta: "+data.status);
		});
	};
	
	ciudadelaSim.endPropuesta= function(){
		location.reload();
	};


	ciudadelaSim.initNewPropuesta=function(item){
		caja = mapa.getExtent().toArray(true);
		
		if(!newuser.getPropuesta()){
			caja = mapa.getExtent().toArray(true);
			$.post("antragen.php","action=guardarPropuesta&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"&pj=900913",
			function(data){
				if(data.m=="ok"){
					ciudadelaSim.initLayers();
					//calles.addMarker(new OpenLayers.Marker.Box(mapa.getExtend()));
					$("#optiontraffic").show();
					$("#endpropuesta").show();
				}
				else{
					ciudadelaSim.clearCapas();
					$("#optiontraffic").hide();
					$("#endpropuesta").hide();

				}
				$("#mbox").html("Propuesta: "+data.status);
			});
		}
	};
	
	

	ciudadelaSim.initStyles=function(){
		topeStyle = new OpenLayers.StyleMap({
			"default": new OpenLayers.Style({
				'cursor' : 'pointer',
				'strokeColor' : "BLACK",
				'strokeOpacity':0,
				'strokeWidth' : 15,
				'pointRadius' : 15,
			'externalGraphic' : "imagenes/bump.png"
			})
		});
		
		semaStyle = new OpenLayers.StyleMap({
			"default": new OpenLayers.Style({
				'cursor' : 'pointer',
				'strokeColor' : "BLACK",
				'strokeOpacity':0,
				'strokeWidth' : 10,
				'pointRadius' : 10,
			'externalGraphic' : "imagenes/semaphore.png"
			})
		});
		
		simuStyle = new OpenLayers.Style({
			'cursor':'pointer',
			'strokeOpacity':0.5,
			'strokeColor':'PURPLE',
			'strokeWidth':10
		});
		
		uselectStyle = new OpenLayers.Style({
			'cursor' : 'pointer',
			'strokeOpacity' : 0.5,
			'strokeColor' : "BLUE",
			'strokeWidth' : 10
		});

		selectStyle = new OpenLayers.Style({
			'cursor' : 'pointer',
			'strokeOpacity' : .3,
			'strokeColor' : "BLUE",
			//'label' : "${label}",
			'labelAlign' : 'cm',
			'pointRadius' : 0,
			'strokeWidth' : 15
		});
		
		intersectStyle = new OpenLayers.Style({
			'cursor' : 'pointer',
			'strokeOpacity' : 0.5,
			'strokeColor' : "PURPLE",
			'pointRadius' : 0,
			'strokeWidth' : 10
			
		});
		
		intersectPointStyle = new OpenLayers.Style({
			'cursor' : 'pointer',
			'strokeOpacity' : 0.5,
			'strokeColor' : "RED",
			'strokeWidth' : 10,
			'pointRadius' : 8,
			'label' : '_ Intersección ',
			'labelAlign' : 'lt',
			'fontSize' : 15
		});
	};

	ciudadelaSim.obtenerEventos=function(){
		/*if(hayMasEventos){
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
		}*/
	};


	function desactivaClic(){
		controlGeneral.deactivate();
	}


	function activaClic(){
		controlGeneral.activate();
	}


	ciudadelaSim.ejecutarInterfazGrafica=function(){
		/*var prosigue = true;
		
		if(!haciendoPeticion && eventosTemp.length == 0){
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
	};


	ciudadelaSim.seleccionaCalles=function(f){
		selectedLine = f.data.idlinea;
			var jsondecoder = new OpenLayers.Format.GeoJSON();
			$.post("peticiones.php","action=getInterseccionPuntos&idosm="+f.data.idlinea+
			"&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(inter){
				oldFeature = jsondecoder.read(inter.seleccion.features);
				seleccion.addFeatures(oldFeature);
				oldLine=f.data.linea;
				selectedLine=f.data.idlinea;
				oldIntersections = jsondecoder.read(inter.lineas.features);
				oldPoints = jsondecoder.read(inter.puntos.features);
				interSeleccion.addFeatures(oldIntersections);
				interPointSeleccion.addFeatures(oldPoints);
			},"json")
			.success(function(){
				aforos.init();
				controlCallesClic.activate();
				aforoPuntoInicio = snap.testTarget(snap.targets[1],new OpenLayers.Geometry.Point(mousecoords.lon,mousecoords.lat));
				
				//aforos.setInitPoint();
				//aforos.addPoint(f.geometry,f.data.idlinea);
				})
			.error(function(){
				alert("Error de la función: Interseccion" );
				selectedLine=null;
				});
			oldLine=f.data.linea;
	};


	ciudadelaSim.seleccionCalles = function(clic,oldLine){		
		if(nuevoObjeto.tipo == "aforo"){
			ciudadelaSim.seleccionaCalles(clic);
		}
		else if(typeof nuevoObjeto != "undefined" ){
			var auxline =clic.geometry;
			$.post("antragen.php","action=compruebaPunto"+"&srcline="+oldLine+"&x1="+mousecoords.lon+"&y1="+mousecoords.lat,
			function(valido){
				clicValido=valido.valido;
			},"json")
			.error(function(data){alert("error punto "+data.responseText);});
			
			if(clicValido="t"){
				//selectedFeature={feature:clic};
				nuevoObjeto.anadirPosicion(mousecoords.lon,mousecoords.lat,clic.data.idlinea);
				propuestas.enableElement(".cseleccion");
				propuestas.enableElement(".save");
			}
			else{
				propuestas.disableElement(".cseleccion");
				propuestas.disableElement(".save");
				$("#mbox").html("El punto queda fuera de la calle,aplica Zoom+");
			}
		}
	};

	ciudadelaSim.muestraDatos = function(f){
		var config = f.data.config[0];
		var name = f.data.type.length>0 ? f.data.type:"";
		var init = config.init ? config.init:"0";
		var end = config.init ? config.end:"0";
		var attr = config.attr1 ? config.attr1:"0";
		var other ="";
		var states = config.states;
		selectedFeature = {feature:f,o:f.data.id,c:config.rid,t:name};
		if(!newuser.getStatus()){
			if(name=="semaforo"){
				var l1 = "\nLuz:"+ states.attr1.attr1+
					" Tiempo "+states.attr1.attr2;
				var l2 = "\nLuz:"+ states.attr2.attr1+
					" Tiempo "+states.attr2.attr2;
				var l3 = "\nLuz:"+ states.attr3.attr1+
					" Tiempo "+states.attr3.attr2;
				other = l1+l2+l3;
				}
			alert("Objeto: "+f.data.type+
				"\nPeriodo: "+init+"-"+end+other);
		}
		else{
			$("."+name).show();
			$(".save").hide();
			$(".modify").show();
			propuestas.enableElement(".modify");
			propuestas.enableElement(".save");
			$("."+name+".attr").val(attr);
			$("select."+name+".attr option").filter(function() {
					return $(this).text() == attr; 
				}).attr('selected', true);
			
			$("."+name+".init").val(init);
			$("."+name+".end").val(end);
			
			if(states.attr1)$("."+name+"."+states.attr1.attr1).val(states.attr1.attr2);
			if(states.attr2)$("."+name+"."+states.attr2.attr1).val(states.attr2.attr2);
			if(states.attr3)$("."+name+"."+states.attr3.attr1).val(states.attr3.attr2);
			
		}
	};

	ciudadelaSim.loadLayers=function(){
		controlCallesClic.activate();
		//controlCalles.activate();
	};


	ciudadelaSim.initLayers=function(c){
		
		controlCallesClic.activate();
		//controlGeneral.deactivate();
		/*Modificar para usar el Utyil 
		 * */
		$.post("antragen.php", {action:"getSimulationFile",pj:"900913"},
		//$.post("peticiones.php","action=getSegmentosEnCaja&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",
			function(d){
				if(d.m!="error"){
				var jsondecode = new OpenLayers.Format.GeoJSON();
				
				var geojson_format = new OpenLayers.Format.GeoJSON({
					'internalProjection': new OpenLayers.Projection("EPSG:900913"),
					'externalProjection': new OpenLayers.Projection("EPSG:4326")
				});				
		

				//calles.addFeatures(jsondecode.read(d.features));
				calles.addFeatures(geojson_format.read(d));
				mapa.events.register("mousemove",mapa,function(e){
					mousecoords = mapa.getLonLatFromPixel(e.xy);
				});
			
				//activar controles de calles
				/*controlCalles.activate();
				controlCallesClic.activate();*/
				//if(nuevoObjeto.tipo=="aforo")
				controlCallesClic.deactivate();
				controlCalles.activate();
				}
				else{
					$("#mbox").html("No hay segmentos guardados en esta area.");
				}
			},"json").
			fail(function(d){
				alert(d.responseText);
			});
		
		newuser.changeActivity(true);
		estaProponiendo = true;
		$(c).val("Terminar");
	};



	ciudadelaSim.newPropuesta=function(c){
		if($(c).val()=="Iniciar"){
			caja = mapa.getExtent().toArray(true);
			$.post("antragen.php","action=guardarPropuesta&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"&pj=900913",
			function(data){
				if(data.m=="ok"){
					$("#optiontraffic").show();
					ciudadelaSim.initLayers(c);
				}
				$("#mbox").html("Propuesta: "+data.status);
			});

		}
		else if($(c).val()=="Terminar"){
			location.reload();
		}
	};


	function activarInteraccionUsuario(c){
		ciudadelaSim.initStyles(c);
		ciudadelaSim.initNewPropuesta(c);	
	}

	
	ciudadelaSim.OSMLatLong=function(lng,lat){
		return new OpenLayers.LonLat(lng,lat).transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913"));
	};

