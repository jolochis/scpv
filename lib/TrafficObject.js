function TrafficObject(map){
	
	TrafficObject.configurations=[];
	TrafficObject.type = '';
	TrafficObject.name = '';
	TrafficObject.id = '';
	TrafficObject.dataid = '';
	TrafficObject.status ='';
	TrafficObject.map = map;	
}


/**
 * Agrega una nueva configuracion al objeto vial
 */
TrafficObject.prototype.addConfiguration = function(){
	TrafficObject.configurations = new TrafficConfiguration();
};

/**
 *Agrega objeto visual en mapa 
 */
TrafficObject.prototype.showObject = function(){
	
};

/**
 * Guarda toda la información del objeto o actualiza en la base de datos
 */
TrafficObject.prototype.save = function(){
	
};

/**
 * Elimina de la base de datos la informacion del objeto vial
 */
TrafficObject.prototype.delete = function(){
	
};