            </main>
        </div>
    </div>
    <footer class="text-center py-3 mt-4" style="background-color: var(--primary-dark); color: #d4a843;">
        <p class="mb-0">&copy; 2026 Toko Minuman - Sistem Manajemen Inventaris & Penjualan</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Yakin ingin menghapus data ini?')) e.preventDefault();
            });
        });
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => {
                a.style.transition = 'opacity 0.5s';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 500);
            });
        }, 3000);
    </script>
</body>
</html>
