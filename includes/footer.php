<footer>
    <div class="container footer-content">
        <div class="footer-links">
            <a href="index.php">Inicio</a>
        </div>
        <p class="footer-text">&copy; <?php echo date('Y'); ?> Soporte Master. Todos los derechos reservados. Desarrollado con PHP & MySQL.</p>
    </div>
</footer>

<!-- Botón flotante Scroll to Top -->
<button id="scrollTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Volver arriba">
    <i class="fa-solid fa-arrow-up"></i>
</button>
<style>
#scrollTopBtn {
    position: fixed;
    bottom: 100px;
    right: 30px;
    z-index: 9999;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--primary, #4f46e5), #6366f1);
    color: white;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}
#scrollTopBtn.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
#scrollTopBtn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
    filter: brightness(1.15);
}
</style>
<script>
window.addEventListener('scroll', function() {
    var btn = document.getElementById('scrollTopBtn');
    if (window.scrollY > 300) {
        btn.classList.add('visible');
    } else {
        btn.classList.remove('visible');
    }
});
</script>

</body>
</html>
