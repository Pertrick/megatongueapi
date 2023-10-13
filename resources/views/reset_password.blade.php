
{{-- @section('content') --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
</head>

<body>
{{-- <h1>helllo</h1> --}}
    {{-- @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

    @endif --}}

    <form method="POST" action="{{ url('updatepassword') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $user['email'] }}">
        <input type="password" name="password" class="form-control mb-5 w-50" placeholder="New Password">
        <input type="password" name="confirm_pass" class="form-control mb-5 w-50" placeholder="Confirm Password">
        <button type="submit" class="form-control btn btn-primary w-50">Reset Password</button>
    </form>
</body>

</html>
{{-- @endsection --}}
