var nCarriles;
var nomCarril;
var oneWay;

function Carriles(numero, nombres, oneWay){
	nCarriles = numero;
	enSentido = oneWay;
	nomCarril = nombres.split('|');
}


Carriles.prototype.dialogo = function (){
	var index;
	var nInputs =  new Array();
	
	for(index=0;index<nCarriles;index++){
		nInputs.push({header: "Carril "+nomCarril[index], type: "checkBox", name: "carril"+index, value: nomCarril[index]});
	} 

	$.msgBox({ 
	
		title: "Carriles",
		type: "prompt",
		inputs: nInputs,
		buttons: [
		{ value: "Aceptar" }, {value:"Cancelar"}],
		success: function (result, values) {
			
		}
	});
	
};