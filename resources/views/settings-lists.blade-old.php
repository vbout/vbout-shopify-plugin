<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Customers List</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[customersList][id]" id="customersListId" >
                    <option value="">Please select a list</option>
                    @if ($options)
                        @foreach ($options as $id => $opt)
                            <option value="{{ $id }}" {{ (isset($settings->customersList->id) && $settings->customersList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <a href="https://www.vbout.com/email-marketing/lists" target="_blank">Create List</a>
            </div>
        </div>
    </div>
</div>
@if (isset($settings->customersList->id) && $settings->customersList->id!=='')
<div class="row custom-fields-wrapper">
    @if (count($shopifyFields) > 0)
        @php ($i = 0)
        @foreach ($shopifyFields as $field)
            <div class="custom-fields-content">
                <div class="col-md-6">
                    {{ ucwords(str_replace('_', ' ', $field)) }}
                </div>
                <div class="col-md-6">
                    <select class="form-control" name="settings[customersList][fields][]">
                        <option value="">Please select a field</option>
                        @foreach ($options[$settings->customersList->id]['fields'] as $fId => $field)
                            <!-- Default email and phone number can't be synced because they're not custom fields -->
                            @if ($field !== 'Email Address')
                                <option value="{{ $fId }}|{{ $field }}" {{ (isset($settings->customersList->fields[$i]) && $settings->customersList->fields[$i] == $fId . '|' . $field) ? 'selected' : '' }}>{{ $field }}</option>
                            @endif
                        @endforeach
                    </select>
                </div> 
            </div>
            @php ($i++)
        @endforeach
        <div class="col-md-12">
            <div class="toggle-link text-center"><small><a href="javascript:void(0);">Hide/Show Fields</a></small></div>
        </div>
        <div class="col-md-12">
            <div class="checkbox sync-customers">
                <label>
                    <input type="checkbox" value="1" name="sync">
                    Sync Current Customers
                </label>
            </div>
        </div>
    @endif
</div>
@endif

<hr>

<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Incomplete Purchases</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[incompletePurchasesList][id]" id="incompletePurchasesListId" >
                    <option value="">Please select a list</option>
                    @if ($options)
                        @foreach ($options as $id => $opt)
                            <option value="{{ $id }}" {{ (isset($settings->incompletePurchasesList->id) && $settings->incompletePurchasesList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <a href="https://www.vbout.com/email-marketing/lists" target="_blank">Create List</a>
            </div>
        </div>
    </div>
</div>
@if (isset($settings->incompletePurchasesList->id) && $settings->incompletePurchasesList->id!=='')
<div class="row custom-fields-wrapper">
    @if (count($shopifyFields) > 0)
        @php ($i = 0)
        @foreach ($shopifyFields as $field)
            <div class="custom-fields-content">
                <div class="col-md-6">
                    {{ ucwords(str_replace('_', ' ', $field)) }}
                </div>
                <div class="col-md-6">
                    <select class="form-control" name="settings[incompletePurchasesList][fields][]">
                        <option value="">Please select a field</option>
                        @foreach ($options[$settings->incompletePurchasesList->id]['fields'] as $fId => $field)
                            <!-- Email can't be synced for now -->
                            @if ($field !== 'Email Address')
                                <option value="{{ $fId }}|{{ $field }}" {{ (isset($settings->incompletePurchasesList->fields[$i]) && $settings->incompletePurchasesList->fields[$i] == $fId . '|' . $field) ? 'selected' : '' }}>{{ $field }}</option>
                            @endif
                        @endforeach
                    </select>
                </div> 
            </div>
            @php ($i++)
        @endforeach
        <div class="col-md-12">
            <div class="toggle-link text-center"><small><a href="javascript:void(0);">Hide/Show Fields</a></small></div>
        </div>
    @endif
</div>
@endif
<hr>

<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Complete Purchases</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[completePurchasesList][id]" id="completePurchasesListId" >
                    <option value="">Please select a list</option>
                    @if ($options)
                        @foreach ($options as $id => $opt)
                            <option value="{{ $id }}" {{ (isset($settings->completePurchasesList->id) && $settings->completePurchasesList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <a href="https://www.vbout.com/email-marketing/lists" target="_blank">Create List</a>
            </div>
        </div>
    </div>
</div>
@if (isset($settings->completePurchasesList->id) && $settings->completePurchasesList->id!=='')
<div class="row custom-fields-wrapper">
    @if (count($shopifyFields) > 0)
        @php ($i = 0)
        @foreach ($shopifyFields as $field)
            <div class="custom-fields-content">
                <div class="col-md-6">
                    {{ ucwords(str_replace('_', ' ', $field)) }}
                </div>
                <div class="col-md-6">
                    <select class="form-control" name="settings[completePurchasesList][fields][]">
                        <option value="">Please select a field</option>
                        @foreach ($options[$settings->completePurchasesList->id]['fields'] as $fId => $field)
                            <!-- Email can't be synced for now -->
                            @if ($field !== 'Email Address')
                                <option value="{{ $fId }}|{{ $field }}" {{ (isset($settings->completePurchasesList->fields[$i]) && $settings->completePurchasesList->fields[$i] == $fId . '|' . $field) ? 'selected' : '' }}>{{ $field }}</option>
                            @endif
                        @endforeach
                    </select>
                </div> 
            </div>
            @php ($i++)
        @endforeach
        <div class="col-md-12">
            <div class="toggle-link text-center"><small><a href="javascript:void(0);">Hide/Show Fields</a></small></div>
        </div>
    @endif
</div>

@endif
<hr>

<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Newsletter Signup</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[newsLettersList][id]" id="newsLettersListId" >
                    <option value="">Please select a list</option>
                    @if ($options)
                        @foreach ($options as $id => $opt)
                            <option value="{{ $id }}" {{ (isset($settings->newsLettersList->id) && $settings->newsLettersList->id == $id) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <a href="https://www.vbout.com/email-marketing/lists" target="_blank">Create List</a>
            </div>
        </div>
    </div>
</div>
@if (isset($settings->newsLettersList->id) && $settings->newsLettersList->id!=='')
<div class="row custom-fields-wrapper">
    @if (count($shopifyFields) > 0)
        @php ($i = 0)
        @foreach ($shopifyFields as $field)
            <div class="custom-fields-content">
                <div class="col-md-6">
                    {{ ucwords(str_replace('_', ' ', $field)) }}
                </div>
                <div class="col-md-6">
                    <select class="form-control" name="settings[newsLettersList][fields][]">
                        <option value="">Please select a field</option>
                        @foreach ($options[$settings->newsLettersList->id]['fields'] as $fId => $field)
                            <!-- Email can't be synced for now -->
                            @if ($field !== 'Email Address')
                                <option value="{{ $fId }}|{{ $field }}" {{ (isset($settings->newsLettersList->fields[$i]) && $settings->newsLettersList->fields[$i] == $fId . '|' . $field) ? 'selected' : '' }}>{{ $field }}</option>
                            @endif
                        @endforeach
                    </select>
                </div> 
            </div>
            @php ($i++)
        @endforeach
        <div class="col-md-12">
            <div class="toggle-link text-center"><small><a href="javascript:void(0);">Hide/Show Fields</a></small></div>
        </div>
    @endif
</div>
@endif