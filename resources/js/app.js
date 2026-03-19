import './bootstrap';

document.addEventListener('livewire:init', () => {
    window.addEventListener('comment-added', () => {
        requestAnimationFrame(() => {
            const comments = document.querySelectorAll('[data-comment-item]');
            const latest = comments[comments.length - 1];

            latest?.scrollIntoView({ behavior: 'smooth', block: 'end' });
        });
    });

    window.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        if (! window.Livewire) {
            return;
        }

        window.Livewire.dispatch('workspace-clear-selection');
    });
});
