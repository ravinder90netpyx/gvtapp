@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
        <strong>{!! $message !!}</strong>
</div>
@endif


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
        <strong>{!! $message !!}</strong>
</div>
@endif


@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{!! $message !!}</strong>
</div>
@endif


@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{!! $message !!}</strong>
</div>
@endif

@if( ( Session::get('success') || Session::get('error') || Session::get('warning') || Session::get('info') ) && !Session::get('not_removable') )
<script type="text/javascript">
setTimeout(function() {
    $(".alert").alert('close');
}, 3000);
</script>
@endif