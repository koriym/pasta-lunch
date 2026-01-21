function switchView(view) {
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelector('[data-view="' + view + '"]').classList.add('active');
    document.getElementById('view-' + view).classList.add('active');
}

function copyPath(el, path) {
    navigator.clipboard.writeText(path).then(() => {
        var originalSvg = el.innerHTML;
        el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>';
        el.classList.add('copied');

        var toast = document.getElementById('toast');
        toast.textContent = path;
        toast.classList.add('show');

        setTimeout(function() {
            el.innerHTML = originalSvg;
            el.classList.remove('copied');
            toast.classList.remove('show');
        }, 1500);
    });
}
