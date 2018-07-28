

function prepareSlider(){
	$(".slider").slider({
		min: 0,
		max: 10,
		step:1
	});
}


$("body #cov-dialog").
	on("show.bs.modal", function(e){
		cov.destroyObjects();
});

function ObjectConfigurationManager(){	
	// conjunto de objetos viales, debe de limpiarse y volverse a llenar al seleccionar una calle
	ObjectConfigurationManager.objects = {"Flow":[],"Sign":[],"Light":[],"Other":[]};
	
	/**
	 * Listener que escucha los clics cobre la pestaña + con la cual crea un nuevo tab tipo objeto con
	 * el atributto "data-type"="new" 
	 */
	this.initListeners = function(){
		$("a[data-type='new']")
		.on("shown.bs.tab", this.newConfiguration);
	};
	
	ObjectConfigurationManager.getObjects = function(){
		return this.objects;
	};

    /**     
     * Crea un nuevo recuadro de configuración dentro del oobjeto que se desea crear la nueva configuración
     * @param {Object} e evento clic sobre el boton "+" de cada dialogo objeto 
     */
	ObjectConfigurationManager.prototype.newConfiguration = function(e){
		e.preventDefault();
		// identifica el tipo de objeto al que se creará una nueva configuraación
		selected = $(".objectSelector li.active a");
		//e.target identifica el recuadro de tipo de tabs al que se agregará el objeto flow, light, other, sign
		ObjectConfigurationManager.prototype.addTab(selected,e.target);
		switch(e.target){
			case "flowtab":
			break;
			
			case "lighttab":
			break;
			
			case "signtab":
			break;
			
			case "othertab":
			break;
		}
	};
	
	/**
	 * Crea un tap(Objeto) y un objeto dentro de acuerdo al objeto seleccionado
	 *  "Flow","Traffic Ligt", "Other" o "Sign"
	 * @param {String} selected tipo de objeto seleccionado
	 * @param {Object} target es el tab en el cual se agregará el objeto
	 *  ver en index.html "flowtab", othertab, lighttab y signtab
	 */
	ObjectConfigurationManager.prototype.addTab = function(selected,target){
		
		var tabSelector = $(target).parent()[0];
		var tabContainer = $(tabSelector).parent().siblings(".tab-content");
		var dataType = $(selected).attr("data-type");
		//<li role="presentation" class="active"><a href="#fo1" role="tab" data-toggle='tab'>Flow <span>1</span></a></li>
		//crea un nuevo objeto vial y lo almacena en el array correspondiente a su tipo de objeto
		// le asigna un identificador, se usará para saber la posición del objeto "data-number" que es el indice del objeto en el cual
		//se almacena la información del objeto, 
		ObjectConfigurationManager.getObjects()[dataType].push(new TrafficObject());
		var n = ObjectConfigurationManager.getObjects()[dataType].length;
		var newTab = $("<li>",{role:'presentation',
			html:$("<a>",{role:'tab','data-toggle':'tab','role':'tab', href:'#'+dataType+n,'data-number':n,
				html:$("<span>",{text:''+dataType+' '+n})
			})
		});
		//<div role="tabpanel" class="tab-pane active" id="fo1">
		$(tabContainer).find(".tab-pane.active").removeClass("active");
		var tabContent = $("<div>",{role:"tabpanel",class:"tab-pane active",id:dataType+n});
		//carga uno de los templates, de acuerdo al tipo de obeto "Proyecto/Templates/<tipo>.html"
		//carousel_item.html puede usarse para crear una nueva configuración dentro del mismo objeto	
		$(tabContent).load("templates/"+dataType+".html",function(){
			$(tabContent).find(".slider").slider();
		});
		$(tabContainer).append(tabContent);
		$(tabSelector).parent().append(newTab);
		$(tabSelector).parent().append($(target).parent());			
			$(newTab).tab('show');
			$(tabContent).tab('show'); 
	};
	
	ObjectConfigurationManager.prototype.initListeningDialog=function(){
		$("body #cov-dialog")
		.on("show.bs.modal", function(e){
			$(".slider").slider({
				min: 0,
				max: 10,
				step:1
			});
		});
	};
	
	ObjectConfigurationManager.prototype.setType = function(type){
		this.type = type;
	};
	
	/*
	 * Cargará las configuraciones, dentro del carousel,
	 */
	ObjectConfigurationManager.prototype.showObjectConfiguration = function(){
		$("#cov-dialog").modal('show');
	};
	
	/**
	 *Cargará la información de cada uno de los estados del objeto vial como una tabal 
	 */
	ObjectConfigurationManager.prototype.loadTable = function(){
		
	};
	
	/**
	 * Agrega una nueva configuración dentro del objeto seleccionado
	 * @param {Object} who Objeto(carousel item) en el que se agregará la nueva configuración   
	 */
	ObjectConfigurationManager.prototype.createConfiguration = function(who){
		//buscar parent
		//cargar template "carousel_item.html"
		//hacer modificaciones pertinentes de acuerdo al tipo de objeto
		// agregar tabla createTableConfiguration()
		// mostrarlo
	};
	
	/**
	 * Creará una nueva tabla de configuración dentro de la configuración creada, sin datos 
	 */
	ObjectConfigurationManager.prototype.createTableConfiguration = function(){
		
	};
	
	/**
	 * Limpia la lista de objetos viales 
	 */
	ObjectConfigurationManager.prototype.destroyObjects = function(){
		for(key in this.objects){
			this.objects[key] = [];
		}
	};
}