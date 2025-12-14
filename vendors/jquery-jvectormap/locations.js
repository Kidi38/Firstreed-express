/*
 * jVector Maps
 */ 

var markers = 
[
/*
{ latLng: [41.50, -87.37], name: 'Chicago' }, 
{ latLng: [32.46, -96.46], name: 'Dallas' }, 
{ latLng: [36.10, -115.12], name: 'Las Vegas' }, 
{ latLng: [34.3, -118.15], name: 'Los Angeles' }, 
{ latLng: [40.43, -74.00], name: 'New York City' }, 
{ latLng: [53.412910, -8.243890], name: 'Ireland' }, 
{ latLng: [19.0760, 72.8777], name: 'Mumbai' }, 
{ latLng: [42.697708, 23.321868], name: 'Sofia, Bulgia' }, 
{ latLng: [40.014986, -105.270546], name: 'Boulder, CO' }
*/
{ latLng: [37.09024, -95.712891], name: 'United States' },
{ latLng: [55.378051, -3.435973], name: 'United Kingdom' },
{ latLng: [23.424076, 53.847818], name: 'United Arab Emirates' },
{ latLng: [33.93911, 67.709953], name: 'Afghanistan' },
{ latLng: [-38.416097, -63.616672], name: 'Argentina' },
{ latLng: [47.516231, 14.550072], name: 'Austria' },
{ latLng: [-25.274398, 133.775136], name: 'Australia' },
{ latLng: [50.503887, 4.469936], name: 'Belgium' },
{ latLng: [-14.235004, -51.92528], name: 'Brazil' },
{ latLng: [56.130366, -106.346771], name: 'Canada' },
{ latLng: [46.818188, 8.227512], name: 'Switzerland' },
{ latLng: [35.86166, 104.195397], name: 'China' },
{ latLng: [35.126413, 33.429859], name: 'Cyprus' },
{ latLng: [51.165691, 10.451526], name: 'Germany' },
{ latLng: [56.26392, 9.501785], name: 'Denmark' },
{ latLng: [26.820553, 30.802498], name: 'Egypt' },
{ latLng: [40.463667, -3.74922], name: 'Spain' },
{ latLng: [46.227638, 2.213749], name: 'France' },
//{ latLng: [7.946527, -1.023194], name: 'Ghana' },
{ latLng: [22.396428, 114.109497], name: 'Hong Kong' },
{ latLng: [-0.789275, 113.921327], name: 'Indonesia' },
{ latLng: [53.41291, -8.24389], name: 'Ireland' },
{ latLng: [20.593684, 78.96288], name: 'India' },
{ latLng: [33.223191, 43.679291], name: 'Iraq' },
{ latLng: [41.87194, 12.56738], name: 'Italy' },
{ latLng: [-0.023559, 37.906193], name: 'Kenya' },
{ latLng: [40.339852, 127.510093], name: 'North Korea' },
{ latLng: [35.907757, 127.766922], name: 'South Korea' },
{ latLng: [23.634501, -102.552784], name: 'Mexico' },
{ latLng: [4.210484, 101.975766], name: 'Malaysia' },
//{ latLng: [9.081999, 8.675277], name: 'Nigeria' },
{ latLng: [12.879721, 121.774017], name: 'Philippines' },
{ latLng: [30.375321, 69.345116], name: 'Pakistan' },
{ latLng: [25.354826, 51.183884], name: 'Qatar' },
{ latLng: [-30.559482, 22.937506], name: 'South Africa' },
{ latLng: [71.706936, -42.604303], name: 'Greenland' },
{ latLng: [6.42375, -66.58973], name: 'Venezuela' },
{ latLng: [23.69781, 120.960515], name: 'Taiwan' },
{ latLng: [61.52401, 105.318756], name: 'Russia' },
{ latLng: [28.033886, 1.659626], name: 'Algeria' },
];

$(function() {
    "use strict";
    var $jvectormapDiv = $('#jvectormap');
    if ($jvectormapDiv.length && $.fn.vectorMap) {
        $jvectormapDiv.vectorMap({
            map: 'world_mill',
            zoomOnScroll: false,
            hoverOpacity: 0.7,
            regionStyle: {
                initial: {
                    fill: '#FCBE32',
                    "fill-opacity": 0.8,
                    "stroke-width": 0,
                },
                hover: {
                    fill: '#cfdcf7',
                    "fill-opacity": 1,
                    cursor: 'pointer'
                },
            },
            markerStyle: {
                initial: {
                    fill: '#FF5F2E',
                    stroke: '#FF5F2E'
                }
            },
            markers: markers,
            backgroundColor: 'white'
        });
    }
});