@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')

@endsection

@section('scripts')
<script type="text/javascript">
    // $(document).ready(function() {    
    //      $('#brand_ids[data-toggle="select-multiple"]').select2({
    //   allowClear: false,
    //   closeOnSelect: false,
  
    //   templateSelection: function(selected, container) {
    //   // Check if the selection is not a placeholder
    //   if (selected.id !== '') {
    //   // Append the selected option text to the container
    //   $(container).text(selected.text);
    //   }
    //   return container;
    //   }
     
    //      });      
    //  });
     
    $(document).ready(function() {
       // Function to generate series based on input fields
        function generateSeries() {
            var series_name = $('#name').val();
            var number_separator = $('#number_separator').val();
            var str_start_number = $('#start_number').val();
            var str_min_length = $('#min_length').val();

            // var min_length = Math.max(parseInt(str_min_length), str_start_number.length);
            
        var changed_num = String(str_start_number).padStart(str_min_length, '0');

            var result = series_name + number_separator + changed_num;

            return result;
        }

        // Update the result when any of the relevant fields change
        $('#name, #number_separator, #start_number, #min_length').on('change', function() {
            var series = generateSeries();
            $('#generated_series').text("First Number : " + series);
        });

        // Initialize the result when the document is ready
        var initialSeries = generateSeries();
        $('#generated_series').text("First Number : " + initialSeries);
        });

</script>
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
    <div class="row">
        <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <div>{{ $title_shown }} (<span id="generated_series"></span>)</div>
                    <div>
                        <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
                        <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
                    </div>
                </div>

                <div class="card-body">
                   <div class="row">
                        
                        <div class="col-md-4">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsText($current_field, __('Series Name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                         <div class="col-md-4">
                            @php 
                            $current_field = 'number_separator';
                            $row_data = $model->getSeparatorList();
                            @endphp
                            {!! Form::bsSelect($current_field, __('Number Separator'), $row_data, $form_data->$current_field ?? '', ['required', 'data-toggle'=>'select'], ['vertical'=>true]); !!}
                        </div>

                           @php $disablefield = ($mode == 'edit') ? 'disabled' : ''  @endphp
                           <div class="col-md-4">
                            @php $current_field = 'start_number'; @endphp
                            {!! Form::bsInput('number',$current_field, __('Start Number'), $form_data->$current_field ?? 1, ['required', $disablefield, 'min' => 1], ['vertical'=>true]); !!}
                        </div>


                        <div class="col-md-4">
                            @php $current_field = 'min_length'; @endphp
                            {!! Form::bsInput('number',$current_field, __('Minimum Length'), $form_data->$current_field ?? 1, ['required', $disablefield, 'min' => 1, 'max' => 20], ['vertical'=>true]); !!}
                        </div>             
                        
                       

                        <div class="col-md-4">
                            @php 
                            $current_field = 'type';
                            $row_data = $model->getModuleTypeList();
                            @endphp
                            {!! Form::bsSelect($current_field, __('Type'), $row_data, $form_data->$current_field ?? '', ['required','data-toggle'=>'select', $disablefield], ['vertical'=>true]); !!}
                        </div>

                        @php 
                            $auth_user = Auth::user();  
                             $roles = $auth_user->roles()->pluck('name','id')->toArray();
                            if(in_array('1', array_keys($roles))){   
                         @endphp  
                           @if ($mode != 'edit')
                        <div class="col-md-4">
                            @php 
                            $current_field = 'organization_id';
                             $organizations = \App\Models\Organization::pluck('name', 'id');
                            @endphp
                            {!! Form::bsSelect($current_field, __('Organization Name'), $organizations, $form_data->$current_field ?? '', ['required', 'data-toggle'=>'select'], ['vertical'=>true]); !!}
                        </div>
                           @endif
                           @php } @endphp
                       </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection
