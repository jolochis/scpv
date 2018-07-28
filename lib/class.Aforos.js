var mapa;
var capaAforos;
var lineaAforo;
var oldFeature;
var snap;
var numPoint=0;
var idLines = new Array();
var oldLine = null;
var oldPoint = null;
var newPoint = null;
var validLine = false;
var path = OpenLayers.Handler.Path;
var aforoPositions;
var aforoPuntoInicio;
var aStyle = new OpenLayers.StyleMap({
        "default": new OpenLayers.Style({
			'cursor' : 'pointer',
			'strokeColor' : "BLACK",
			'pointRadious' : 6,
			'strokeWidth' : 3,
			'pointRadius' : 7,
		'externalGraphic' : "${graphic}",
		rotation : "${angle}"
		})
    });
	

function Aforos(map){
	  mapa=map;
	  //interSeleccion = is;
	  //seleccion = s;
	  this.preparaAforo();
	  mapa.addLayers([capaAforos]);
	  mapa.addControl(lineaAforo);
}


Aforos.prototype.preparaAforo=function(){
	aforoStyle = new OpenLayers.Style({
		'cursor' : 'pointer',
		'strokeColor' : "BLACK",
		'fillColor' :"blue",
		'strokeWidth' : 3
	});
	
	capaAforos = new OpenLayers.Layer.Vector("Aforos",{styleMap: aStyle});
	/*Este objeto se encarga de dibujar las líneas del aforo, pontHandler es una función que se ejecuta
	  cada vez que el usuario agrega un punto, clic, a la línea a dibujar*/
	lineaAforo = new OpenLayers.Control.DrawFeature(capaAforos,path,
		{
			callbacks : {"point":pointHandler},
			displayClass:"lineaAforo",
			handlerOptions:{
				"dblclick":function(){}
			}
		}
	);
	/*Se declara el control Snapp que permite que la línea que vaya a dibujar el usuario, de forma automática, se dibuje sobre una linea*/
	snap = new OpenLayers.Control.Snapping({
		layer: capaAforos,
        greedy: true
	});
	
	capaAforos.events.on({
		"featureadded" : function(f)
		{
			obj = ""+f.feature.geometry;
			if(f.feature.data && f.feature.data.aforo)
				f.feature.data.points = oldFeature;
			if(obj.indexOf ("LINESTRING")>-1)
			{
				if(!f.feature.data.id){
					$.post("antragen.php",{
						action:"getAforo","id1":idLines[0],
						"id2":idLines[idLines.length-1],
						line:obj,pj:900913},
					function(f){
						var jsondecode = new OpenLayers.Format.GeoJSON();
						//aforoPositions = f.aforoPoints;
						oldFeature = jsondecode.read(f.features);
						capaAforos.addFeatures(oldFeature);
						selectedFeature= {f:oldFeature,id:null};
						propuestas.enableElement(".cseleccion");
						propuestas.enableElement(".save");
						lineascr = idLines[0];
						lineadst = idLines[idLines.length-1];
					},"json")
					.error(function(){alert("Error de la función: getAforos");});
				}
			}

		},
		"sketchstarted":function(s){
			setInitPoint();
			}
	});
	
};

Aforos.prototype.init = function(){
	snap.addTargetLayer(capaAforos);
	lineaAforo.activate();
	snap.activate();
};

Aforos.prototype.finish = function (){
	lineaAforo.cancel();
	//this.finish();
	numPoint=0;
	seleccion.destroyFeatures();
	interSeleccion.destroyFeatures();
	interPointSeleccion.destroyFeatures();
	lineaAforo.deactivate();
};

Aforos.prototype.borrarReciente = function (){
	capaAforos.destroyFeatures(oldFeature);
};

Aforos.prototype.borrarTodo = function (){
	capaAforos.destroyFeatures();
};

Aforos.prototype.snapAddLayer = function(layer){
	  snap.addTargetLayer(layer);
};

function setMapa(mapa){
	  mapa=mapa;
}

Aforos.prototype.getCapa = function(){
	return this.capaAforo;
};


function setInitPoint(){
	initPoint = aforoPuntoInicio ;
	if(initPoint)
	{
		newPoint = new OpenLayers.Geometry.Point(initPoint.x,initPoint.y);
		nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,selectedLine);
		dibujaAforo(selectedLine,false);		
	}
};

