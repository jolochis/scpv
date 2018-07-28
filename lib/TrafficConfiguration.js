function TrafficConfiguration(){	
	this.states={};
	this.attributes=[];
	this.init = "0";
	this.end = "1";
	
	this.addAttribute  = function(value){
		this.attributes.push(value);
	};
	
	this.getAttributes = function(){
		var attr=[];
		for(key in this.attributes){
			name = "attr"+key;
			attr[name]=this.atrtibutes[key];
		}
		
		return attr;
	};
	
	this.addState = function(name,value){
		this.states[name] = {"attr1":name,"attr2":value};
	};
	
	this.setInit = function (time){
		if(isNaN(time) && time>=0 && time<this.end)
			return false;
		else{
			this.init = time;
			return true;
		}
	};
	
	this.setEnd = function (time){
		if(isNaN(time) && time>0 && time>this.init)
			return false;
		else{
			this.end = time;
			return true;
		}
	};
	
	
	this.getConfiguration = function(){
		return {
			"states":this.states,
			"attributes":this.getAttributes()
		};
	};
	
}
