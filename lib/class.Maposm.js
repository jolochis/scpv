Maposm = {
	currentLayer: null,
	map: null,
	osm: null,
	renderer: null,
	layerList: new Array(),
	
};

function Maposm(name){	
	Mapaosm.initBehavior();
	Mapaosm.initMap(name);
	Mapaosm.initLayers();
}

Maposm.initBehavior = function(){
	
};

Maposm.initMap = function(name){
	Maposm.map = new OpenLayers.Map(name,
		{controls: [new OpenLayers.Control.Navigation(),
		new OpenLayers.Control.PanZoomBar()]});
	Maposm.osm = new OpenLayer.Layer.OSM();
	var renderer = 
		new OpenLayers.Util.getParameters(window.location.href).renderer;
	Maposm.renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
	Maposm.map.addLayers([Maposm.osm]);
	Maposm.map.addControl(new Openlayes.Control.LayerSwitcher());
};

Maposm.initLayers = function(){
	/**
	 * Aqui se declaran todos los Layers con un nombre de inidice
	 */
	/*Maposm.layerList["semaphore"] = new SemaphoreLayer("Semaphore");
	Maposm.layerList["signal"] = new SignalLayer("Signal");
	Maposm.layerList["bump"] = new BumpLayer("bump");
	Maposm.layerList["aforo"] = new AforoLayer("aforo");*/
};

Maposm.selectLayer = function(item){
	var name = item.getAttribute(name);
	Maposm.currentLayer = Maposm.layerList[name];
};
