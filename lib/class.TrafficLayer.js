TrafficLayer={
	VECTOR : 0,
	MARKER : 1,
	layer : "",
	styleMap : "",
	onClick : "",
	featureAdded : "",
	map : ""
};

function TrafficLayer(map,name,type){
	TrafficLayer.map = map;
	var clic;
	
	clic = new OpenLayers.Control.SelectFeature(TrafficLayer.layer,{
		clickFeature : function(data){
			alert(data.data.type);
		}
	});
		
	switch(type){
		case TrafficLayer.VECTOR:
			TrafficLayer.prepareVector; break;
		break;
			
		case TrafficLayer.MARKER:
			TrafficLayer.prepareMarker();
			break;
		break;
	}
}

TrafficLayer.prototype.prepareVector=function(){
	
	TrafficLayer.layer = new OpenLayers.Layer.Vector(name,{styleMap : clic});
	TrafficLayer.featureAdded= new OpenLayers.Control.DrawFeature(
		TrafficLayer.layer,
		OpenLayers.Handler.Point,
		{"displayClass": "featureAdded"});
		
	TrafficLayer.layer.events.on({
			featureAdded: TrafficLayer.lineAdded(feature);
		});
};

TrafficLayer.prototype.prepareMarker=function(){
	
	TrafficLayer.layer = new OpenLayers.Layer.Markers(name,{styleMap : clic});
	TrafficLayer.featureAdded= new OpenLayers.Control.DrawFeature(
		TrafficLayer.layer,
		OpenLayers.Handler.Point,
		{"displayClass": "featureAdded"}
	);
		
	TrafficLayer.layer.events.on({
			featureAdded: TrafficLayer.pointAdded(feature);
	
	});
};

TrafficLayer.prototype.pointAdded = function(nPoint){
};

TrafficLayer.prototype.lineAdded = function(nPoint){
	obj = ""+f.feature.geometry
	if(obj.indexOf ("LINESTRING")>-1){
		$.post("peticiones.php","action=getSubLinea&line="+obj,function(f)
		{
			var jsondecode = new OpenLayers.Format.GeoJSON();
			line = jsondecode.read(f.features);
			capaAforos.addFeatures(line);
		},"json")
		.error(function(){alert("Error de la función: Sublinea Evento");});
	}
};

TrafficLayer.prototype.drawObject = function(){
	
};

TrafficLayer.prototype.validate = function(){
};

