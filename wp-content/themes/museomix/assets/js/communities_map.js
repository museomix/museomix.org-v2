// The following example creates complex markers to indicate beaches near
// Sydney, NSW, Australia. Note that the anchor is set to (0,32) to correspond
// to the base of the flagpole.

function initMap() {
	if (!document.getElementById('communities-map'))
		return;
  var map = new google.maps.Map(document.getElementById('communities-map'), {
    zoom: 4,
	styles : [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#dde6e8"},{"visibility":"on"}]}],
    center: {lat: 47, lng: -30},
	scrollwheel: false,
	zoomControl: false,
  });

  setMarkers(map);
}

// Data for the markers consisting of a name, a LatLng and a zIndex for the
// order in which these markers should display on top of each other.

  var markers = [];
  var infowindows = new Array();
    infowindow = new google.maps.InfoWindow({
                content: "loading..."
            });
function setMarkers(map) {
  // Adds markers to the map.

  // Marker sizes are expressed as a Size of X,Y where the origin of the image
  // (0,0) is located in the top left of the image.

  // Origins, anchor positions and coordinates of the marker increase in the X
  // direction to the right and in the Y direction down.
  var image = custom_variables.template_url+'/assets/images/picto_map.png';
  // Shapes define the clickable region of the icon. The type defines an HTML
  // <area> element 'poly' which traces out a polygon as a series of X,Y points.
  // The final coordinate closes the poly by connecting to the first coordinate.
  var shape = {
    coords: [1, 1, 1, 20, 18, 20, 18, 1],
    type: 'poly'
  };
  var image = custom_variables.template_url+'/assets/images/picto_map.png';

  for (var i = 0; i < communities.length; i++) {
		var community = communities[i];
		var siteLatLng = new google.maps.LatLng(community.lat, community.lng);
		var marker = new google.maps.Marker({
			position: siteLatLng,
			map: map,
			title: community.title,
			icon: image,
			//zIndex: sites[3],
			html: community.title + community.link
		});

		var contentString = "Some content";

		google.maps.event.addListener(marker, "click", function () {
			infowindow.setContent(this.html);
			infowindow.open(map, this);
		});
	}
}
function attachInfoWindow(marker, info_content) {
  var infowindow = new google.maps.InfoWindow({
    content: info_content
  });

  marker.addListener('click', function() {
	if (infowindow) {
        infowindow.close();
    }
    infowindow.open(marker.get('map'), marker);
  });
}
initMap();
