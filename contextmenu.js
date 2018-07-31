/* global ol, ContextMenu */
var view = new ol.View({ center: ol.proj.fromLonLat([-103.4194886, 20.6081778]), zoom: 15 }),
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
    text: 'Elementos',
    icon: 'img/view_list.png',
    items: [
      {
        text: 'paso peatonal',
        icon: 'img/paso.png',
        callback: pasoP
      },

      {
        text: 'Tope',
        icon: 'img/tope.png',
        callback: pinTope
      },

      {
        text: 'Semaforo',
        icon: 'img/semaphore.png',
        callback: pinSemaforo
      },
      {
        text: 'Flujo vehicular',
        icon: 'img/auto.png',
        callback: pinAforo
      },

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
// Funciones pin
// PIN = Colocar elementos el mapa
var contadorArray = 0;
function pinSemaforo(obj) {

  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
    template = 'Semaforo  ({x} | {y})',
    type = 'semaforo',
    iconStyle = new ol.style.Style({
      image: new ol.style.Icon({ scale: .8, src: 'img/semaphore.png' }),
      center: [coord4326],
      zoom: 4,

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
  tiempoSemaforo();
  document.getElementById('tipo').value = type;
  document.getElementById('tipo').innerHTML = type;

  var lat = coord4326[1];
  var lon = coord4326[0];
  document.getElementById('lat').value = lat;
  document.getElementById('lon').value = lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;

}//pinSemaforo

function pasoP(obj) {

  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
    template = 'paso peatonal  ({x} | {y})',
    type = "paso",
    iconStyle = new ol.style.Style({
      image: new ol.style.Icon({ scale: .3, src: 'img/paso.png' }),
      center: [coord4326],
      zoom: 4,

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
  document.getElementById('tipo').value = type;
  document.getElementById('tipo').innerHTML = type;
  var lat = coord4326[1];
  var lon = coord4326[0];
  document.getElementById('lat').value = lat;
  document.getElementById('lon').value = lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;


}//paso peatonal


function pinAforo(obj) {

  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
    template = 'Flujo vehicular  ({x} | {y})',
    type = "aforos"
  iconStyle = new ol.style.Style({
    image: new ol.style.Icon({ scale: .2, src: 'img/auto.png' }),
    center: [coord4326],
    zoom: 4,

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

  aforos();
  document.getElementById('tipo').value = type;
  document.getElementById('tipo').innerHTML = type;

  var lat = coord4326[1];
  var lon = coord4326[0];

  document.getElementById('lat').value = lat;
  document.getElementById('lon').value = lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;

}//pinAforo
function aforos() {
  var di = document.querySelector('.semaforo');
  var autos = document.createElement('input');
  var tiempo = document.createElement('input');

  autos.setAttribute('placeholder', 'Numero de autos');
  autos.setAttribute('class', 'aforo');
  autos.setAttribute('name', 'autos');

  tiempo.setAttribute('placeholder', 'Tiempo');
  tiempo.setAttribute('class', 'aforo');
  tiempo.setAttribute('name', 'tiempo');

  di.appendChild(autos);
  di.appendChild(tiempo);
}

function pinTope(obj) {

  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
    template = 'tope  ({x} | {y})',
    type = 'tope',
    iconStyle = new ol.style.Style({
      image: new ol.style.Icon({ scale: .2, src: 'img/tope.png' }),
      center: [coord4326],
      zoom: 4,

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
  document.getElementById('tipo').value = type;
  document.getElementById('tipo').innerHTML = type;

  var lat = coord4326[1];
  var lon = coord4326[0];
  document.getElementById('lat').value = lat;
  document.getElementById('lon').value = lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;


}//pinTope


function tiempoSemaforo() {
  var di = document.querySelector('.semaforo');
  var rojo = document.createElement('div');
  var inpRojo = document.createElement('input');
  var inpAmarillo = document.createElement('input');
  var inpVerde = document.createElement('input');
  inpRojo.setAttribute('placeholder', 'rojo');
  inpRojo.setAttribute('class', 'inp');
  inpRojo.setAttribute('name', 'rojo');
  inpRojo.setAttribute('required', 'true');


  inpAmarillo.setAttribute('placeholder', 'amarillo');
  inpAmarillo.setAttribute('class', 'inp');
  inpAmarillo.setAttribute('name', 'amarillo');

  inpVerde.setAttribute('placeholder', 'verde');
  inpVerde.setAttribute('class', 'inp');
  inpVerde.setAttribute('name', 'verde');
  rojo.setAttribute('id', 'rojo');
  rojo.setAttribute('class', 'r');

  di.appendChild(rojo);
  rojo.appendChild(inpRojo);

  var amarillo = document.createElement('div');
  amarillo.setAttribute('id', 'amarillo');
  amarillo.setAttribute('class', 'r');
  di.appendChild(amarillo);
  amarillo.appendChild(inpAmarillo);

  var verde = document.createElement('div');
  verde.setAttribute('id', 'verde');
  verde.setAttribute('class', 'r');
  di.appendChild(verde);
  verde.appendChild(inpVerde);

}
function removeMarker(obj) {
  vectorLayer.getSource().removeFeature(obj.data.marker);
}
// Funciones para los PINS 


function marker(obj) {
  var coord4326 = ol.proj.transform(obj.coordinate, 'EPSG:3857', 'EPSG:4326'),
    template = 'Coordinate is ({x} | {y})',
    iconStyle = new ol.style.Style({
      image: new ol.style.Icon({ scale: .6, src: 'img/pin_drop.png' }),
      center: [coord4326],
      zoom: 4,

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
  document.getElementById('lat').value = lat;
  document.getElementById('lon').value = lon;
  document.getElementById("lat").innerHTML = lat;
  document.getElementById("lon").innerHTML = lon;



}//marcador

(function () {

  document.addEventListener('DOMContentLoaded', function () {

    var divform = document.getElementById('divform');

    opcion = document.getElementsByTagName('option');
    //opcion = addEventListener('change', valores);

    /*
       var types = [];
   
   
       function pushea(type) {
         types.push(type);
         var arra = JSON.stringify(types);
         document.getElementById('tipo').value = arra;
         console.log(arra)
       }//pushea
       // Empieza funcion para enviar datos
      
       
        var enviar = document.getElementsByName('submit');
        var formulario = document.getElementById('formulario');
        var descripcion = document.getElementById('inpDescripcion');
        var action = formulario.getAttribute('action');
    
    
        var form_datos = new FormData(formulario);
        for ([key, value] of form_datos.entries()) {
          console.log(key + ": " + value);
        }
     
        function valores() {
          var static = document.getElementById('estatico').value;
    
            var di = document.querySelector('.semaforo');
            var rojo = document.createElement('div');
            var inpRojo = document.createElement('input');
            var inpAmarillo = document.createElement('input');
            var inpVerde = document.createElement('input');
            inpRojo.setAttribute('placeholder', 'rojo');
            inpRojo.setAttribute('class', 'inp');
            inpRojo.setAttribute('name', 'rojo');
            inpRojo.setAttribute('required', 'true');
    
    
            inpAmarillo.setAttribute('placeholder', 'amarillo');
            inpAmarillo.setAttribute('class', 'inp');
            inpAmarillo.setAttribute('name', 'amarillo');
    
            inpVerde.setAttribute('placeholder', 'verde');
            inpVerde.setAttribute('class', 'inp');
            inpVerde.setAttribute('name', 'verde');
            rojo.setAttribute('id', 'rojo');
            rojo.setAttribute('class', 'r');
    
            di.appendChild(rojo);
            rojo.appendChild(inpRojo);
    
    
    
            var amarillo = document.createElement('div');
            amarillo.setAttribute('id', 'amarillo');
            amarillo.setAttribute('class', 'r');
            di.appendChild(amarillo);
            amarillo.appendChild(inpAmarillo);
    
            var verde = document.createElement('div');
            verde.setAttribute('id', 'verde');
            verde.setAttribute('class', 'r');
            di.appendChild(verde);
            verde.appendChild(inpVerde);
   
            var list = document.getElementsByClassName("r");
            for (var i = list.length - 1; 0 <= i; i--)
              if (list[i] && list[i].parentElement)
                list[i].parentElement.removeChild(list[i]);
    
   
            var di = document.querySelector('.semaforo');
            var autos = document.createElement('input');
            var tiempo = document.createElement('input');
    
            autos.setAttribute('placeholder', 'Numero de autos');
            autos.setAttribute('class', 'aforo');
            autos.setAttribute('name', 'autos');
    
            tiempo.setAttribute('placeholder', 'Tiempo');
            tiempo.setAttribute('class', 'aforo');
            tiempo.setAttribute('name', 'tiempo');
    
            di.appendChild(autos);
            di.appendChild(tiempo);
         
    
            var list = document.getElementsByClassName("aforo");
            for (var i = list.length - 1; 0 <= i; i--)
              if (list[i] && list[i].parentElement)
                list[i].parentElement.removeChild(list[i]);
           
        } //valores
    */

  });






})();
