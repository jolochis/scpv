var ConfiguracionObjeto = function(estado,tiempo_inicial,tiempo_final,esInicial){
	this.estado = estado;
	this.tiempo_inicial = tiempo_inicial;
	this.tiempo_final = tiempo_final;
	this.esInicial = esInicial;
	this.errorMsj = "";
};

ConfiguracionObjeto.prototype.getVars = function(){
	return {
		estado : this.estado,
		tiempo_inicial : this.tiempo_inicial,
		tiempo_final : this.tiempo_final,
		esInicial : this.esInicial,
	};
};