function ejecutarLogin(event) {
    event.preventDefault(); 

    const email = document.getElementById('email').value.toLowerCase();
    
    if (email.includes('superadmin')) {
        alert('Detectado como Super Administrador');
        window.location.href = 'superadmin/inicio.php';
    } 
    else if (email.includes('admin')) {
        alert('Detectado como Administrador');
        window.location.href = 'admin/inicio.php'; 
    } 
    else if (email.includes('coord')) {
        alert('Detectado como Coordinador');
        window.location.href = 'coord/inicio.php';
    } 
    else if (email !== "") {
        alert('Detectado como Estudiante');
        window.location.href = 'estudiante/inicio.php'; 
    } 
    else {
        alert('Por favor, ingresa un correo');
    }
}