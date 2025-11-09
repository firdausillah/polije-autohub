<script>
    document.addEventListener("livewire:init", () => {
        console.log("[SessionHandler] Livewire initialized");

        let handled = false;

        window.Livewire.hook('request', ({
            fail
        }) => {
            fail((response) => {
                if (response.status === 419 && !handled) {
                    handled = true;
                    console.warn("[SessionHandler] Session expired detected — disabling Livewire & showing popup patch");

                    // Hentikan semua Livewire request berikutnya
                    try {
                        window.Livewire.stop(); // stop polling if available
                    } catch (e) {
                        console.log("Livewire.stop() not available, fallback to manual stop");
                    }

                    // Bersihkan interval polling (yang di set oleh wire:poll)
                    let highestId = window.setInterval(() => {}, 9999);
                    for (let i = 0; i <= highestId; i++) {
                        clearInterval(i);
                    }

                    // Matikan event fetch agar tidak kirim ulang
                    if (!window._fetchBackup) {
                        window._fetchBackup = window.fetch;
                        window.fetch = () => Promise.reject("Session expired - fetch disabled");
                    }

                    // Patch tombol reload di popup bawaan Livewire
                    const observer = new MutationObserver(() => {
                        const reloadBtn = document.querySelector('button[wire\\:click="reloadPage"]');
                        if (reloadBtn) {
                            console.log("[SessionHandler] Patched reload button");
                            reloadBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                window.location.reload();
                            }, {
                                once: true
                            });
                            observer.disconnect();
                        }
                    });
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        });
    });
</script>