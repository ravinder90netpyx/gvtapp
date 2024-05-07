@php 
$field_attributes['multiple'] = "multiple";
$vertical = empty($extra_attributes['vertical']) ? false : true;
$multiple = !empty($field_attributes['multiple']) ? true : false;

if(!function_exists('buildTreeView')){
    function buildTreeView(array $elements, $parentId = 0){
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                unset($element['parent_id']);
                $children = buildTreeView($elements, $element['row_id']);
                if($children){
                    foreach($children as $kchild=>$child) unset($children[$kchild]['parent_id']);
                    $element['nodes'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }
}

$field_id = rtrim($name, '[]');
$tree_id = $field_id.'_tree';
if(substr($name, -2)=="[]"){
    $error_name = rtrim($name, '[]');
    #$error_name = $error_name.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}

if($multiple){
    $data = array_filter($data);
}

#echo "<pre>"; print_r($value); echo "<pre>"; exit;
if(!is_array($value)){
    if(empty($value)) $value = [];
    else{
        $value = [$value];
    }
}

$kv_arr = [];
$tree_arr = [];
$tree_array = [];
if($field_id=="category"){
    $treeModel =  new App\Models\Product_Categories;
    $getDt = $treeModel->select(['pcat_id', 'pcat_name', 'pcat_parent_id'])->get()->toArray();

    if(!empty($getDt)){
        foreach($getDt as $gdt){
            $kv_arr[$gdt['pcat_id']] = $gdt['pcat_name'];
            $dt = array(
                'row_id' => $gdt['pcat_id'],
                'text' => $gdt['pcat_name'],
                'parent_id' => $gdt['pcat_parent_id'],
                'state' => array(
                    'selected' => in_array($gdt['pcat_id'], $value) ? true : false,
                )
            );

            $tree_arr[] = $dt;
        }
    }
} elseif($field_id=="group"){
    $treeModel =  new App\Models\Product_Groups;
    $getDt = $treeModel->select(['pgroup_id', 'pgroup_name', 'pgroup_parent_id'])->get()->toArray();

    if(!empty($getDt)){
        foreach($getDt as $gdt){
            $kv_arr[$gdt['pgroup_id']] = $gdt['pgroup_name'];
            $dt = array(
                'row_id' => $gdt['pgroup_id'],
                'text' => $gdt['pgroup_name'],
                'parent_id' => $gdt['pgroup_parent_id'],
                'state' => array(
                    'selected' => in_array($gdt['pgroup_id'], $value) ? true : false,
                )
            );

            $tree_arr[] = $dt;
        }
    }
}

if(!empty($tree_arr)){
    $tree_array = buildTreeView($tree_arr);
}

#echo "<pre>"; print_r($tree_array); echo "<pre>"; exit;

$default_attr = ['id'=>$field_id, 'data-toggle'=>'select-multiple-tree', 'class' => 'form-control '.( !empty($errors->has($error_name)) ? 'is-invalid ' : '' )];
@endphp
<div class="position-relative bs-tree-wrap form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($field_id, $label_text.( in_array('required', $field_attributes) ? '<span class="req"></span>' : '' ), ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-9">' : '' !!}
        {!! Form::select($name, $kv_arr, $value ?? '', array_merge($default_attr, $field_attributes)); !!}
        <button type="button" class="toggle_bstree"><i class="fas fa-angle-down"></i></button>
        <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>

        <div class="tree_container_wrap" style="display:none;">
            {{--<input type="search" id="{{ $field_id }}_search" class="form-control" value="" />--}}
            <div id="{{ $tree_id }}"></div>
        </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>
<script type="text/javascript">
function _updateChildren(node, $unselecting_val){
    node.forEach(function(n){
        if(typeof n.row_id !== undefined && n.row_id == $unselecting_val){
            //console.log(n.nodeId);
            $('#{{ $tree_id }}').treeview('unselectNode', [n.nodeId, { silent: true } ]);
        }

        if(n.nodes !== undefined){
            var childrenNodes = n.nodes;
            return _updateChildren(childrenNodes, $unselecting_val);
        }
    });
    //if(node.nodes === undefined) return [];
    //alert(node[0]['nodeId']);
    //$('#{{ $tree_id }}').treeview('uncheckNode', [ node.nodeId, { silent: true } ]);

    //if(node.nodes !== undefined){
        /*var childrenNodes = node.nodes;
        node.nodes.forEach(function(n) {
            childrenNodes = childrenNodes.concat(_getChildren(n));
        });
        return childrenNodes;*/
    /*} else{
        return [];
    }*/
}

$(function(){
    $('#{{ $field_id }}').select2({
        allowClear: false,
        dropdownParent: $('.bs-tree-wrap'),
    });

    /*$('#{{ $field_id }}').on('select2:select', function(e){
        var data = e.params.data;
        console.log(data);
    });*/

    /*$(document).click(function(event){
        var obj = $(".bs-tree-wrap");
        if (!obj.is(event.target) && !obj.has(event.target).length) {
            if($(".bs-tree-wrap .tree_container_wrap").is(":visible")){
                $(".bs-tree-wrap .tree_container_wrap").hide();
            }
        } else{
        }
    });*/

    $('.bs-tree-wrap .toggle_bstree').click(function(e){
        e.preventDefault();
        //alert($(this).find('.selection').length);
        setTimeout(function(){
            $(this).closest('.bs-tree-wrap').find('.tree_container_wrap').toggle();
        }.bind(this), 100);
        
    });

    /*$('body').click(function(e){
        if($(this).closest('.bs-tree-wrap').length<1){
            $('.bs-tree-wrap').find('.tree_container_wrap').hide();
        }
    });*/


    var $tree_data = {!! json_encode($tree_array) !!};

    var $myTree = $('#{{ $tree_id }}').treeview({
        data: $tree_data,

        //emptyIcon: 'far fa-circle',
        icon: "",

        nodeIcon: 'far fa-circle',
        selectedIcon: "far fa-check-circle",
        showIcon: true,

        uncheckedIcon: 'far fa-circle',
        checkedIcon: 'far fa-check-circle',
        showCheckbox: false,
        
        expandIcon: 'fas fa-plus-circle',
        collapseIcon: 'fas fa-minus-circle',

        color: "#000033",
        backColor: "#FFFFFF",
        selectedColor: '#000000',
        selectedBackColor: '#FFFFFF',

        injectStyle: true,
        levels: 1,
        selectable: true,
        multiSelect: true,
        
        //tags: ['available'],
        //showTags: false,
        state: {
            //checked: true,
            //disabled: true,
            //expanded: true,
            selected: true
        },
        
    }).on('nodeSelected nodeUnselected', function(e, node){
        var $target_selector = $('#{{ $field_id }}');
        //console.log(node.state.selected+' '+node.row_id);
        if(node.state.selected){
            $target_selector.find('option[value="'+node.row_id+'"]').prop('selected', true);
        } else{
            $target_selector.find('option[value="'+node.row_id+'"]').prop('selected', false);
        }
        $target_selector.trigger('change.select2');
        //console.log($('#{{ $tree_id }}').treeview('getSelected'));
    });

    /*var findSelectableNodes = function() {
      return $myTree.treeview('search', [ $('#{{ $field_id }}_search').val(), { ignoreCase: true, exactMatch: false, revealResults:true } ]);
    };
    var selectableNodes = findSelectableNodes();

    $('#{{ $field_id }}_search').on('keyup', function (e) {
        $myTree.treeview('collapseAll', { silent:true });
        
        selectableNodes = findSelectableNodes();
        //$('.select-node').prop('visibility', !(selectableNodes.length >= 1));
        //alert("vcxbv");

        $myTree.treeview('selectNode', [ selectableNodes, { silent: true }]);
    });*/

    $('#{{ $field_id }}').on('select2:unselecting', function (e){
        var $unselecting_val = e.params.args.data.id;
        var node = $('#{{ $tree_id }}').treeview('getEnabled', 0);
        //console.log(node);
        //var node = $('#{{ $tree_id }}').treeview('getNode', 0);
        var childrenNodes = _updateChildren(node, $unselecting_val);
        /*$(childrenNodes).each(function(){
            //console.log(this.nodeId);
            //$('#{{ $tree_id }}').treeview('checkNode', [ this.nodeId, { silent: false } ]);
        });*/
    });
});
</script>