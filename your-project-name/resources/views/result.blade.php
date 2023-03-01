{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Document</title>
</head>

<body>
    <form action="/uploadFileExam" method="post" enctype='multipart/form-data'>
        @csrf
        <input type="file" name="file">
        <input type="submit" value="Up">
    </form>
</body>

</html> --}}


{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <script src="{{ asset('') }}"></script>
</head>

<body>
    {{-- <div id="app">
        <example-component></example-component>
    </div> --}}
<form action="/uploadFileExam" method="post" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <input type="submit">
</form>
{{-- <form action="/upload" method="POST">
        @csrf
        <input type="file" name="file">
        <input type="submit">
    </form> --}}
</body>

</html>
