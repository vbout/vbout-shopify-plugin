<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Customers List</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[customersList][id]" id="customersListId" required>
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
<hr>
<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Incomplete Purchases</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[incompletePurchasesList][id]" id="incompletePurchasesListId" required>
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
<hr>
<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Complete Purchases</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[completePurchasesList][id]" id="completePurchasesListId" required>
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
<hr>
<div class="row">
    <div class="col-md-6">
        <label><span class="glyphicon glyphicon-play" aria-hidden="true"></span> Shopify Newsletter Signup</label>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-8">
                <select class="form-control" name="settings[newsLettersList][id]" id="newsLettersListId" required>
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