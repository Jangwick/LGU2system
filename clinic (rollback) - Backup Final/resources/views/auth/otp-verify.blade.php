<x-guest-layout>
    {{-- OTP Verification Form --}}
    <form method="POST" action="{{ route('otp.submit') }}">
        @csrf

        <div>
            <label for="otp" 
                class="block font-medium text-sm 
                    {{ $errors->has('otp') ? 'text-red-600' : 'text-gray-700' }}">
                Enter the OTP sent to your email:
            </label>

            <input 
                type="text" 
                name="otp" 
                class="mt-2 p-2 border rounded w-full 
                    {{ $errors->has('otp') ? 'border-red-600 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}" 
                required
            >

            @error('otp')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 flex gap-2">
            <button 
                type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Verify
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('otp.cancel') }}" class="mt-2">
        @csrf
        <button 
            type="submit" 
            class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
            Cancel
        </button>
    </form>
</x-guest-layout>
