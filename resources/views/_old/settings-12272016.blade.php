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

        <script src="../../js/modernizr-2.8.3.min.js"></script>

        <style type="text/css">
            body { padding-top: 50px;  }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Integration Settings</h3>
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
                            <form method="post" action="{{ url('settings') }}/{{ $shop }}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="apiKey">API Key</label>
                                    <input type="text" class="form-control" name="settings[apiKey]" id="apiKey" value="{{ (isset($settings->apiKey)) ? $settings->apiKey : '' }}" required>
                                    <span id="helpBlock" class="help-block">Please provide a valid API Key from your Vbout account.</span>
                                </div>
                                @if (isset($settings->apiKey) && $settings->apiKey !== '')
                                    <div class="form-group">
                                        <label for="customersListId">New Customers List</label>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <select class="form-control" name="settings[customersList][id]" id="customersListId">
                                                    <option value="">Please select a list</option>
                                                    @if ($options)
                                                        @foreach ($options as $id => $opt)
                                                            <option value="{{ $id }}" {{ (isset($settings->customersList->id) && $settings->customersList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                @if (isset($settings->customersList->id) && $settings->customersList->id)
                                                    @if (isset($settings->customersList->fields))
                                                        <input type="hidden" class="customer-list-fields" value="{{ implode(',', $settings->customersList->fields) }}">
                                                    @endif
                                                    <select class="form-control multiselect customer-list-fields" name="settings[customersList][fields][]" multiple="multiple">
                                                        @if (isset($options[$settings->customersList->id]['fields']))
                                                            @foreach ($options[$settings->customersList->id]['fields'] as $fId => $field)
                                                                <!-- Email and Phone can't be synced for now -->
                                                                @if ($field !== 'Email Address' && $field !== 'Phone Number')
                                                                    <option value="{{ $fId }}|{{ $field }}">{{ $field }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="incompletePurchasesListId">Incomplete Purchases List</label>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <select class="form-control" name="settings[incompletePurchasesList][id]" id="incompletePurchasesListId">
                                                    <option value="">Please select a list</option>
                                                    @if ($options)
                                                        @foreach ($options as $id => $opt)
                                                            <option value="{{ $id }}" {{ (isset($settings->incompletePurchasesList->id) && $settings->incompletePurchasesList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                @if (isset($settings->incompletePurchasesList->id) && $settings->incompletePurchasesList->id)
                                                    @if (isset($settings->incompletePurchasesList->fields))
                                                        <input type="hidden" class="incomplete-purchases-list-fields" value="{{ implode(',', $settings->incompletePurchasesList->fields) }}">
                                                    @endif
                                                    <select class="form-control multiselect incomplete-purchases-list-fields" name="settings[incompletePurchasesList][fields][]" multiple="multiple">
                                                        @if (isset($options[$settings->incompletePurchasesList->id]['fields']))
                                                            @foreach ($options[$settings->incompletePurchasesList->id]['fields'] as $fId => $field)
                                                                <!-- Email and Phone can't be synced for now -->
                                                                @if ($field !== 'Email Address' && $field !== 'Phone Number')
                                                                    <option value="{{ $fId }}|{{ $field }}">{{ $field }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="completePurchasesListId">Complete Purchases List</label>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <select class="form-control" name="settings[completePurchasesList][id]" id="completePurchasesListId">
                                                    <option value="">Please select a list</option>
                                                    @if ($options)
                                                        @foreach ($options as $id => $opt)
                                                            <option value="{{ $id }}" {{ (isset($settings->completePurchasesList->id) && $settings->completePurchasesList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                @if (isset($settings->completePurchasesList->id) && $settings->completePurchasesList->id)
                                                    @if (isset($settings->completePurchasesList->fields))
                                                        <input type="hidden" class="complete-purchases-list-fields" value="{{ implode(',', $settings->completePurchasesList->fields) }}">
                                                    @endif
                                                    <select class="form-control multiselect complete-purchases-list-fields" name="settings[completePurchasesList][fields][]" multiple="multiple">
                                                        @if (isset($options[$settings->completePurchasesList->id]['fields']))
                                                            @foreach ($options[$settings->completePurchasesList->id]['fields'] as $fId => $field)
                                                                <!-- Email and Phone can't be synced for now -->
                                                                @if ($field !== 'Email Address' && $field !== 'Phone Number')
                                                                    <option value="{{ $fId }}|{{ $field }}">{{ $field }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="newsLettersListId">Newsletters List</label>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <select class="form-control" name="settings[newsLettersList][id]" id="newsLettersListId">
                                                    <option value="">Please select a list</option>
                                                    @if ($options)
                                                        @foreach ($options as $id => $opt)
                                                            <option value="{{ $id }}" {{ (isset($settings->newsLettersList->id) && $settings->newsLettersList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                @if (isset($settings->newsLettersList->id) && $settings->newsLettersList->id)
                                                    @if (isset($settings->newsLettersList->fields))
                                                        <input type="hidden" class="newsletters-list-fields" value="{{ implode(',', $settings->newsLettersList->fields) }}">
                                                    @endif
                                                    <select class="form-control multiselect newsletters-list-fields" name="settings[newsLettersList][fields][]" multiple="multiple">
                                                        @if (isset($options[$settings->newsLettersList->id]['fields']))
                                                            @foreach ($options[$settings->newsLettersList->id]['fields'] as $fId => $field)
                                                                <!-- Email and Phone can't be synced for now -->
                                                                @if ($field !== 'Email Address' && $field !== 'Phone Number')
                                                                    <option value="{{ $fId }}|{{ $field }}">{{ $field }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

        <script type="text/javascript" src="../../js/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap-multiselect.js"></script>
        <script type="text/javascript">
            $(function () {
                var options = {
                    includeSelectAllOption: true,
                    enableFiltering: false,
                    disableIfEmpty: true,
                    buttonWidth: '100%',
                    nonSelectedText: 'Select fields to sync'
                };

                $('select.multiselect').each(function () {
                    $(this).multiselect(options);
                });

                $('select.customer-list-fields').multiselect('select', ($('input.customer-list-fields').length) ? $('input.customer-list-fields').val().split(',') : '');
                $('select.incomplete-purchases-list-fields').multiselect('select', ($('input.incomplete-purchases-list-fields').length) ? $('input.incomplete-purchases-list-fields').val().split(',') : '');
                $('select.complete-purchases-list-fields').multiselect('select', ($('input.complete-purchases-list-fields').length) ? $('input.complete-purchases-list-fields').val().split(',') : '');
                $('select.newsletters-list-fields').multiselect('select', ($('input.newsletters-list-fields').length) ? $('input.newsletters-list-fields').val().split(',') : '');
            });
        </script>
    </body>
</html>
