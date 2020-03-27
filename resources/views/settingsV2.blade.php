<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="../../css/bootstrap.min.css" />
        <link rel="stylesheet" href="../../css/bootstrap-multiselect.css" />
        <link rel="stylesheet" href="../../css/main.css" />
        <link rel="stylesheet" href="../../css/login.css" />

        <script src="../../js/modernizr-2.8.3.min.js"></script>
        <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
        <script type="text/javascript">
            ShopifyApp.init({
                apiKey: '{{ $shopifyAppApiKey }}',
                shopOrigin: 'https://{{ $shop }}'
            });

            ShopifyApp.ready(function () {
                ShopifyApp.Bar.loadingOff();
            });
        </script>

        <style type="text/css">
            body { padding-top: 20px;  }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
					@if ($isTrial)
						<!--div class="alert alert-warning alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<strong>Warning!</strong> Your 14days Free Trial will expire in {{ $daysLeft }} day(s). <a href="{{ $upgradeUrl }}">Upgrade Now</a>.
						</div-->
					@endif

                    <form method="post" action="{{ url('settings/assign/') }}/{{ $shop }}">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
									@if (!isset($apiKey) || $apiKey == '')
										<img src="../../logo.gif" class="img-responsive">
									@else
										<img src="../../logo.gif" class="img-responsive" style="display: inline-block;">
										<span class="pull-right" style="font-size: 80%;margin-top: 10px;"><a href="javascript:;" id="vboutconnect">[Change Account]</a></span>
									@endif
                                </h3>
                            </div>
                            <div class="panel-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <strong>Success!</strong> {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('warning'))
                                    <div class="alert alert-warning alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <strong>Warning!</strong> {{ session('warning') }}
                                    </div>
                                @endif
                                {{ csrf_field() }}
								<input type="hidden" id="shopUrl" name="shop" value="{{ $shop }}">


								@if (!isset($apiKey) || $apiKey == '')
									<div class="form-group">
										<label>Connect your Vbout account.</label>
										<span class="help-block"><small>If you don't have an account, please sign up <a href="https://www.vbout.com/register" target="_blank">here</a>.</small></span>
									</div>
                                @else
									@if (isset($apiKey) && $apiKey !== '')


 										@include('settings-listV2',compact($listOfSettings,$settingsHeaders))


									@endif

								@endif

                            </div>
                            <div class="panel-footer">
								<input type="hidden" name="settings[apiKey]" id="apiKey" value="{{ (isset($apiKey)) ? $apiKey : '' }}" >
								<input type="hidden" name="settings[userName]" id="apiKeyUserName" value="{{ (isset($userName)) ? $userName : '' }}" >

                                @if (!isset($apiKey) || $apiKey == '')
                                    <button type="button" id="vboutconnect" class="btn btn-primary pull-right">Connect Account</button>
									<button type="submit" id="vboutconnectnext" class="btn btn-primary pull-right" style="display:none">Next</button>
                                @else
                                    <button type="submit" id="vboutconnectnext" class="btn btn-primary pull-right">Save</button>
                                    <button type="button" class="btn btn-default pull-left" id="reset">Reset</button>
                                @endif

                                @if (isset($settings->customersList->id) && $settings->customersList->id !== '')
                                    <!-- <button type="button" class="btn btn-success pull-right" id="sync">Sync Customers</button> -->
                                @endif
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2"></div>
            </div>


            <div id="connect-modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" >
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content" style="width:400px;min-height:450px">
                        <div class="modal-body">
                            <div id="signinBox">
								<h1 class="signIn-title" style="padding-top: 40px;">Sign In to Vbout</h1>

								<p class="signIn-Subtitle">Hey, Welcome Back</p>
								<div class="signIn-form">
									<div class="errorSummary" style="display:none"></div>
									<form id="da-login-form" class="signIn-form-login" method="post" onsubmit="return false;">
										<div class="signIn-input-email">
											<p>Email</p>
											<input type="email" id="username" placeholder="email@company.com" tabindex="1" autocomplete="off" required />
										</div>
										<div class="signIn-input-password">
											<p>Password</p>
											<input type="password" id="password" placeholder="6 characters minimum" tabindex="2" autocomplete="off" required />
										</div>
										<div>
											<button class="signIn-login-button" tabindex="3" id="loginaccount">CONNECT ACCOUNT</button>
										</div>
									</form>
									<div id="login-loader" class="login-loader" style="display: none;"></div>
								</div>
							</div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <script type="text/javascript" src="../../js/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(function () {

                $('#connect-modal').modal({
                    show: false,
                });


                $('#reset').on('click', function () {
                    $( 'input[type="checkbox"]' ).prop('checked', false);
                });

                $('.toggle-link a').on('click', function () {
                    $(this).parent().parent().parent().parent().find('.custom-fields-content').toggle();
                });


				$('#vboutconnect').on('click',function(){
					var uname = $('#apiKeyUserName').val();
					$('#username').val(uname);
					$('#password').val('');
					$('.errorSummary').hide();
					$('#login-loader').hide();
					$('#connect-modal').modal('show');
				});

				$('#loginaccount').on('click',function(){
					$('.errorSummary').hide();
					$('.errorSummary').html('');
					$('#login-loader').show();

					var username = $('#username').val();
					var password = $('#password').val();

					if(username==''){
						$('.errorSummary').html("Username cannot be blank.");
						$('.errorSummary').show();
						$('#login-loader').hide();
					}else if(password==''){
						$('.errorSummary').html("Password cannot be blank.");
						$('.errorSummary').show();
						$('#login-loader').hide();
					}else{
						var postdata =  {
 
							"USERNAME": username,
							"PASSWORD": password
						};
						$.ajax({
							url:"https://api.vbout.com/1/app/loginextended",
							data:postdata,
							type:"POST",
							success: function (res) {
								if(res.response.data.key) {
									var key = res.response.data.key
									$('#apiKey').val(key);
									$('#apiKeyUserName').val(username);
									$('#vboutconnectnext').trigger('click');

								} else {
									$('.errorSummary').html("Your login details are incorrect.");
									$('.errorSummary').show();
									$('#login-loader').hide();
								}
							}
						});
					}
				});
            });

        </script>
    </body>
</html>
