@if(session('show_2fa_reminder') && auth()->user() && !auth()->user()->two_factor_enabled)
<div id="2fa-reminder" class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-yellow-400 to-orange-500 shadow-lg border-b-4 border-orange-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">
                        <strong>Zabezpiecz swoje konto!</strong>
                        Włącz uwierzytelnianie dwuskładnikowe aby zwiększyć bezpieczeństwo.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('two-factor.setup') }}" 
                   class="bg-white text-orange-600 hover:text-orange-700 font-semibold py-1 px-3 rounded text-sm transition duration-150 ease-in-out">
                    Włącz teraz
                </a>
                <button onclick="dismiss2FAReminder()" 
                        class="text-white hover:text-orange-200 font-medium text-sm underline">
                    Nie teraz
                </button>
                <button onclick="dismiss2FAReminder()" 
                        class="text-white hover:text-orange-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function dismiss2FAReminder() {
    // Hide the reminder
    document.getElementById('2fa-reminder').style.display = 'none';
    
    // Store dismissal in session via AJAX
    fetch('/dismiss-2fa-reminder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
}
</script>

<style>
    /* Push content down when reminder is shown */
    body { padding-top: 60px; }
</style>
@endif