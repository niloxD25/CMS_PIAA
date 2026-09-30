@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Détails de l\'Activité')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <span class="audit-badge-large audit-{{ $audit->event }}">
                <i class="fas fa-{{ $audit->event === 'created' ? 'plus' : ($audit->event === 'updated' ? 'edit' : ($audit->event === 'deleted' ? 'trash' : ($audit->event === 'restored' ? 'undo' : 'info'))) }} mr-2"></i>
                {{ ucfirst($audit->event) }}
            </span>
        </div>
        <div class="flex space-x-2">
            <a href="{{ back()->getTargetUrl() }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1 text-sm text-gray-900">{{ class_basename($audit->auditable_type) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $audit->auditable_id }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Utilisateur</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($audit->user)
                                {{ $audit->user->name }} ({{ $audit->user->email }})
                            @else
                                <span class="text-gray-400">Non spécifié</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date et heure</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $audit->created_at->format('d/m/Y à H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Adresse IP</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $audit->ip_address ?? 'Non disponible' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">User Agent</label>
                        <p class="mt-1 text-sm text-gray-900 text-xs">{{ Str::limit($audit->user_agent, 180) ?? 'Non disponible' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails des modifications -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Détails des modifications</h2>
                </div>
                <div class="p-6">
                    @if($audit->event === 'created')
                        <div class="space-y-4">
                            <h3 class="text-md font-medium text-gray-900">Nouvelles valeurs :</h3>
                            @if($audit->new_values && count($audit->new_values) > 0)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="space-y-3">
                                        @foreach($audit->new_values as $key => $value)
                                            <div class="flex justify-between items-start py-2 border-b border-green-100 last:border-b-0">
                                                <span class="font-medium text-green-800 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="text-green-700 text-right break-all ml-4">
                                                    @if(is_array($value))
                                                        <pre class="text-xs whitespace-pre-wrap">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @elseif(is_bool($value))
                                                        {{ $value ? 'Oui' : 'Non' }}
                                                    @elseif($value === null)
                                                        <span class="text-gray-400">Null</span>
                                                    @elseif($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, 'date'))
                                                        {{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500">Aucune donnée disponible</p>
                            @endif
                        </div>
                    @elseif($audit->event === 'updated')
                        <div class="space-y-6">
                            <h3 class="text-md font-medium text-gray-900">Modifications :</h3>
                            @if($audit->old_values && count($audit->old_values) > 0)
                                <div class="space-y-4">
                                    @foreach($audit->old_values as $key => $oldValue)
                                        @php
                                            $newValue = $audit->new_values[$key] ?? null;
                                            $hasChanged = $oldValue != $newValue;
                                        @endphp
                                        @if($hasChanged)
                                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                                    <span class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 divide-x divide-gray-200">
                                                    <div class="p-3 bg-red-50">
                                                        <label class="text-xs font-medium text-red-600 mb-1 block">Ancienne valeur:</label>
                                                        <div class="text-sm text-red-700 break-words">
                                                            @if(is_array($oldValue))
                                                                <pre class="text-xs whitespace-pre-wrap">{{ json_encode($oldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                            @elseif(is_bool($oldValue))
                                                                {{ $oldValue ? 'Oui' : 'Non' }}
                                                            @elseif($oldValue === null)
                                                                <span class="text-gray-400">Null</span>
                                                            @elseif($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, 'date'))
                                                                {{ \Carbon\Carbon::parse($oldValue)->format('d/m/Y H:i') }}
                                                            @else
                                                                {{ $oldValue }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-green-50">
                                                        <label class="text-xs font-medium text-green-600 mb-1 block">Nouvelle valeur:</label>
                                                        <div class="text-sm text-green-700 break-words">
                                                            @if(is_array($newValue))
                                                                <pre class="text-xs whitespace-pre-wrap">{{ json_encode($newValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                            @elseif(is_bool($newValue))
                                                                {{ $newValue ? 'Oui' : 'Non' }}
                                                            @elseif($newValue === null)
                                                                <span class="text-gray-400">Null</span>
                                                            @elseif($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, 'date'))
                                                                {{ \Carbon\Carbon::parse($newValue)->format('d/m/Y H:i') }}
                                                            @else
                                                                {{ $newValue }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">Aucune modification détectée</p>
                            @endif
                        </div>
                    @elseif($audit->event === 'deleted')
                        <div class="space-y-4">
                            @if($audit->auditable && method_exists($audit->auditable, 'trashed') && $audit->auditable->trashed())
                                <div class="px-6 py-4 bg-yellow-50 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                    <span class="text-yellow-700 text-sm flex-1">
                                        Cet objet est actuellement supprimé (soft delete). Vous pouvez le restaurer ou le supprimer définitivement.
                                    </span>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.audits.restore', $audit->id) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                                                <i class="fas fa-undo mr-2"></i> Restaurer
                                            </button>
                                        </form>
                                        @if(auth()->user()->type_compte_id == 1)
                                        <form method="POST" action="{{ route('admin.audits.forceDelete', $audit->id) }}" onsubmit="return confirm('⚠️ Cette action supprimera définitivement l\'objet. Cette action est réservée aux administrateurs. Continuer ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                                                <i class="fas fa-times mr-2"></i> Supprimer définitivement
                                            </button>
                                        </form>
                                        @else
                                        <div class="px-4 py-2 bg-gray-400 text-white rounded-lg text-sm font-medium cursor-not-allowed" title="Réservé aux administrateurs">
                                            <i class="fas fa-times mr-2"></i> Supprimer définitivement
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="px-6 py-4 bg-yellow-50 flex justify-between items-center">
                                    <span class="text-red-700 text-sm">
                                        Cet objet a été restoré ou n'existe plus dans la base de données.
                                    </span>
                                </div>
                            @endif
                            
                            <h3 class="text-md font-medium text-gray-900">Données supprimées :</h3>
                            @if($audit->old_values && count($audit->old_values) > 0)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="space-y-3">
                                        @foreach($audit->old_values as $key => $value)
                                            <div class="flex justify-between items-start py-2 border-b border-red-100 last:border-b-0">
                                                <span class="font-medium text-red-800 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="text-red-700 text-right break-all ml-4">
                                                    @if(is_array($value))
                                                        <pre class="text-xs whitespace-pre-wrap">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @elseif(is_bool($value))
                                                        {{ $value ? 'Oui' : 'Non' }}
                                                    @elseif($value === null)
                                                        <span class="text-gray-400">Null</span>
                                                    @elseif($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, 'date'))
                                                        {{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500">Aucune donnée disponible</p>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500">Aucun détail disponible pour ce type d'événement.</p>
                    @endif
                </div>
            </div>

            <!-- Résumé des modifications -->
            @if($audit->event === 'updated' && $audit->old_values && count($audit->old_values) > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Résumé des modifications</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        @php
                            $changedFields = array_keys($audit->old_values);
                            $changedCount = count($changedFields);
                        @endphp
                        <p class="text-sm text-gray-600">
                            <strong>{{ $changedCount }}</strong> champ(s) modifié(s) :
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($changedFields as $field)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium capitalize">
                                    {{ str_replace('_', ' ', $field) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- URL et Méthode -->
            @if($audit->url || $audit->method)
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Requête HTTP</h2>
                </div>
                <div class="p-4 space-y-3">
                    @if($audit->method)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Méthode</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $audit->method }}</p>
                    </div>
                    @endif
                    @if($audit->url)
                    <div>
                        <label class="text-sm font-medium text-gray-500">URL</label>
                        <p class="mt-1 text-sm text-gray-900 break-all">{{ $audit->url }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informations sur l'objet audité -->
            @if($audit->auditable)
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Objet Audité : {{ class_basename($audit->auditable_type) }}
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($audit->auditable->getAttributes() as $key => $value)
                        <div class="flex justify-between border-b border-gray-100 py-1">
                            <span class="text-gray-600 font-medium capitalize">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="text-gray-800 break-all ml-4">
                                @if(is_bool($value))
                                    {{ $value ? 'Oui' : 'Non' }}
                                @elseif($value === null)
                                    <span class="text-gray-400">Null</span>
                                @elseif($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at' || str_contains($key, 'date'))
                                    {{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Fonction pour améliorer l'affichage des données longues
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des tooltips pour les valeurs tronquées
    const valueElements = document.querySelectorAll('.text-sm.break-words');
    
    valueElements.forEach(element => {
        if (element.scrollWidth > element.clientWidth) {
            element.title = element.textContent;
        }
    });
    
    // Formater les dates dans les tableaux JSON
    const jsonPreElements = document.querySelectorAll('pre');
    jsonPreElements.forEach(pre => {
        try {
            const jsonContent = pre.textContent;
            const parsed = JSON.parse(jsonContent);
            
            // Formater les dates dans les tableaux
            const formatDates = (obj) => {
                if (typeof obj !== 'object' || obj === null) return obj;
                
                if (Array.isArray(obj)) {
                    return obj.map(item => formatDates(item));
                }
                
                const formatted = {};
                for (const [key, value] of Object.entries(obj)) {
                    if (typeof value === 'string' && 
                        (key.includes('date') || key.includes('time') || key.includes('at')) &&
                        /^\d{4}-\d{2}-\d{2}/.test(value)) {
                        try {
                            formatted[key] = new Date(value).toLocaleString('fr-FR');
                        } catch (e) {
                            formatted[key] = value;
                        }
                    } else if (typeof value === 'object' && value !== null) {
                        formatted[key] = formatDates(value);
                    } else {
                        formatted[key] = value;
                    }
                }
                return formatted;
            };
            
            const formatted = formatDates(parsed);
            pre.textContent = JSON.stringify(formatted, null, 2);
        } catch (e) {
            
        }
    });
});
</script>

<style>
.audit-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    display: inline-flex;
    align-items: center;
}

.audit-created { background: var(--color-success); }
.audit-updated { background: var(--color-primary); }
.audit-deleted { background: var(--color-error); }
.audit-restored { background: var(--color-warning); }

/* Styles pour l'affichage des données */
pre {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.75rem;
    line-height: 1.4;
    margin: 0;
}

.break-words {
    word-break: break-word;
    overflow-wrap: break-word;
}

.capitalize {
    text-transform: capitalize;
}

/* Style pour le bouton désactivé */
.cursor-not-allowed {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

@endsection