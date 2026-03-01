<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Profile</h2>
    </x-slot>

    <div class="space-y-6 max-w-xl">
        <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
            @include('profile.partials.update-password-form')
        </div>

        <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
