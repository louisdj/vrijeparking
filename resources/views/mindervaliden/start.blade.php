

<html>

    <head>

        {{--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAXkYYUuZKcnAmMTbzqtRsMeXr0Cvyj7Rg&libraries=places"></script>--}}
        {{--<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>--}}

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Allerta+Stencil">


        <style>
            * {
              box-sizing: border-box;
            }

            .background-image {
              /*background-image: url('/img/parkings/mindervaliden/mindervalidenplaatsen.jpg');*/
              background-image: url('http://transportation.psu.edu/sites/transportation/files/handicapped-parking.jpg');
              background-size: cover;
              display: block;
              filter: blur(5px);
              -webkit-filter: blur(5px);
              height: 100%;
              left: 0;
              position: fixed;
              right: 0;
              z-index: 1;
            }

            .content {
              background: white;
              border-radius: 3px;
              box-shadow: 0 1px 5px rgba(0, 0, 0, 0.25);
              font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
              top: 28%;
              left: 15%;
              right: 15%;
              position: fixed;
              margin-left: 20px;
              margin-right: 20px;
              z-index: 2;
              padding: 0 10px;
              text-align: center;
              border-radius: 20px;
              opacity: 0.9;
            }

            h1 {
                font-weight: bold;
            }

            .form-control {
                height: 50px;
                font-size: 25px;
                text-align: center;
            }
        </style>


    </head>

    <body>

        <div class="background-image"></div>
        <div class="content">

          <form action="/mindervaliden" method="post">

              <p style="padding-top: 5px; margin-bottom: -20px;">
                    <img src="/img/parkings/mindervaliden/mindervalide_icoon.png" width="100px" alt=""/>
              </p>
              <p>
                    <h1 style="color: dodgerblue">Vind parking voor mindervaliden</h1>
                    Vertrek niet langer onvoorbereid naar uw bestemming. Zoek naar <b>{{ $plaatsen }}</b> parkings in Vlaanderen.
                    <br/>
              </p>
              <p>

                    <input name="location" id="searchTextField" type="text" class="form-control" label="Zoeken..." required="" />
                    <input name="coordinates" type="hidden" id="coordinates"  />
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                    {{--<input id="locationTextField" name="location" class="form-control" type="text" placeholder="{{ isset($searchTerm) ? $searchTerm : "Geef uw locatie in" }}" value="">--}}

                    <br/>
              </p>
              <p>
                <button type="submit" class="btn btn-success form-control">Zoeken</button>
              </p>

          </form>

        </div>



        <div style="position: fixed; bottom:0px; z-index: 999; width: 100%;  background-color: #2c3e50; text-align: right; padding: 5px; padding-top: 0px; opacity: 1">
                {{--<h2 style="color:white; float:left; font-weight: bold; padding-bottom: -5px;">{{ $plaatsen }} parkings</h2>--}}

                <h1 style="text-align:right;float:right; color:white; font-weight: bold; padding-right: 30px; font-family: 'Allerta Stencil'">
                      Een realisatie van <strong style="font-weight: bold; color: yellowgreen">Vrijeparking.be</strong>
                </h1>
            </div>

    </body>


    <script>
        function initMap()
        {
            var input = document.getElementById('searchTextField');
            var autocomplete = new google.maps.places.Autocomplete(input);

            google.maps.event.addListener(autocomplete, 'place_changed',
               function() {
                  var place = autocomplete.getPlace();
                  var lat = place.geometry.location.lat();
                  var lng = place.geometry.location.lng();
                  document.getElementById("coordinates").value = lat+","+lng;
               }
            );
        }
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdcNxd6g8V0tyzJK87vZjsRYlnPI7DLRw&libraries=places&callback=initMap" async defer></script>


</html>




