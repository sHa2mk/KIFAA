function applyKefaaTheme(mode = null) {
    const selectedMode = mode || localStorage.getItem('kefaa-theme') || 'light';

    document.documentElement.classList.remove('dark');

    if (selectedMode === 'dark') {
        document.documentElement.classList.add('dark');
    }

    if (selectedMode === 'system') {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    }
}

window.setKefaaTheme = function (mode) {
    localStorage.setItem('kefaa-theme', mode);
    applyKefaaTheme(mode);
};

document.addEventListener('DOMContentLoaded', () => {
    applyKefaaTheme();
});

document.addEventListener('livewire:navigated', () => {
    applyKefaaTheme();
});