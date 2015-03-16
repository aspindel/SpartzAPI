<html><head></head>


<body>

	{{Form::open('route'=>'update')}}

{{Form::text('id')}}
{{Form::password('password')}}

{{Form::submit()}}
	{{Form::close()}}

</body></html>