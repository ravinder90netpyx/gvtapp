<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use \Collective\Html\FormFacade;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        LogViewer::auth(function ($request) {
            return $request->user() && in_array($request->user()->id, [1]);
        });

        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');

        FormFacade::macro('rawLabel', function($name, $value = null, $options = array()){
            $label = FormFacade::label($name, '%s', $options);
            return sprintf($label, $value);
        });

        FormFacade::component('bsText', 'components.form.bsText', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsPassword', 'components.form.bsPassword', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsEmail', 'components.form.bsEmail', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsInput', 'components.form.bsInput', ['type', 'name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsMultiInput', 'components.form.bsMultiInput', ['multi_attributes'=>[], 'label_text', 'extra_attributes'=>[] ]);
        FormFacade::component('bsTextArea', 'components.form.bsTextArea', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsSelect', 'components.form.bsSelect', ['name', 'label_text', 'data', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsTree', 'components.form.bsTree', ['name', 'label_text', 'data', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsDatalist', 'components.form.bsDatalist', ['name', 'label_text', 'data', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsToggle', 'components.form.bsToggle', ['name', 'label_text', 'value', 'checked', 'field_attributes'=>[], 'extra_attributes'=>[]]);
        FormFacade::component('bsDate', 'components.form.bsDate', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsDatetime', 'components.form.bsDatetime', ['name', 'label_text', 'value', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
        FormFacade::component('bsFile', 'components.form.bsFile', ['name', 'label_text', 'field_attributes'=>[], 'extra_attributes'=>[] ]);
    }
}
