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
<object data="{{ asset('/upload/pdf_files/'.$name.'.pdf') }}" type="application/pdf"><iframe src="https://docs.google.com/viewer?embedded=true&url={{ asset('/upload/pdf_files/'.$name.'.pdf') }}"></iframe></object>