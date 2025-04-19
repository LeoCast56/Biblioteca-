// Validaciones para el menú de empleados
document.addEventListener("DOMContentLoaded", function () {
    // Validar si el usuario es un empleado antes de mostrar el menú de empleados
    const empleadoMenu = document.querySelector(".menu:nth-child(2)"); // Selecciona el menú de empleados
    const esEmpleado = confirm("¿Eres un empleado? Presiona 'Aceptar' si es así.");

    if (!esEmpleado) {
        empleadoMenu.style.display = "none"; // Oculta el menú de empleados si no es un empleado
    }

    // Validar si el alumno tiene multas o préstamos pendientes
    const pedirLibroBtn = document.querySelector(".menu:nth-child(3) ul li:nth-child(2) a");
    pedirLibroBtn.addEventListener("click", function (event) {
        const tieneMulta = confirm("¿Tienes alguna multa pendiente? Presiona 'Aceptar' si es así.");
        const tienePrestamo = confirm("¿Tienes algún préstamo pendiente? Presiona 'Aceptar' si es así.");

        if (tieneMulta || tienePrestamo) {
            alert("No puedes pedir un libro prestado hasta que pagues tu multa o devuelvas el libro.");
            event.preventDefault(); // Evita que el enlace funcione
        } else {
            alert("Puedes proceder a pedir un libro prestado.");
        }
    });
});