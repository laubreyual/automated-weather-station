// Prepare demo data
// Data is joined to map using value of 'hc-key' property by default.
// See API docs for 'joinBy' for more info on linking data and map.

if( $("#mapcontainer").length > 0 ) {
    // get data
    var data = []; 
    var markerData = []; 

    $.ajax({
      dataType: 'json',
      url: BASE+'/mapJSON',
      success: function (readings) {
        console.log(readings);
        readings.forEach(function(reading){
            console.log(reading);
            data.push({
                lat: reading.latitude,
                lon: reading.longitude,
                observation_time: reading.observation_time,
                station_name: reading.station_name,
                rain: reading.rain,
                temperature: reading.temperature,
                wind_direction: reading.wind_direction,
                wind_speed: reading.wind_speed,
                solar_radiation: reading.solar_radiation
            });
            markerData.push({
                name: reading.station_name,
                lat: reading.latitude,
                lon: reading.longitude
            })
            console.log(markerData)
        }) 
      },
      error: Meteogram.prototype.error
    });


    // Create the chart
    Highcharts.mapChart('mapcontainer', {
        chart: {
            map: 'countries/ph/ph-all'
        },

        title: {
            text: 'Weather Stations in the Philippines'
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
                            <b>Precipitation<b>: {point.rain} <br>'
        },

        colorAxis: {
            min: 0
        },

        series: [{
            data: data,
            name: 'Temperature',
            states: {
                hover: {
                    color: '#BADA55'
                }
            },
            dataLabels: {
                enabled: true,
                format: '{point.name}'
            }
        }, {
            
            // Specify points using lat/lon

            type: 'mappoint',
            name: 'Cities',
            color: Highcharts.getOptions().colors[8],
            data: markerData
            // data: [{
            //     name: 'Los Banos',
            //     lat: 14.1699,
            //     lon: 121.2441
            // }]
        }]
    });
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
}