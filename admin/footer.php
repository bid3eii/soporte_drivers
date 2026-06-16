</div> <!-- Fin de .admin-container -->
<script>
    // Prevenir el mensaje de "Confirmar reenvío del formulario" al refrescar la página (F5)
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    // Interceptar botones de eliminar para mostrar SweetAlert2 en lugar del alert nativo
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete');
        deleteButtons.forEach(button => {
            const originalOnclick = button.getAttribute('onclick');
            let message = "¿Estás seguro de eliminar este registro?";
            if (originalOnclick) {
                const match = originalOnclick.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    message = match[1];
                }
                button.removeAttribute('onclick');
            }

            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: 'rgba(255, 255, 255, 0.1)',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    background: '#0e1220',
                    color: '#f3f4f6',
                    customClass: {
                        popup: 'swal-custom-popup',
                        confirmButton: 'swal-custom-confirm',
                        cancelButton: 'swal-custom-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    });
</script>
</body>
</html>
