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
                    <form method="post" action="{{ url('settings') }}/{{ $shop }}">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <img src="https://staging.vbout.com/images/vbout-logo-new.gif" class="img-responsive">
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
								
									
								@if (!isset($settings->apiKey) || $settings->apiKey == '')
									<div class="form-group">
										<label>Connect your Vbout account.</label>
										<span class="help-block"><small>If you don't have an account, please sign up <a href="https://www.vbout.com/register" target="_blank">here</a>.</small></span>
										<input type="text" class="form-control" name="settings[apiKey]" style="display:none" id="apiKey" value="{{ (isset($settings->apiKey)) ? $settings->apiKey : '' }}" >
									</div>	
                                @else
									<div class="form-group">
										<label for="apiKey">Your Unique Vbout API User Key</label>
										<span id="helpBlock" class="help-block"><small>Vbout API key can be fetched from Vbout.com<a href="https://www.vbout.com/settings/apikeys" target="_blank"> Settings > API &amp; Plugins</a>. </small></span>
										<input type="text" class="form-control" name="settings[apiKey]" id="apiKey" value="{{ (isset($settings->apiKey)) ? $settings->apiKey : '' }}" required>
									</div>
								
								
									@if (isset($settings->apiKey) && $settings->apiKey !== '')
										<div class="row">
											<div class="col-md-6">
												<h4>SHOPIFY CUSTOMERS</h4>
											</div>
											<div class="col-md-6">
												<h4>VBOUT LISTS</h4>
											</div>
										</div>
										<hr>
										@if (!$isSetupComplete)
											@include('partials.settings-partial')
										@else
											@include('partials.settings-full')
										@endif
									@endif
									
								@endif
                            </div>
                            <div class="panel-footer">
                                @if (!isset($settings->apiKey) || $settings->apiKey == '')
                                    <button type="button" id="vboutconnect" class="btn btn-primary pull-right">Connect Account</button>
									<button type="submit" id="vboutconnectnext" class="btn btn-primary pull-right" style="display:none">Next</button>
                                @elseif (!$isSetupComplete)
									<button type="submit" id="vboutconnectnext" class="btn btn-primary pull-right" >Next</button>
                                @else
                                    <button type="submit" class="btn btn-primary pull-right" style="margin-left: 5px;">Save</button>
                                    <button type="button" class="btn btn-default pull-right" id="reset">Reset</button>
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

            <!--
            <div id="sync-modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="text-center"><strong>Syncing contacts, please wait...</strong></p>
                            <p class="text-center"><small>Do not refresh the page</small></p>
                        </div>
                    </div>
                </div>
            </div>
            -->
			
        </div>

        <script type="text/javascript" src="../../js/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script>
        <!-- <script type="text/javascript" src="../../js/bootstrap-multiselect.js"></script> -->
        <script type="text/javascript">
            $(function () {
				
                // $('#sync-modal').modal({
                //     keyboard: false,
                //     show: false,
                //     backdrop: 'static'
                // });

                // var options = {
                //     includeSelectAllOption: true,
                //     enableFiltering: false,
                //     disableIfEmpty: true,
                //     buttonWidth: '100%',
                //     nonSelectedText: 'Select fields to sync'
                // };

                // $('select.multiselect').each(function () {
                //     $(this).multiselect(options);
                // });

                // $('select.customer-list-fields').multiselect('select', ($('input.customer-list-fields').length) ? $('input.customer-list-fields').val().split(',') : '');
                // $('select.incomplete-purchases-list-fields').multiselect('select', ($('input.incomplete-purchases-list-fields').length) ? $('input.incomplete-purchases-list-fields').val().split(',') : '');
                // $('select.complete-purchases-list-fields').multiselect('select', ($('input.complete-purchases-list-fields').length) ? $('input.complete-purchases-list-fields').val().split(',') : '');
                // $('select.newsletters-list-fields').multiselect('select', ($('input.newsletters-list-fields').length) ? $('input.newsletters-list-fields').val().split(',') : '');

                // Sync Customers
                // $('#sync').on('click', function () {
                //     if (confirm('Sync customer data?')) {
                //         var request = $.ajax({
                //             url: 'customers/sync',
                //             method: 'POST',
                //             dataType: 'json',
                //             data: {
                //                 _token: $('input[name="_token"]').val(),
                //                 apiKey: $('#apiKey').val(),
                //                 shopUrl: $('#shopUrl').val(),
                //                 customersListId: $('#customersListId').val(),
                //                 customersListFields: $('input.customer-list-fields').val()
                //             },
                //             beforeSend: function () {
                //                 $('#sync-modal').modal('show');
                //                 $('#sync').attr('disabled', true);
                //                 $('#sync').text('Syncing...');
                //             }
                //         });

                //         request.done(function (response) {
                //             $('#sync').attr('disabled', false);
                //             $('#sync').text('Sync Customers');
                //             $('#sync-modal').modal('hide');
                //         });
                //     }
                // });

                $('#reset').on('click', function () {
                    $(':input').not('input[type="hidden"]').val('');
                    $(':select').val('');
                });

                $('.toggle-link a').on('click', function () {
                    $(this).parent().parent().parent().parent().find('.custom-fields-content').toggle();
                });
				
				$('#vboutconnect').on('click',function(){
					var myWindow = window.open("https://staging.vbout.com/index.php?r=ApiTest/LoginAccount", "myWindow", "width=400,height=450");
				});
            });
			
			window.addEventListener('message', function(event) { 
				// IMPORTANT: Check the origin of the data! 
				if (~event.origin.indexOf('https://staging.vbout.com')) { 
					// The data has been sent from your site 

					// The data sent with postMessage is stored in event.data 
					alert('apikey : '+event.data); 
					//var key = event.data;								//apikey from staging 
					var key = '3327855131335714334782833'; 				//apikey from live, static value for testing only
					$('#apiKey').val(key);
					$('#vboutconnectnext').trigger('click');
				} else { 
					// The data hasn't been sent from your site! 
					// Be careful! Do not use it. 
					return; 
				} 
			});
        </script>
    </body>
</html>
