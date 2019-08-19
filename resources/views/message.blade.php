<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Vbout</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css', true) }}" />
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.css', true) }}" />

        <script src="{{ asset('js/modernizr-2.8.3.min.js', true) }}"></script>

        <style type="text/css">
            body { padding-top: 50px;  }
            .vbout-logo {
                margin-left: -10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="jumbotron">
                        <img src="https://staging.vbout.com/images/vbout-logo-new.gif" class="img-responsive vbout-logo">
                        <h2>Oops! Something went wrong...</h2>
                        <p class="text-danger">{{ $message }}</p>
                        @if (isset($shop))
                        <p>
                            <a href="https://{{ $shop }}" class="btn btn-primary">Back to shop</a>
                        </p>
                        @endif
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

        <script type="text/javascript" src="{{ asset('js/jquery-1.11.2.min.js', true) }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap.min.js', true) }}"></script>
    </body>
</html>
