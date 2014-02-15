<h5 id="titrecarte">Carte</h5>

<div id="map"></div>


<script type="text/javascript">
var map = L.map('map').setView([46, 0.8], 5);
L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png',
 { maxZoom:18,
 
    attribution: ''
}).addTo(map);

<?php echo getMarkerList() ?>

</script>

