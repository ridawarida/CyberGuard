(() => {
    document.querySelectorAll('.cg-journal-range').forEach((range) => {
        const outputId = range.id === 'stress_level'
            ? 'stress-output'
            : `stress-output-${range.id.replace('stress-', '')}`;
        const output = document.getElementById(outputId);
        if (!output) return;
        range.addEventListener('input', () => {
            output.textContent = `${range.value}/10`;
        });
    });

    const key = document.getElementById('journal-key');
    const copyButton = document.getElementById('copy-key');

    if (!key || !copyButton) return;

    copyButton.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(key.textContent.trim());
            copyButton.innerHTML = '<i class="fas fa-check me-2"></i>Key copied';
        } catch {
            copyButton.textContent = 'Select the key above to copy it manually';
        }
    });
})();
