<!-- loader.php -->
<div class="loader-overlay hide" id="globalLoader">
    <svg class="loader" viewBox="0 0 100 100">
        <circle class="circle" cx="50" cy="50" r="10"></circle>
        <circle class="circle" cx="50" cy="50" r="20"></circle>
        <circle class="circle" cx="50" cy="50" r="30"></circle>
        <circle class="circle" cx="50" cy="50" r="40"></circle>
    </svg>
</div>

<style>
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(4px);
        z-index: 999999;
        opacity: 1;
        visibility: visible;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        pointer-events: auto;
    }

    .loader-overlay.hide {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .loader {
        width: 180px;
        height: 80px;
        overflow: visible;
    }

    .circle {
        fill: none;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-dasharray: 0, 314;
        animation: draw 1.6s ease-in-out infinite;
        filter: drop-shadow(0 0 10px currentColor);
        transform-origin: center;
    }

    .circle:nth-child(1) {
        stroke: #c099bd;
        animation-delay: 0s;
    }

    .circle:nth-child(2) {
        stroke: #0b5cad;
        animation-delay: 0.3s;
    }

    .circle:nth-child(3) {
        stroke: #c099bd;
        animation-delay: 0.6s;
    }

    .circle:nth-child(4) {
        stroke: #0b5cad;
        animation-delay: 0.9s;
    }

    @keyframes draw {
        0% {
            stroke-dasharray: 0, 314;
            opacity: 0.2;
            transform: scale(0.85);
        }

        50% {
            opacity: 1;
        }

        100% {
            stroke-dasharray: 314, 314;
            opacity: 0.2;
            transform: scale(1.15);
        }
    }
</style>

<script>
(function() {
    const loader = document.getElementById('globalLoader');
    if (!loader) return;

    let showTimer = null;
    let safetyTimer = null;

    function hideLoader() {
        if (showTimer) {
            clearTimeout(showTimer);
            showTimer = null;
        }
        if (safetyTimer) {
            clearTimeout(safetyTimer);
            safetyTimer = null;
        }
        loader.classList.add('hide');
    }

    function showLoader() {
        loader.classList.remove('hide');
        if (safetyTimer) clearTimeout(safetyTimer);
        safetyTimer = setTimeout(hideLoader, 2500);
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        hideLoader();
    } else {
        document.addEventListener('DOMContentLoaded', hideLoader);
    }
    window.addEventListener('load', hideLoader);
    window.addEventListener('pageshow', hideLoader);

    setTimeout(hideLoader, 150);

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href') || '';
        const target = link.getAttribute('target');
        const download = link.getAttribute('download');
        const role = link.getAttribute('role');
        const classList = link.className || '';
        const onclickAttr = link.getAttribute('onclick') || '';

        if (target === '_blank' || download !== null || !href || href.startsWith('#') || href.startsWith('javascript:')) {
            return;
        }

        if (/(\?|&)export=/i.test(href)) {
            return;
        }

        if (
            link.hasAttribute('data-id') ||
            link.hasAttribute('data-status') ||
            link.hasAttribute('data-bs-toggle') ||
            link.hasAttribute('data-toggle') ||
            role === 'button' ||
            /\b(cust-toggle-btn|action-button|toggle-active-btn|toggle-inactive-btn|view-btn|edit-btn|delete-btn|btn-delete|modal|dropdown)\b/i.test(classList) ||
            /(\?|&)delete=|\bdelete\.php\b/i.test(href) ||
            /confirm/i.test(onclickAttr)
        ) {
            return;
        }

        showTimer = setTimeout(() => {
            if (!e.defaultPrevented) {
                showLoader();
            }
        }, 200);
    }, true);

    document.addEventListener('submit', function(e) {
        if (e.defaultPrevented) return;
        const form = e.target;
        const action = form.getAttribute('action') || '';
        const onsubmitAttr = form.getAttribute('onsubmit') || '';

        if (/confirm.*delete/i.test(onsubmitAttr) || /(\?|&)delete=/i.test(action) || /(\?|&)export=/i.test(action)) {
            return;
        }

        showTimer = setTimeout(() => {
            if (!e.defaultPrevented) {
                showLoader();
            }
        }, 200);
    }, true);
})();
</script>
