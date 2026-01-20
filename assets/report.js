function switchView(view) {
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelector('[data-view="' + view + '"]').classList.add('active');
    document.getElementById('view-' + view).classList.add('active');
}
