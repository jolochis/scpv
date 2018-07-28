/* global ol, ContextMenu */
var view = new ol.View({ center:ol.proj.fromLonLat([-103.4194886, 20.6081778]), zoom: 12 }),
    vectorLayer = new ol.layer.Vector({ source: new ol.source.Vector() }),
    baseLayer = new ol.layer.Tile({ source: new ol.source.OSM() }),
    map = new ol.Map({
      target: 'map',
      view: view,
      layers: [baseLayer, vectorLayer]
    });

var contextmenu_items = [
  {
    text: 'Center map here',
    classname: 'bold',
    icon: 'img/center.png',
    callback: center
  },
  {
    text: 'Some Actions',
    icon: 'img/view_list.png',
    items: [
      {
        text: 'Center map here',
        icon: 'img/center.png',
        callback: center
      },
      {
        text: 'Add a Marker',
        icon: 'img/pin_drop.png',
        callback: marker
      }
    ]
  },
  {
    text: 'Add a Marker',
    icon: 'img/pin_drop.png',
    callback: marker
  },
 
  '-' // this is a separator
];


var removeMarkerItem = {
  text: 'Remove this Marker',
  classname: 'marker',
  callback: removeMarker
};

var contextmenu = new ContextMenu({
  width: 180,
  items: contextmenu_items
});
map.addControl(contextmenu);


contextmenu.on('open', function (evt) {
  var feature = map.forEachFeatureAtPixel(evt.pixel, function (ft, l) {
    return ft;
  });
  if (feature && feature.get('type') === 'removable') {
    contextmenu.clear();
    removeMarkerItem.data = {
      marker: feature
    };
    contextmenu.push(removeMarkerItem);
  } else {
    contextmenu.clear();
    contextmenu.extend(contextmenu_items);
    contextmenu.extend(contextmenu.getDefaultItems());
  }
});

map.on('pointermove', function (e) {

  var pixel = map.getEventPixel(e.originalEvent);
  var hit = map.hasFeatureAtPixel(pixel);

  if (e.dragging) return;

  map.getTargetElement().style.cursor = hit ? 'pointer' : '';
});

// from https://github.com/DmitryBaranovskiy/raphael
function elastic(t) {
  return Math.pow(2, -10 * t) * Math.sin((t - 0.075) * (2 * Math.PI) / 0.3) + 1;
}

function center(obj) {
  view.animate({
    duration: 700,
    easing: elastic,
    center: obj.coordinate
  });
}

function removeMarker(obj) {
  vectorLayer.getSource().removeFeature(obj.data.marker);
}

function marker(obj) {
  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
      template = 'Coordinate is ({x} | {y})',
      iconStyle = new ol.style.Style({
        image: new ol.style.Icon({ scale: .6, src: 'img/pin_drop.png' }),
        text: new ol.style.Text({
          offsetY: 25,
          text: ol.coordinate.format(coord4326, template, 2),
          font: '15px Open Sans,sans-serif',
          fill: new ol.style.Fill({ color: '#111' }),
          stroke: new ol.style.Stroke({ color: '#eee', width: 2 })
        })
      }),
      feature = new ol.Feature({
        type: 'removable',
        geometry: new ol.geom.Point(obj.coordinate)
      });

  feature.setStyle(iconStyle);
  vectorLayer.getSource().addFeature(feature);
  var lat = coord4326[1];
  var lon = coord4326[0];
  
  //console.log(lat);
  //console.log(lon);
  document.getElementById('lat').value=lat;
  document.getElementById('lon').value=lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;
     
      
 if(!coord4326==''){
  var myJson = new Object();
  myJson.lat = lat;
  myJson.lon = lon;
  var str= JSON.stringify(myJson);
  console.log(JSON.parse(str));
 }
    


}//Marker
/*
var test = document.getElementById('test');
test.addEventListener('click',(e)=>{
  e.preventDefault();
  var estatic = document.getElementById('estatico').value;
  console.log(estatic.value);
  if(estatic===6){
    alert("assaas");
    console.log("semaforo");
  }
} );*/



