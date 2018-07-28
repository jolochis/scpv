TrafficObject.properties = {
	_iddb:"",
	_attr:null,
	_cofig:new Array(),
	_points:"",
	_name:"",
	_idPropuesta:""
};

TrafficObject.propuesta = {
	_idprop:0,
	_iduser:0
};

function TrafficObject(idprop,iduser){
		TrafficObject.propuesta._idprop=idprop;
		TrafficObject.propuesta._iduser=iduser;
		TrafficObject.properties._config.push(new Configuracion());
}

	TrafficObject.prototype.newConfiguration = function(){
		TrafficObject.properties._cofig.push(new Configuracion());
	};

	TrafficObject.prototype.setAttr = function(p){
		TrafficObject.properties._attr=p;
	};

	TrafficObject.prototype.setName = function(p){
		TrafficObject.properties._name=p;
		
	};
	
	TrafficObject.prototype.setConfiguration=function(){
		var size = TrafficObject.properties._cofig.length;
		var config = TrafficObject.properties._cofig[size-1];
		config.end = document.getElementById('config_end');
		config.init = document.getElementById('config_init');
	};

	TrafficObject.prototype.setPoint = function(point){
		TrafficObject.properties._points = point.x+" "+point.y;
	
	TrafficObject.prototype.getProperties = function(){
		return TrafficObject.properties;
	};

	TrafficObject.prototype.printl = function(){
		alert("TrafficaObject");
	};
