<x-filament::widget>
    <x-filament::section>
        <div x-data="repartidoresMap()" x-init="init()" class="w-full h-96">
            <div id="repartidores-map" class="w-full h-full rounded-lg"></div>
            <div class="mt-2 p-2 bg-gray-100 rounded-lg">
                <h3 class="text-md font-semibold text-gray-700">Repartidores activos: <span x-text="repartidoresActivos"></span></h3>
                <div class="mt-2">
                    <ul class="space-y-1">
                        <template x-for="repartidor in repartidores" :key="repartidor.id">
                            <li class="flex items-center gap-2 text-sm">
                                <span class="h-3 w-3 bg-green-500 rounded-full"></span>
                                <span x-text="repartidor.usuario.name"></span>
                                <span class="text-gray-500" x-text="formatLastUpdate(repartidor.ultima_actualizacion)"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&callback=Function.prototype"></script>
    <script>
        function repartidoresMap() {
            return {
                map: null,
                markers: [],
                repartidores: [],
                repartidoresActivos: 0,
                
                init() {
                    // Inicializar el mapa (ubicación central de Honduras)
                    this.map = new google.maps.Map(document.getElementById('repartidores-map'), {
                        center: { lat: 14.0723, lng: -87.1921 },
                        zoom: 13,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: true,
                        streetViewControl: true,
                        fullscreenControl: true
                    });
                    
                    // Cargar los datos de repartidores
                    this.cargarRepartidores();
                    
                    // Actualizar cada 30 segundos
                    setInterval(() => this.cargarRepartidores(), 30000);
                },
                
                cargarRepartidores() {
                    fetch('/admin/api/repartidores-disponibles')
                        .then(response => response.json())
                        .then(data => {
                            this.repartidores = data;
                            this.repartidoresActivos = data.length;
                            this.actualizarMarcadores();
                        })
                        .catch(error => {
                            console.error('Error al cargar repartidores:', error);
                        });
                },
                
                actualizarMarcadores() {
                    // Remover marcadores anteriores
                    this.markers.forEach(marker => marker.setMap(null));
                    this.markers = [];
                    
                    // Añadir nuevos marcadores
                    this.repartidores.forEach(repartidor => {
                        if (repartidor.ultima_ubicacion_lat && repartidor.ultima_ubicacion_lng) {
                            const position = {
                                lat: parseFloat(repartidor.ultima_ubicacion_lat),
                                lng: parseFloat(repartidor.ultima_ubicacion_lng)
                            };
                            
                            const marker = new google.maps.Marker({
                                position: position,
                                map: this.map,
                                title: repartidor.usuario.name,
                                icon: {
                                    url: '/images/moto-icon.png',
                                    scaledSize: new google.maps.Size(32, 32)
                                }
                            });
                            
                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                    <div class="p-2">
                                        <strong>${repartidor.usuario.name}</strong><br>
                                        Teléfono: ${repartidor.usuario.telefono || 'N/A'}<br>
                                        Última actualización: ${this.formatLastUpdate(repartidor.ultima_actualizacion)}
                                    </div>
                                `
                            });
                            
                            marker.addListener('click', () => {
                                infoWindow.open(this.map, marker);
                            });
                            
                            this.markers.push(marker);
                        }
                    });
                    
                    // Centrar mapa si hay marcadores
                    if (this.markers.length > 0) {
                        const bounds = new google.maps.LatLngBounds();
                        this.markers.forEach(marker => bounds.extend(marker.getPosition()));
                        this.map.fitBounds(bounds);
                        
                        // Ajustar zoom si solo hay un marcador
                        if (this.markers.length === 1) {
                            this.map.setZoom(15);
                        }
                    }
                },
                
                formatLastUpdate(datetime) {
                    if (!datetime) return 'No disponible';
                    const date = new Date(datetime);
                    return date.toLocaleString();
                }
            }
        }
    </script>
    @endpush
</x-filament::widget>