function Configuracion(){
}

Configuracion={
	attr1 : "",
	attr2 : "",
	attr3 : "",
	init : "",
	end : "",
	utyl:""
};

Configuracion.setAtributes=function(attrs){
	for(key in attrs){
		Configuracion[key] = attrs[key];
	}
};

Configuracion.setAttr1 = function(name,val){
	Configuracion.attr1 = new Array({name:val});
};

Configuracion.setAttr2 = function(name,val){
	Configuracion.attr2 = new Array({name:val});
};

Configuracion.setAttr3 = function(name,val){
	Configuracion.attr3 = new Array({name:val});
};

Configuracion.setInitEnd = function(init,end){
	Configuracion.init=init;
	Configuracion.end=end;
};

Configuracion.getUtiyl = function(){
	var array = Configuracion.config;
	var ut = Configuracion.config.utyl 
	ut += "<configuration>"
	ut += "<state>"+ ""+"</state>";
};
