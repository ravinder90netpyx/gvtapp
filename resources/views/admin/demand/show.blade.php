@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
@endsection

@section('content')
<div class="app-page-title row">
    <div class="col page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <table class="mb-0 table table-bordered">
                    <tbody>
                        @php $current_field = 'name'; @endphp
                        <tr>
                            <th scope="row" style="width: 150px">{{ __('admin.text_name') }}</th>
                            <td>{{ $form_data->$current_field }}</td>
                        </tr>

                        <tr>
                            <th scope="row"><a class="btn btn-primary btn-sm" href="{{ route($module['main_route'].'.index') }}">{!! __('admin.text_button_back') !!}</a></th>
                            <td>&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection