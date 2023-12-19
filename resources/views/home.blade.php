<h1>Listing Users</h1>

<div>
    @foreach ($users as $user)
    <p>ID: {{ $user->user_id }}</p>
    <p>NAME: {{ $user->user_name }}</p>
    <p>EMAIL: {{ $user->user_email }}</p>
    <p>CREATED AT: {{ date('F d, Y h:i A', strtotime($user->created_at)) }}</p>
    @endforeach
</div>

@guest
<a href="{{ url('/login/keycloak') }}">Login with Keycloak</a>
@endguest

@auth
<p>Hi, {{ auth()->user()->user_name }}</p>
<hr>
<a href="{{ url('/logout') }}">Logout</a>
@endauth
