function actualizarConsumo() {
    fetch('consumo.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('consumoSistema').textContent = data;
        })
        .catch(() => {
            document.getElementById('consumoSistema').textContent = 'Error';
        });
}

actualizarConsumo();
setInterval(actualizarConsumo, 20000);
