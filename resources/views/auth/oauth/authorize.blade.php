<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authorize App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/sass/next.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-center">
                <div class="w-16 h-16 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Authorization Request</h1>
                <p class="text-blue-100 text-sm">
                    <span class="font-semibold">"{{ $client->name }}"</span> would like to access your account
                </p>
            </div>

            <div class="px-6 py-6">
                <div class="bg-gray-50 rounded-lg px-4 py-3 mb-6">
                    <p class="text-sm text-gray-600 mb-1">Signed in as</p>
                    <p class="font-medium text-gray-900">{{ $user->email }}</p>
                </div>

                @if(count($scopes))
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-3">This app is requesting permission to:</p>
                    <ul class="space-y-2">
                        @foreach($scopes as $scope)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-gray-700">{{ $scope->description ?? $scope->id }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="space-y-3">
                    <form method="post" action="{{ route('passport.authorizations.approve') }}">
                        @csrf
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 cursor-pointer">
                            Authorize
                        </button>
                    </form>

                    <form method="post" action="{{ route('passport.authorizations.deny') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="{{ $request->state }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-lg border border-gray-300 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 cursor-pointer">
                            Cancel
                        </button>
                    </form>
                </div>

                <p class="text-xs text-gray-500 text-center mt-6">
                    By authorizing, you allow this app to use your information in accordance with their Terms of Service and Privacy Policy.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
