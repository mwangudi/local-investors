document.addEventListener('livewire:initialized', () => {
    // Auto-clear Filament field errors when fixed
    Livewire.hook('message.processed', () => {
        document.querySelectorAll('[data-validation-error]').forEach(el => {
            const input = el.querySelector('input, select, textarea');
            const error = el.querySelector('.fi-input-error-message');

            if (!input || !error) return;

            input.addEventListener('input', () => {
                if (error.innerText.trim() !== '') {
                    // If user starts fixing input, revalidate field
                    Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'))
                        .$validateOnly(input.getAttribute('wire:model'));

                    // If error is removed, hide the message
                    setTimeout(() => {
                        if (error.innerText.trim() === '') {
                            error.style.display = 'none';
                        }
                    }, 50);
                }
            });
        });
    });
});