function toggleOtroAsunto() {
    const select = document.getElementById('selectAsunto');
    const bloqueOtro = document.getElementById('bloqueOtroAsunto');
    
    if (select.value === 'otro') {
        bloqueOtro.classList.remove('d-none'); // Muestra el campo
    } else {
        bloqueOtro.classList.add('d-none');    // Lo oculta si cambian de opinión
    }
}