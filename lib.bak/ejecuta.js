var eventos=new Array(); //eventos ejecutandose
var eventosTemp = new Array(); //eventos siguientes temporalmente almacenados
var eventosEjec = 0; //eventos que se han ejecutado
var eventosTotalSolic = 0; //total de eventos solicitados
var hayMasEventos=true; //para saber si hay mas eventos si no detener la peticion
var rangoResultados=500; //el numero de resultados que se obtienen en cada peticion
var haciendoPeticion=true;
const VEHICULO = 1; //tipo de objeto vehiculo
const SEMAFORO = 2; //tipo de objeto semaforo
const SEMAFORO_VERDE = "http://www.bioquest.org/bedrock/thailand_1997/images/green_ball.gif"; //url del semaforo en verde.
const SEMAFORO_AMBAR = "http://www.distribuidoralatinoandina.com/images/semaforo-amarillo.jpg";//url del semaforo en ambar
const SEMAFORO_ROJO = "http://www.accuridewheels.com/images/red_ball_m.gif";//url del semaforo en rojo
const nuevosemaforo = "http://www.kitlatren.com/tienda/images/semaforo.png";
const nuevotope = "urbano/demoweb4/ciudadelasim/imagenes/tope.png";
const nuevospautobus = "urbano/demoweb4/ciudadelasim/imagenes/bus.png";


var vehiculos = new Array(); //vehiculos, un arreglo de tipo Marker de OpenLayers
var semaforos = new Array(); //semaforos, un arreglo del tipo Marker de OpenLayers
var vehiculosLayer; //capa de tipo Markers de los vehiculos de OpenLayers
var semaforosLayer; //capa de tipo Markers de los semaforos de OpenLayers
var vehiculoIcon; //un objeto de tipo Icon que almacenara el icono de vehiculos
var semaforoVerdeIcon; //un objeto de tipo Icon que almacenara la imagen en semaforo verde
var semaforoAmbarIcon; //un objeto de tipo Icon que almacenara la imagen en semaforo ambar
var semaforoRojoIcon; //un objeto de tipo Icon que almacenara la imagen en semaforo rojo
var nuevoSemaforoIcon;
var nuevoTopeIcon;
var nuevopAutoIcon;
var mapa;


//para las propuestas del usuario
var estaProponiendo = false;
var calles;
var seleccion;
var interSelecion;
var mousecoords;
var controlCalles;
var nuevoObjeto;
var capaDesniveles;
var capaAforos;
var capaTopes;
var capapAuto;

//id = 0;
//tipo = tipo;
//linea = 0;