Aforos.prototype.addAforo=function(f){
	var nodes = f[0].geometry.getVertices();
	capaAforos.addFeatures(f);
};
	

Aforos.prototype.dibujaLinea = function (line,id1,id2){

	$.post("antragen.php",{action:"getAforo","id1":id1,"id2":id2,"line":line},
		function(){
			var jsondecode = new OpenLayers.Format.GeoJSON();
			oldFeature = jsondecode.read(f.features);
			capaAforos.addFeatures(oldFeature);
		},"json")
	.error(function(d){alert("Error de la función: getAforos :"+d.responseText);});
	this.finish();
	
};

/*
function dibujaLinea(linea,punto1,punto2){
//alert(linea+" "+punto1+" "+punto2);
	$.post("peticiones.php","action=getSubLinea&line="+linea+"&point1="+punto1+"&point2="+punto2,function(f)
	{
		var jsondecode = new OpenLayers.Format.GeoJSON();
		oldFeature = jsondecode.read(f.features);
		capaAforos.addFeatures(oldFeature);
		//capaAforos.addFeatures([new OpenLayers.Feature.Vector(punto1,{angle:ang})]);
		//capaAforos.addFeatures([new OpenLayers.Feature.Vector(punto2,{angle:ang})]);
		//alert(punto1);
		
	},"json")
	.error(function(){alert("Error de la función: Sublinea");});
	this.finish();
}*/


function borrarTodoAforo(){
	lineaAforo.finishSketch();
	//this.finish();
	numPoint=0;
	seleccion.destroyFeatures();
	interSeleccion.destroyFeatures();
	interPointSeleccion.destroyFeatures();
	lineaAforo.deactivate();
	//lineaAforo.activate();
	controlCalles.activate();
	controlCallesClic.deactivate();
	
}

function dibujaAforo(id,done){

	if(id>0){
		numPoint++;
		idLines.push(id);
		lineaAforo.insertXY(newPoint.x,newPoint.y);
		if(done)
		{
			borrarTodoAforo();
		}	
		oldPoint = new OpenLayers.Geometry.Point(newPoint.x,newPoint.y);
	}
	else
		borrarTodoAforo();
}


Aforos.prototype.addPoint = function(line,id){
	//alert(line);
		//snap.removeTargetLayer(capaAforos);
	newPoint = new OpenLayers.Geometry.Point(snap.point.x,snap.point.y);
	switch(numPoint){
		case 0: 
			if(selectedLine==id){
				nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,id);
				dibujaAforo(id,false);
			}
			else{
				lineaAforo.redo();
				alert("El aforo debe iniciar sobre la calles que seleccionaste");
			}
		break;
		case 1:
			if(id==selectedLine){
				nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,id);
				dibujaAforo(id,true);
			}
			else if(line.intersects(newPoint)){
				nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,id);
				dibujaAforo(id,false); 
			}
			else{
				lineaAforo.redo();
				$("#mbox").html("Punto no valido");
			}
		break;
		case 2:
			if(id==selectedLine){
				nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,id);
				dibujaAforo(id,true);
			}
			else if(line.intersects(newPoint) && !line.intersects(oldPoint)){
				$("#mbox").html("El aforo debe de ser continuo");
				lineaAforo.redo();
			}
			else{
				nuevoObjeto.anadirPosicion(newPoint.x,newPoint.y,id);
				dibujaAforo(id,true);
			}
		break;
	}
};

function pointHandler(nPoint){
	/*if(snap.point)
		lineaAforo.insertXY(snap.point.x,snap.point.y);
	else*/
	lineaAforo.redo();
	lineaAforo.undo();
	$("#mbox").html("Fuera del cámino");
}


Aforos.prototype.getFeature = function(){
	return feature;
};


function validaLinea(line,point,newLine){
	valido = false;
	$.post("peticiones.php","action=compruebaLinea&x1="+  oldPoint.x+"&y1="+ oldPoint.y+"&x2="
		+point.x+"&y2="+point.y+"&linea="+line+"",
		function(simil){
		
			if(simil.igual!='t'){
				$("#mbox").html("Fuera de la calle"+lineaAforo.getCurrentPointIndex());
				numPoint--;
				lineaAforo.redo();
				valido =false;
			}
			else{
				valido = true;
			}
		},"json")
	.error(function(){alert("Error Comprueba"); valido = false;});
	return valido;
}
	
