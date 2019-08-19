
     <div class="col-md-12 " style="margin-bottom: 20px">
        <h2 >Vbout Plugin Configuration</h2>
    </div>
    </hr>
    <label style="margin-left:20px">Please Check the functionalities that you'd like to track with our plugin:</label>
        @foreach($listOfSettings as  $settingsKey =>$settingsValue )
                 <div style="margin-left:30px" class = "form-group">
                    <div class="col-md-12">

                         <div class="checkbox">
                               <label><input type="checkbox" @if(($settingsValue) == 1) checked="checked"@endif
                                name = "configurationList[{{$settingsKey}}]" value="1">{{$settingsHeaders[$settingsKey]}}</label>
                        </div>
                    </div>
                </div>
        @endforeach

