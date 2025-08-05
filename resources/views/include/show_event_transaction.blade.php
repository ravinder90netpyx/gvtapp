<style type="text/css">
*{
	padding: 0;
	margin: 0;
}
object,iframe{
	height: 100vh;
	width: 100vw;
	overflow: hidden;
}
</style>
<object data="{{ asset('/upload/event_transaction/'.$name.'.pdf') }}" type="application/pdf"><iframe src="https://docs.google.com/viewer?embedded=true&url={{ asset('/upload/event_transaction/'.$name.'.pdf') }}"></iframe></object>