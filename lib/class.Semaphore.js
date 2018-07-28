function Semaphore(idprop,iduser){
	TrafficObject.call(this,idprop,iduser);
	
	
	Semaphore.prototype.setPoint = function(point){
		TrafficObject.properties._points = point.x+" "+point.y;
	};	
}

Semaphore.prototype = Object.create(TrafficObject.prototype);



