<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimiendo Ticket...</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

    <!-- Este iframe cargará el PDF -->
    <iframe id="pdf-frame" src="{{ $urlPdf }}"></iframe>

    <script>
        const iframe = document.getElementById('pdf-frame');
        
        // Variable para controlar que solo se imprima una vez
        let hasPrinted = false;

        // ==========================================================
        // CAMBIO 1: Lógica de "después de imprimir" corregida
        // ==========================================================
        const afterPrintAction = () => {
            if (hasPrinted) return; // Evita doble ejecución
            hasPrinted = true;

            try {
                // 1. Recarga la página principal del TPV (el 'parent')
                if (window.parent && window.parent.location) {
                    window.parent.location.reload();
                }
            } catch (e) {
                console.error("Error al recargar el TPV:", e);
            } finally {
                // 2. Limpia este iframe para evitar bucles de recarga
                // Esto es crucial para que no vuelva a imprimir al recargar el TPV.
                window.location.href = 'about:blank';
            }
        };

        // Cuando el iframe (con el PDF) termine de cargar...
        iframe.onload = function() {
            if (hasPrinted) return; // Si el fallback se ejecutó primero, no hacer nada

            try {
                // 1. Lanza el diálogo de impresión
                iframe.contentWindow.print();
                
                // ==========================================================
                // CAMBIO 2: Marcar como impreso INMEDIATAMENTE
                // (Esto evita que el setTimeout de abajo se dispare)
                // ==========================================================
                hasPrinted = true; 
                
                // 3. Escucha cuándo se cierra el diálogo de impresión
                iframe.contentWindow.onafterprint = afterPrintAction;

            } catch (e) {
                console.error("Error al imprimir (onload):", e);
                afterPrintAction(); // Si falla, al menos recarga
            }
        };

        // Fallback (por si 'onload' tarda demasiado o falla)
        setTimeout(function() {
            // Si el 'onload' ya funcionó, no hacer nada
            if (hasPrinted) return; 

            try {
                console.log("Usando fallback de impresión (setTimeout)");
                iframe.contentWindow.print();
                hasPrinted = true;
                iframe.contentWindow.onafterprint = afterPrintAction;
            } catch(e) {
                console.error("Error en fallback de impresión:", e);
                afterPrintAction();
            }
        }, 2500); // Espera 2.5 segundos
    </script>

</body>
</html>