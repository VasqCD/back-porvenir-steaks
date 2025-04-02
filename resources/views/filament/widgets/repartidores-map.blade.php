<x-filament::widget>
    <x-filament::card>
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-8">
                <h2 class="text-xl font-semibold tracking-tight">
                    Repartidores activos
                </h2>
            </div>

            <div id="map" style="height: 400px; width: 100%;"></div>
        </div>
    </x-filament::card>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Coordenadas centrales de Honduras
            const map = L.map('map').setView([15.5, -88.0], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Añadir marcadores para cada repartidor
            const repartidores = @json($this->getRepartidores());

            repartidores.forEach(function(repartidor) {
                const marker = L.marker([repartidor.ultima_ubicacion_lat, repartidor.ultima_ubicacion_lng]).addTo(map);
                marker.bindPopup(`
                    <strong>${repartidor.usuario.name}</strong><br>
                    Teléfono: ${repartidor.usuario.telefono || 'No disponible'}<br>
                    Última actualización: ${new Date(repartidor.ultima_actualizacion).toLocaleString()}
                `);
            });
        });
    </script>
</x-filament::widget>
