document.addEventListener("livewire:navigated", () => {
    initAutographFix();
});

document.addEventListener("DOMContentLoaded", () => {
    initAutographFix();
});

function initAutographFix() {
    const resizeSignatureCanvas = () => {
        document.querySelectorAll("canvas[x-ref='canvas']").forEach(canvas => {
            const rect = canvas.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0) {
                canvas.width = rect.width;
                canvas.height = rect.height;
            }
        });
    };

    // Initial trigger
    resizeSignatureCanvas();

    // Trigger again whenever tab changes
    document.addEventListener('click', (e) => {
        if (e.target.closest('[role="tab"]')) {
            setTimeout(() => {
                resizeSignatureCanvas();
            }, 100);
        }
    });
}
