@vite('resources/css/app.css')
<x-filament::page>
    {{ $this->form }}

    <x-filament::actions
        :actions="$this->getActions()"
        :full-width="$this->hasFullWidthActions()"
    />
    
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Instrucciones
        </x-slot>
        
        <div class="prose max-w-none prose-blue">
            <h3>¿Cómo registrar un cambio de estado?</h3>
            <ol>
                <li>Seleccione el <strong>pedido</strong> al que desea registrar un cambio de estado.</li>
                <li>El <strong>estado anterior</strong> se completará automáticamente con el estado actual del pedido.</li>
                <li>Seleccione el <strong>nuevo estado</strong> al que desea cambiar el pedido.</li>
                <li>Verifique la <strong>fecha y hora del cambio</strong>. Por defecto se establece la fecha y hora actual.</li>
                <li>Seleccione el <strong>usuario</strong> que realiza el cambio. Por defecto es el usuario actual.</li>
                <li>Guarde el cambio de estado.</li>
            </ol>
            
            <p class="text-gray-500 italic">Nota: Al guardar el historial de cambio de estado, se actualizará automáticamente el estado del pedido al nuevo estado seleccionado.</p>
        </div>
    </x-filament::section>
</x-filament::page>