(function(){
  document.addEventListener('DOMContentLoaded',function(){
  
    dis();
 

     opcion = document.getElementsByTagName('option');
     opcion = addEventListener('change',valores);

     var contadorA = 0;
     var contadorS =0
function valores(){
 
var static = document.getElementById('estatico').value;

if(static==6 && contadorS <1){

  var di = document.querySelector('.semaforo');
  var rojo = document.createElement('div');
  var inpRojo = document.createElement('input');
  var inpAmarillo = document.createElement('input');
  var inpVerde = document.createElement('input');
  inpRojo.setAttribute('placeholder','rojo');
  inpRojo.setAttribute('class','inp');
  inpRojo.setAttribute('name','rojo');
  inpRojo.setAttribute('required','true');
  

  inpAmarillo.setAttribute('placeholder','amarillo');
  inpAmarillo.setAttribute('class','inp');
  inpAmarillo.setAttribute('name','amarillo');

  inpVerde.setAttribute('placeholder','verde');
  inpVerde.setAttribute('class','inp');
  inpVerde.setAttribute('name','verde');
  rojo.setAttribute('id','rojo');
  rojo.setAttribute('class' ,'r');
  
  di.appendChild(rojo);
  rojo.appendChild(inpRojo);
 

 
  var amarillo = document.createElement('div');
  amarillo.setAttribute('id','amarillo');
  amarillo.setAttribute('class' ,'r');
  di.appendChild(amarillo);
  amarillo.appendChild(inpAmarillo);

  var verde = document.createElement('div');
  verde.setAttribute('id','verde');
  verde.setAttribute('class' ,'r');
  di.appendChild(verde);
  verde.appendChild(inpVerde);

  contadorS ++;
  
}

else if(static!=6 ){
  contadorS =0;
  var list = document.getElementsByClassName("r");
  for(var i = list.length - 1; 0 <= i; i--)
  if(list[i] && list[i].parentElement)
  list[i].parentElement.removeChild(list[i]);


 
 
}
if(static==7 && contadorA<1){
  var di = document.querySelector('.semaforo');
  var autos  = document.createElement('input');
  var tiempo = document.createElement('input');

  autos.setAttribute('placeholder','Numero de autos');
  autos.setAttribute('class','aforo');
  autos.setAttribute('name','autos');

  tiempo.setAttribute('placeholder','Tiempo');
  tiempo.setAttribute('class','aforo');
  tiempo.setAttribute('name','tiempo');

  di.appendChild(autos);
  di.appendChild(tiempo);
  contadorA ++;
  
}
if( static!=7){ 
  contadorA =0;
  var list = document.getElementsByClassName("aforo");
  for(var i = list.length - 1; 0 <= i; i--)
  if(list[i] && list[i].parentElement)
  list[i].parentElement.removeChild(list[i]);
}


} //valores

// Empieza funcion para enviar datos

var enviar =document.getElementById('submit');
var formulario = document.getElementById('formulario');
var descripcion = document.getElementById('inpDescripcion');
var action =formulario.getAttribute('action');

enviar.addEventListener('click', function(e){
  //e.preventDefault();
  var form_datos = new FormData(formulario);
  for([key, value] of form_datos.entries()){
      console.log(key + ": " + value);
}
});


   
    
    /*
    var xhr = new XMLHttpRequest();
    xhr.open("POST",action, "scpv.json");
    xhr.setRequestHeader('X-Requested-with', 'XMLHttpRequest');
    xhr.onreadystatechange = function(){
      if(xhr.readyState==4 && xhr.status ==200){
        var resultado = xhr.responseText;
        
      }
    }
    */





inpdes = document.getElementById('descripcion');
inpdes.addEventListener('change',dis);
function dis(){
  var desc = document.getElementById('descripcion').value;
  //leeyendo colores
  var v = document.getElementById('verde');
  var r = document.getElementById('rojo');
  var a = document.getElementById('amarillo');
  var opciones = document.getElementById('estatico').value;
  var inpDesc = document.getElementById('submit');
  
  if(desc == '' ){
  
    inpDesc.setAttribute('disabled','true');
  }else{

    inpDesc.removeAttribute('disabled');
  }
}
  });



})();
