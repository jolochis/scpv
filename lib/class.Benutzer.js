
var	status = false;
var propuesta = false;
var actividad = false;


function Benutzer(data){
	this.status = data;
}

Benutzer.prototype.changePropuesta = function(data){
	this.propuesta=data;
};

Benutzer.prototype.getStatus=function(){
	return this.status;
};

Benutzer.prototype.getActivity=function(){
	return this.actividad;
};

Benutzer.prototype.getPropuesta=function(){
	return this.propuesta;
}

/*
 * true = Proponiendo
 * false = Finalizando 
 * */
Benutzer.prototype.changeActivity=function(data){
	this.actividad = data;
};

Benutzer.prototype.changeStatus = function(data){
	this.status = data;
};
