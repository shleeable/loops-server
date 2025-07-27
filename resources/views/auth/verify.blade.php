@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Verify Your Email Address') }}</h2>
        </div>

        <div class="px-6 py-6">
            @if (session('resent'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-green-800 text-sm">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </p>
                </div>
            @endif

            <div class="text-gray-700 text-sm leading-relaxed">
                <p class="mb-4">{{ __('Before proceeding, please check your email for a verification link.') }}</p>

                <p>
                    {{ __('If you did not receive the email') }},
                    <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit"
                                class="text-blue-600 hover:text-blue-800 hover:underline font-medium focus:outline-none focus:underline">
                            {{ __('click here to request another') }}
                        </button>.
                    </form>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
