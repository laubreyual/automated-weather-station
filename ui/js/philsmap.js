// Prepare demo data
// Data is joined to map using value of 'hc-key' property by default.
// See API docs for 'joinBy' for more info on linking data and map.

// Highcharts.getOptions().colors
// 0: "#7cb5ec" - blue
// 1: "#434348" - black
// 2: "#90ed7d" - green
// 3: "#f7a35c" - orange
// 4: "#8085e9" - violet
// 5: "#f15c80" - pink
// 6: "#e4d354" - yellow
// 7: "#2b908f" - dark green
// 8: "#f45b5b" - red
// 9: "#91e8e1" - blue green

function Map(data, zmetric) {
    // Create the chart
    var titleText;
    var mapData = Highcharts.maps['countries/ph/ph-all'];
    if(zmetric == "temp" || zmetric == null) titleText = "Temperature readings of weather stations in the Philippines";
    else if(zmetric == "rain") titleText = "Precipitation readings of weather stations in the Philippines";
    else if(zmetric == "speed") titleText = "Wind speed readings of weather stations in the Philippines";
    else if(zmetric == "rad") titleText = "Solar radiation readings of weather stations in the Philippines";

    Highcharts.mapChart('mapcontainer', {
        chart: {
            map: 'countries/ph/ph-all'
        },

        title: {
            text: titleText
        },

        subtitle: {
            text: 'Source map: <a href="http://code.highcharts.com/mapdata/countries/ph/ph-all.js">Philippines</a>'
        },

        mapNavigation: {
            enabled: true,
            buttonOptions: {
                verticalAlign: 'bottom'
            }
        },
        tooltip: {
            headerFormat: '',
            pointFormat: '<b>{point.station_name}</b><br> \
                            <b>Temperature</b>: {point.temperature} <br> \
                            <b>Precipitation</b>: {point.precipitation} <br> \
                            <b>Wind Speed</b>: {point.wind_speed} <br> \
                            <b>Wind Direction</b>: {point.wind_direction} <br> \
                            <b>Solar Radiation</b>: {point.solar_radiation} <br>'
        },

        series: [
        {
            name: 'Provinces',
            mapData: mapData,
            enableMouseTracking: false
        },{
            type: 'mapbubble',
            mapData: mapData,
            name: 'Weather Stations',
            data: data,
                joinBy: ['postal-code', 'code'],
            minSize: 4,
            maxSize: '12%',
            color: Highcharts.getOptions().colors[8]
        }]
    });
}



function initMap(zmetric = null) {
    var data = []; 
    var markerData = []; 
    var min = 999, max = 0;

    $.ajax({
      dataType: 'json',
      url: BASE+'/mapJSON',
      success: function (readings) { 
        readings.forEach(function(reading){
            var input = {
                
                lat: reading.latitude,
                lon: reading.longitude,
                observation_time: reading.observation_time,
                station_name: reading.station_name,
                temperature: reading.temperature,
                precipitation: reading.rain,
                wind_direction: reading.wind_direction,
                wind_speed: reading.wind_speed,
                solar_radiation: reading.solar_radiation
            }

            if(zmetric == "temp" || zmetric == null) input.z = reading.temperature;
            else if(zmetric == "rain") input.z = reading.rain;
            else if(zmetric == "speed") input.z = reading.wind_speed;
            else if(zmetric == "rad") input.z = reading.solar_radiation;

            data.push(input);

            if(input.z > max) max = input.z;
            if(input.z < min) min = input.z;
        }) 
        // console.log(max);
        // console.log(min);
        data.forEach(function(reading) {
            if(reading.z == null) reading.z = 0;
            if(max != min) reading.z = ((reading.z - min) / (max-min)*1.0); 
            // console.log(reading.z);
        })
        highcharts = new Map(data, zmetric);
      }
    });
}

if( $("#mapcontainer").length > 0 ) {
    // get data
    initMap();
    metric.onchange = function(){
        zmetric = metric.value;
        initMap(zmetric);
    }
}
