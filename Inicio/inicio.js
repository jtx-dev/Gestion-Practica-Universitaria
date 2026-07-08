document.addEventListener('DOMContentLoaded', function () {
    const elementoContador = document.getElementById('contador-usuarios');

    if (!elementoContador) {
        return;
    }

    const totalUsuarios = Number(elementoContador.dataset.total || 0);
    let contadorActual = 0;

    if (totalUsuarios <= 0) {
        elementoContador.textContent = '0';
        return;
    }

    const paso = Math.max(1, Math.floor(totalUsuarios / 80));

    const intervalo = setInterval(function () {
        contadorActual += paso;

        if (contadorActual >= totalUsuarios) {
            contadorActual = totalUsuarios;
            clearInterval(intervalo);
        }

        elementoContador.textContent = contadorActual.toLocaleString('en-US');
    }, 50);
});
