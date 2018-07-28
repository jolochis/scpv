var mapa;
var capaAforos;
	/*
		Este objeto se encarga de dibujar las líneas del aforo, pontHandler es una función que se ejecuta
		cada vez que el usuario agrega un punto, clic, a la línea a dibujar
	*/
var handler;
var lineaAforo;
var feature;
/*
Se declara el control Snapp que permite que la línea que vaya a dibujar el usuario, de forma automática, se dibuje sobre una linea
*/
var snap;
		
var numPoint=0;
var oldLine = null;
var oldPoint = null;

function Aforos(map){
	  mapa=map;
	  this.preparaAforo();
	  mapa.addLayers([  capaAforos]);
	  mapa.addControl(  lineaAforo);
}

Aforos.prototype.preparaAforo=function(){
	  capaAforos = new OpenLayers.Layer.Vector("Aforos");
	  handler = OpenLayers.Handler.Path;
	  lineaAforo = new OpenLayers.Control.DrawFeature(  capaAforos,   handler,{callbacks : {"point":this.pointHandler},displayClass:"lineaAforo"});
	  snap = new OpenLayers.Control.Snapping({
                layer: capaAforos,
                greedy: true
		});
};

Aforos.prototype.init = function(){
	  lineaAforo.activate();
	  snap.activate();
};


Aforos.prototype.snapAddLayer = function(layer){
	  snap.addTargetLayer(layer);
};

function setMapa(mapa){
	  mapa=mapa;
	
}

Aforos.prototype.getCapa = function(){
	return   capaAforo;
};

function featureAdded(f){
	alert(f);
}

Aforos.prototype.addPoint = function(line){

	if(  oldLine==line && numPoint==1)
	{
		  lineaAforo.cancel();
		  lineaAforo.deactivate();
		  controlCalles.deactivate();
		  this.dibujaLinea(line,snap.point,oldPoint)
	}
	else
	{
		oldLine = line;
		numPoint++;
		oldPoint = snap.point;
		lineaAforo.insertXY(snap.point.x,snap.point.y);
		if(numPoint>2)
		{
			alert("si entra a la classe de aforos");
			lineaAforo.finishSketch();
			controlCalles.deactivate();
			lineaAforo.deactivate();
		}
	}
};

Aforos.prototype.pointHandler = function(newPoint){
	numPoint++;
	if(numPoint>2){
			  lineaAforo.finishSketch();
			controlCalles.deactivate();
			  lineaAforo.deactivate();
		}
};


Aforos.prototype.getFeature = function(){
	return feature;
};

Aforos.prototype.dibujaLinea = function(linea,punto1,punto2){
	$.post("peticiones.php","action=getSubLinea&line="+linea+"&point1="+punto1+"&point2="+punto2,function(f)
		{
			var jsondecode = new OpenLayers.Format.GeoJSON();
			capaAforos.addFeatures(jsondecode.read(f.features[0].geometry));
		},"json")
		.error(function(){alert("Error de la función: Sublinea");});
};

Aforos.prototype.validaLinea = function(line,point){
	$.post("peticiones.php","action=compruebaLinea&x1="+  oldPoint.x+"&y1="+  oldPoint.y+"&x2="
		+point.x+"&y2="+point.yt+"&linea="+line+"",
		function(simil){
									
		//alert("Valido: "+simil.igual);

		if(simil.igual!='t'){
			alert("Fuera de la calle"+lineaAforo.getCurrentPointIndex());
			  numPoint--;
			  lineaAforo.redo();	
		}
		
		
	},"json")
	.error(function(){alert("Error Comprueba");});
};