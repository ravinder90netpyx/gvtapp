<?php
return [
    'icons' => [
        'active' => 'text-success fa-lg fas fa-check-circle',
        'inactive' => 'text-danger fa-lg fas fa-times-circle',
        'info' => 'text-info fa-lg fas fa-info-circle',
        'edit' => 'text-primary fa-lg fas fa-edit',
        'delete' => 'text-danger fa-lg fas fa-trash-alt'
    ],
    'datetime_format' => 'Y-m-d H:i:s',
    'date_format' => 'Y-m-d',
    'datatable' => [
        #'searchBuilder'=>['depthLimit'=>'1'],

        'lengthMenu'=>[ [10, 50, 100, 1000, -1], [10, 50, 100, 1000, "All"] ],
        'language'=>[
            'search'=>'_INPUT_', 
            'searchPlaceholder'=>'Search...',
            'paginate'=>[
                'previous'=>'&lsaquo;',
                'next'=>'&rsaquo;'
            ],
            'searchBuilder'=>[
                'button'=>'Advance Search'
            ],
            'buttons'=>[
                'colvis'=>'Visibility',
                'excel'=>'XLS',
                'csv'=>'CSV',
                'pdf'=>'PDF',
                'print'=>'Print',
                'copy'=>'Copy',
                'pageLength'=>[
                    '_'=>'%d rows'
                ],
                'savedStates'=>'Instance',
                'createState'=>'Create Instance',
                'updateState'=>'Update',
                'stateRestore'=>'New Instance %d',
                'removeState'=>'Remove',
                'renameState'=>'Rename'
            ],
            'stateRestore'=>[
                'removeSubmit'=>'Confirm',
                'removeConfirm'=>'Confirm you want to remove %s.',
                'emptyStates'=>'No Instance',
                'renameButton'=>'Rename',
                'renameLabel'=>'Rename to:',
                'renameTitle'=>'Rename Instance',
                'removeTitle'=>'Remove Instance',
            ]
        ]
    ]
];