@csrf
@include ('forms.input.text', 
            ["model" => "period", 
            "field" => "title", 
            "placeholder" => "Title",  
            "required" => true, 
            "value" => old('title', isset($period) ? $period->title : '')])

@include ('forms.input.datetime', 
            ["model" => "period", 
            "field" => "begin", 
            "placeholder" => "2019-11-03 13:14:00",  
            "value" => old('date', isset($period) ? $period->begin : '')])
            
@include ('forms.input.datetime', 
            ["model" => "period", 
            "field" => "end", 
            "placeholder" => "2020-11-03 13:15:00",  
            "value" => old('date', isset($period) ? $period->end : '')])

<div>
    <input 
        id="period-save"
        class="btn btn-info" 
        type="submit" 
        value="{{ $buttonText }}">
</div>