function ciudadelaSimInit(){
	mapa = new OpenLayers.Map('mapa', {controls: [new OpenLayers.Control.Navigation(), 
	new OpenLayers.Control.PanZoomBar()]});
    
    var osm = new OpenLayers.Layer.OSM();
    var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
            renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;

    mapa.addLayers([osm]);
  
    mapa.addControl(new OpenLayers.Control.LayerSwitcher());
			
    /*var control = new OpenLayers.Control.SelectFeature(
                    vectors,
                    {
                        multiple: false, hover: true,
                        toggleKey: "ctrlKey", // ctrl key removes from selection
                        multipleKey: "shiftKey" // shift key adds to selection
                    }
                );
	mapa.addControl(control);
    control.activate();*/
    
    
    
    var ext = new OpenLayers.Bounds();
    /*ext.extend(OSMLatLong(-103.3632,20.66396));
    ext.extend(OSMLatLong(-103.3508,20.65833));*/
    ext.extend(OSMLatLong(-103.42132,20.61348));
    ext.extend(OSMLatLong(-103.4001,20.59918));
    /*ext.extend(OSMLatLong(-103.245338,20.675441));
    ext.extend(OSMLatLong(-103.241723,20.67796));*/
    mapa.maxExtent = ext;
    //mapa.setCenter(OSMLatLong(-103.24353, 20.6767005), 18);
    mapa.setCenter(OSMLatLong(-103.41050, 20.60500), 15);
    //mapa.setCenter(OSMLatLong(-103.3580, 20.66000), 16);
    
    
    
    vehiculosLayer = new OpenLayers.Layer.Markers("Vehiculos");
	semaforosLayer = new OpenLayers.Layer.Markers("Semaforos");
	vehiculoIcon = new OpenLayers.Icon("http://www.clasificados.pr/App_Images/master-page/ic_icono_vehiculo.gif",new OpenLayers.Size(15,15),new OpenLayers.Pixel(-7,-15));
	semaforoVerdeIcon = new OpenLayers.Icon(SEMAFORO_VERDE,new OpenLayers.Size(13,13),new OpenLayers.Pixel(-6,-13));
	semaforoAmbarIcon = new OpenLayers.Icon(SEMAFORO_AMBAR,new OpenLayers.Size(13,13),new OpenLayers.Pixel(-6,-13));
	semaforoRojoIcon = new OpenLayers.Icon(SEMAFORO_ROJO,new OpenLayers.Size(13,13),new OpenLayers.Pixel(-6,-13));
	nuevoSemaforoIcon = new OpenLayers.Icon(nuevosemaforo,new OpenLayers.Size(20,20),new OpenLayers.Pixel(-10,-20));
	nuevoTopeIcon = new OpenLayers.Icon(nuevotope,new OpenLayers.Size(20,20),new OpenLayers.Pixel(-10,-20));
	nuevopAutoIcon = new OpenLayers.Icon(nuevospautobus,new OpenLayers.Size(20,20),new OpenLayers.Pixel(-10,-20));

	
	//Este código ejecuta la simulación.
	obtenerEventos();
	setInterval("ejecutarInterfazGrafica()",200);
	mapa.addLayers([vehiculosLayer,semaforosLayer]);
	
	//Este codigo ejecuta la interaccion con el usuario.
	//$("#proponer").bind("click",function(){activarInteraccionUsuario(this);});
	$("#proponer").bind("click",function(){gduser(this);});
	$("#semop,#topeop,#pautop,#dniop,#afrop,#confgen").hide();
	
	
//	nuevoSemaforoIcon = new OpenLayers.Icon("http://www.kitlatren.com/tienda/images/semaforo.png",new OpenLayers.Size(20,20),new OpenLayers.Pixel(-10,-20));
	
	$("#opcionPropuesta").bind("change",function(){
		$("#semop,#topeop,#pautop,#dniop,#afrop,#confgen").hide();
		
		if(typeof nuevoObjeto != "undefined"){
			if(confirm("Si cambias de objeto, los datos ya generados no se guardaran, estas seguro que deseas cambiar?")){
				nuevoObjeto.limpiarCapas();
				nuevoObjeto = null;
			}
		}
		
		if(estaProponiendo){		
			if($(this).val() == "semaforo"){
				//va a proponer un semaforo
				nuevoObjeto = new ObjetoTrafico("semaforo");
				$("#semop").show();
				//$("#carriles").show();			
			}
			else if($(this).val() == "tope"){
				//va a proponer un tope
				nuevoObjeto = new ObjetoTrafico("tope");
				$("#topeop").show();
				//$("#carrilest").show();					
			}
			else if($(this).val() == "pauto"){
				//va a proponer una parada de autobus
				nuevoObjeto = new ObjetoTrafico("pauto");
				$("#pautop").show();
				//$("#carrilespa").show();				
			}
			else if($(this).val() == "desnivel"){
				//va a proponer un desnivel.
				nuevoObjeto = new ObjetoTrafico("desnivel");
				$("#dniop").show();
			}
			else if($(this).val() == "aforo"){
				nuevoObjeto = new ObjetoTrafico("aforo");
				$("#afrop").show();
			}
			else if($(this).val() == "generico"){
				nuevoObjeto = new ObjetoTrafico("generico");
				$("#confgen").show();
			}
			else{
				alert("No has elegido el tipo de propuesta.");
			}
		}
		else{
			alert("No has empezado a proponer");
		}
	});
	
	$("#trazsal").bind("click",function(){ nuevoObjeto.agregarDireccionAforo(this); });
	$("#gaf,#gs,#gdn,#gog,#gt,#gpa").bind("click",function(){ nuevoObjeto.guardarObjeto(); })
	$("#otraconf").bind("click",function(){ nuevoObjeto.nuevaConfiguracion(this); });	
	//$("#elc").bind("click",function(){eliminarchk();});
	/*$("#proponer").bind("click",function(){user})*/
function gduser(c){
	//alert("si entra");
		if($(c).val()=="Iniciar"){
		user();
		activarInteraccionUsuario($("#proponer"));
	}
		else {
			activarInteraccionUsuario($("#proponer"));
		}
		//$.post("peticiones.php","action=guardarPropuesta&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(p){});
}
	
	function user(){
	var enviarUsuario = {
			"action" : "guardarUsuario",
			"p_user" : {
				"name" : user = $("#username").val(),
				"email" : email= $("#usermail").val()
							}
				};

	$.post("peticiones.php",enviarUsuario);
	$("#duser").hide();
}
}
/*function proposal(){
	alert("entra");
		//var caja = mapa.getExtent().toArray(true);
		$.post("peticiones.php","action=guardarPropuesta&norte="+caja[2]+"&sur="+caja[0]+"&este="+caja[3]+"&oeste="+caja[1]+"",function(p){
		
}
}
}
*/
		function cerrardu(){
			div = document.getElementById('duser');
			div.style.display='none';
			}
