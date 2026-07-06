<x-layouts::auth>
    <div class="flex flex-col gap-6">
       <div class="text-center">

    
    <h1 class="text-2xl font-black text-zinc-900">
        {{ __('Confirm password') }}
    </h1>

    <p class="mt-3 text-sm font-semibold leading-6 text-zinc-500">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>
   </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
