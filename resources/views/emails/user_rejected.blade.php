@component('mail::message')
    # Hello {{ $user->name }},

    We're sorry to inform you that your registration has been rejected by our admin team.

    If you believe this was a mistake or need more information, feel free to contact our support.

    Thanks,
    {{ config('app.name') }}
@endcomponent
