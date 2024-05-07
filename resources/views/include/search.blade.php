@php
$search_query = $query ?? '';

$current_url = Request::url();
@endphp
<div class="col-auto mt-1 mb-1">
    <div class="form-group">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <div class="input-group-text p-0 pl-2">
                    <div class="custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input live-search" id="live_search_check" aria-label="Live Search" title="Live Search">
                        <label class="custom-control-label" for="live_search_check" title="Live Search"></label>
                    </div>
                </div>
            </div>

            {!! Form::input('search', 'query', $search_query, ['class' => 'form-control', 'id'=>'query', 'placeholder'=>__('admin.text_search')]); !!}

            <span class="input-group-append">
                <button class="btn btn-dark" name="btn_search" id="btn_search"><i class="fas fa-search"></i></button>
            </span>
        </div>
    </div>
</div>

<script type="text/javascript">
function search_query(){
    var $query_val = $("#query").val();

    document.location.href = "{!! $current_url.'?query=' !!}"+$query_val;
}

function live_search_function(){
    var $target_selector = $(".main-listing-table tbody");
    if($("#live_search_check").prop('checked')){
        $target_selector.children('tr').hide();

        var tableRow = $target_selector.find("td").filter(function() {
            var $this = $(this).text();
            var $partial = $("#query").val();
            return $this.toLowerCase().indexOf($partial.toLowerCase()) > -1
            //return $(this).text() == $("#query").val();
        }).closest("tr").show();
    } else{
        $target_selector.children('tr').show();
    }
}

$(function(){
    $("#query").keypress(function(e){
        if(e.which == 13){
            e.preventDefault();
            search_query();
        }
    });

    $("#btn_search").click(function(e){
        e.preventDefault();
        search_query();
    });

    $("#live_search_check").click(function(){
        live_search_function();
    });

    $("#query").on('search', function(){
        live_search_function();
    });

    $("#query").keyup(function(){
        live_search_function();
    });
});
</script>