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
            body {
                padding-top: 50px;
            }

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
                        <h2>Installation complete!</h2>
                        <p class="text-primary">
                            Thank you for installing Vbout's Shopify app
                        </p>
                        <p>
                            <a href="https://{{ $shop }}/admin/apps/{{ env('SHOPIFY_APP_NAME') }}" class="btn btn-primary">Set up your app</a>
                            <!-- <a href="{{ url('settings') }}/{{ $shop }}?partial=true" class="btn btn-primary">Set up your app</a> -->
                            <a href="https://www.vbout.com/login" class="btn btn-primary">Sign in or register your Vbout account</a>
                        </p>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

        <script type="text/javascript" src="{{ asset('js/jquery-1.11.2.min.js', true) }}"></script>
        <script type="text/javascript" src="{{ asset('js/bootstrap.min.js', true) }}"></script>
    </body>
</html>
