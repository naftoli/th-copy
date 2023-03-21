<!DOCTYPE html>
<html>
<head>
    <title>World Map</title>
    <!-- Load Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+JQLhRjOErjOiGg6aLw6U7Y6UAWOd1n" crossorigin="anonymous">
    <!-- Load Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.css" integrity="sha512-CHJjh7x+8l4yjKX9n2ShN7bckC/8zS7+mN/aD4p/BwN78wu4x4fyKjgYDsoNhRiZ6CLH8p+znU5m3q6+iwU5jQ==" crossorigin="anonymous" />
    <!-- Load Leaflet JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js" integrity="sha512-jZ7KuW/C8Fv7V+a72jc0KjVoA+Dx8ymvBtq3X9fVZd2QGg7AEeekmE/Y/vBOKyLg8Wx7cEkpTtTn7/PyZ86tWQ==" crossorigin="anonymous"></script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center my-4">World Map</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Continents</h5>
                </div>
                <div class="card-body">
                    <select class="form-control" id="continents">
                        <option value="" selected disabled>Select a continent</option>
                        <option value="africa">Africa</option>
                        <option value="asia">Asia</option>
                        <option value="europe">Europe</option>
                        <option value="north-america">North America</option>
                        <option value="south-america">South America</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-lg-10">
            <div id="map" style="height: 500px;"></div>
        </div>
    </div>
</div>

<!-- Load jQuery and Bootstrap JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper-core.min.js"></
