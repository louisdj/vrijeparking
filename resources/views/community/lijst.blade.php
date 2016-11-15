@extends('app_community')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Lijst</h2>
                </div>
            </div>
        </div>
    </header>


    <section id="portfolio">
        <div class="container">

        <br/>

            <div class="table-responsive">
              <table class="table table-hover table-condensed table-bordered table-responsive">

                <tr>
                    <th>Rank</th>
                    <th>Naam</th>
                    <th>Toegevoegde parkings</th>
                </tr>

                @foreach($lijst as $key => $user )

                    <tr>
                        <td>{{ $key }}</td>
                        <td>{{ $user->name }}</td>
                        <td>23</td>
                    </tr>

                @endforeach

            </table>
            </div>

        </div>
    </section>

@endsection