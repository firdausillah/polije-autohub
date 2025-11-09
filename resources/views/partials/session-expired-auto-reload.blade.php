<script>
    document.addEventListener("livewire:init", () => {
        console.log("[SessionHandler] Livewire initialized");

        let popupPatched = false;

        // Patch default Livewire popup reload behavior
        const patchPopup = () => {
            if (popupPatched) return;
            const reloadBtn = document.querySelector('button[wire\\:click="reloadPage"]');

            if (reloadBtn) {
                popupPatched = true;
                console.log("[SessionHandler] Patched Livewire reload button behavior");

                reloadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.location.reload(); // native reload
                }, {
                    once: true
                });
            }
        };

        // Observe DOM changes — because popup inserted dynamically
        const observer = new MutationObserver(() => patchPopup());
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Optional: also handle manual 419 detection if popup fails
        window.Livewire.hook('request', ({
            fail
        }) => {
            fail((response) => {
                if (response.status === 419) {
                    console.warn("[SessionHandler] 419 detected, ensuring popup reload works");
                    patchPopup();
                }
            });
        });
    });
</script>