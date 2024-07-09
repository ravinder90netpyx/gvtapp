@php
$data_arr = $data_arr ?? [];
@endphp
<label>
	@php
		$perpage_select = '<select id="perpage" class="custom-select">
			<option value="1"'.( $perpage=="1" ? " selected" : "" ).'>1</option>
			<option value="5"'.( $perpage=="5" ? " selected" : "" ).'>5</option>
			<option value="10"'.( $perpage=="10" ? " selected" : "" ).'>10</option>
			<option value="30"'.( $perpage=="30" ? " selected" : "" ).'>30</option>
			<option value="50"'.( $perpage=="50" ? " selected" : "" ).'>50</option>
			<option value="100"'.( $perpage=="100" ? " selected" : "" ).'>100</option>
			<option value="1000"'.( $perpage=="1000" ? " selected" : "" ).'>1000</option>
		</select>';
	@endphp
	{!! __('admin.text_perpage', ['perpageSelect'=>$perpage_select]) !!}
</label>
 
<script>
document.getElementById('perpage').onchange = function(){
	{{-- @php dd($data_arr); @endphp --}}
	Cookies.set('perpage', this.value);
	window.location = "{!! current(explode('?', $data->url(1))) !!}?perpage=" + this.value+"&{!!implode('&', $data_arr)!!}"; 
}
</script>