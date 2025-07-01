@component('mail::message')
    # Congratulations {{ $user->name }}!

    Your account has been approved by the admin.

    You can now log in and start using the platform.

    @component('mail::button', ['url' => config('app.url')])
        Go to Login
    @endcomponent

    Thanks,
    {{ config('app.name') }}
@endcomponent
