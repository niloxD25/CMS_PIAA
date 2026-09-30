@extends('admin.layouts.admin')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l d F Y') }}</p>
        </div>
    </div>

    <!-- Bannière de bienvenue Admin -->
    <div class="welcome-banner-admin mb-6">
        <div class="flex items-center space-x-4">
            <div class="welcome-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h2 class="welcome-title">Bienvenue, {{ $user->name }} !</h2>
                <p class="welcome-subtitle">Vous avez un accès <strong>Administrateur complet</strong> au système</p>
            </div>
        </div>
    </div>

    <!-- Courbe interactive des activités -->
    <div class="bg-white rounded-lg overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Activités des Utilisateurs</h2>
            <!-- Contrôles de navigation par date -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <label for="startDate" class="form-label">Du :</label>
                    <input type="date" id="startDate" class="date-picker border border-gray-300 rounded px-3 py-1 text-sm">
                </div>
                <div class="flex items-center space-x-2">
                    <label for="endDate" class="form-label">Au :</label>
                    <input type="date" id="endDate" class="date-picker border border-gray-300 rounded px-3 py-1 text-sm">
                </div>
                <button id="applyDateRange" class="create-btn ">
                    Appliquer
                </button>
                <button id="resetZoom" class="cancel-btn">
                    Reset
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="activityChart" style="height: 400px; width: 100%;"></div>
        </div>
    </div>

    <!-- Actions et Activités -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actions Rapides Admin -->
        <div class="bg-white rounded-lg overflow-hidden" style="border-radius: var(--border-radius);">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Gestion Rapide</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.users.create') }}" class="quick-action-btn bg-blue-50 border-blue-200 hover:bg-blue-100">
                        <i class="fas fa-user-plus text-blue-600"></i>
                        <span>Nouvel Utilisateur</span>
                    </a>
                    <a href="{{ route('admin.projets.create') }}" class="quick-action-btn bg-green-50 border-green-200 hover:bg-green-100">
                        <i class="fas fa-plus-circle text-green-600"></i>
                        <span>Nouveau Projet</span>
                    </a>
                    <a href="{{ route('admin.users.types-compte.index') }}" class="quick-action-btn bg-purple-50 border-purple-200 hover:bg-purple-100">
                        <i class="fas fa-cog text-purple-600"></i>
                        <span>Types de Compte</span>
                    </a>
                    <a href="{{ route('admin.audits.index') }}" class="quick-action-btn bg-orange-50 border-orange-200 hover:bg-orange-100">
                        <i class="fas fa-history text-orange-600"></i>
                        <span>Historique</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="bg-white rounded-lg overflow-hidden" style="border-radius: var(--border-radius);">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Activité Récente</h2>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @forelse ($audits as $audit)
                    <div class="activity-item">
                        <div class="activity-icon 
                            @if($audit->event === 'created') activity-created
                            @elseif($audit->event === 'updated') activity-updated
                            @elseif($audit->event === 'deleted') activity-deleted
                            @elseif($audit->event === 'restored') activity-restored
                            @else activity-default
                            @endif">
                            @if($audit->event === 'created')
                                <i class="fas fa-plus"></i>
                            @elseif($audit->event === 'updated')
                                <i class="fas fa-edit"></i>
                            @elseif($audit->event === 'deleted')
                                <i class="fas fa-trash"></i>
                            @elseif($audit->event === 'restored')
                                <i class="fas fa-undo"></i>
                            @else
                                <i class="fas fa-info-circle"></i>
                            @endif
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">
                                {{ ucfirst($audit->event) }}
                                @if ($audit->auditable_type)
                                    <span class="text-gray-600">
                                        sur {{ class_basename($audit->auditable_type) }}
                                    </span>
                                @endif
                            </p>
                            <p class="activity-time">
                                {{ $audit->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('admin.audits.show', $audit->id) }}" class="activity-action" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-history text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500">Aucune activité récente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Guide Administrateur -->
    <div class="bg-white rounded-lg overflow-hidden mt-6" style="border-radius: var(--border-radius);">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Guide de l'Administrateur</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="guide-item">
                    <div class="guide-icon bg-blue-100">
                        <i class="fas fa-users-cog text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Gestion des Utilisateurs</h3>
                        <p class="guide-description">Créez et gérez les comptes utilisateurs et leurs permissions</p>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon bg-green-100">
                        <i class="fas fa-shield-alt text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Sécurité et Accès</h3>
                        <p class="guide-description">Configurez les niveaux d'accès et la sécurité du système</p>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon bg-purple-100">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Supervision</h3>
                        <p class="guide-description">Surveillez l'activité et générez des rapports détaillés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- amCharts resources -->
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<script>
am5.ready(function() {
    var root = am5.Root.new("activityChart");
    root.setThemes([am5themes_Animated.new(root)]);

    var chart = root.container.children.push(am5xy.XYChart.new(root, {
        focusable: true,
        panX: true,
        panY: false,
        wheelX: "panX",
        wheelY: "zoomX",
        pinchZoomX: true
    }));

    var scrollbarX = am5xy.XYChartScrollbar.new(root, {
        orientation: "horizontal",
        height: 16
    });
    chart.set("scrollbarX", scrollbarX);
    chart.bottomAxesContainer.children.push(scrollbarX);

    var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
        baseInterval: { timeUnit: "day", count: 1 },
        renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 50 }),
        tooltip: am5.Tooltip.new(root, {}),
        maxDeviation: 0.5
    }));

    var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        renderer: am5xy.AxisRendererY.new(root, {}),
        extraMax: 0.1,
        strictMinMax: false,
        numberFormat: "#",
        min: 0,
        maxPrecision: 0
    }));

    var chartData = @json($chartData);

    var originalMinDate, originalMaxDate;

    function createSeries(name, field, color, tooltipText) {
        var series = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: name,
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: field,
            valueXField: "date",
            tooltip: am5.Tooltip.new(root, { labelText: tooltipText }),
            clustered: true
        }));

        series.columns.template.setAll({
            width: 15,
            cornerRadiusTL: 5,
            cornerRadiusTR: 5,
            fill: color,
            stroke: color
        });

        series.data.processor = am5.DataProcessor.new(root, {
            dateFormat: "yyyy-MM-dd",
            dateFields: ["date"]
        });

        series.data.setAll(chartData);

        return series;
    }

    var seriesCreated = createSeries("Créations", "created", am5.color(0x61CEA6), "Créations : {valueY}");
    var seriesUpdated = createSeries("Modifications", "updated", am5.color(0x0071bd), "Modifications : {valueY}");
    var seriesDeleted = createSeries("Suppressions", "deleted", am5.color(0xE34A4A), "Suppressions : {valueY}");
    var seriesRestored = createSeries("Restaurations", "restored", am5.color(0xEFBA75), "Restaurations : {valueY}");

    var legend = chart.children.push(am5.Legend.new(root, {
        centerX: am5.p50,
        x: am5.p50
    }));
    legend.data.setAll(chart.series.values);

    chart.set("cursor", am5xy.XYCursor.new(root, { behavior: "none" }));

    function zoomToDateRange(startDate, endDate) {
        var start = new Date(startDate);
        var end = new Date(endDate);
        
        if (isNaN(start.getTime()) || isNaN(end.getTime())) {
            console.error('Dates invalides');
            return;
        }
        
        xAxis.zoomToDates(start, end);
    }

    function resetZoom() {
        if (xAxis.zoomable) {
            xAxis.zoomable.reset();
        }
        
        if (originalMinDate && originalMaxDate) {
            xAxis.zoomToDates(originalMinDate, originalMaxDate);
        } else {
            if (chartData && chartData.length > 0) {
                var dates = chartData.map(function(item) {
                    return new Date(item.date);
                }).filter(function(date) {
                    return !isNaN(date.getTime());
                });
                
                if (dates.length > 0) {
                    var minDate = new Date(Math.min.apply(null, dates));
                    var maxDate = new Date(Math.max.apply(null, dates));
                    xAxis.zoomToDates(minDate, maxDate);
                }
            }
        }
    }

    var endDate = new Date();
    var startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);

    document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

    seriesCreated.events.on("datavalidated", function() {
        if (chartData && chartData.length > 0) {
            var dates = chartData.map(function(item) {
                return new Date(item.date);
            }).filter(function(date) {
                return !isNaN(date.getTime());
            });
            
            if (dates.length > 0) {
                originalMinDate = new Date(Math.min.apply(null, dates));
                originalMaxDate = new Date(Math.max.apply(null, dates));
            }
        }
        
        setTimeout(function() {
            zoomToDateRange(startDate, endDate);
        }, 100);
    });

    document.getElementById('applyDateRange').addEventListener('click', function() {
        var startDateValue = document.getElementById('startDate').value;
        var endDateValue = document.getElementById('endDate').value;
        
        if (startDateValue && endDateValue) {
            zoomToDateRange(startDateValue, endDateValue);
        } else {
            alert('Veuillez sélectionner une plage de dates valide.');
        }
    });

    document.getElementById('resetZoom').addEventListener('click', function() {
        var endDate = new Date();
        var startDate = new Date();
        startDate.setDate(startDate.getDate() - 30);
        
        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
        
        setTimeout(function() {
            resetZoom();
        }, 50);
    });

    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            var startDateValue = document.getElementById('startDate').value;
            var endDateValue = document.getElementById('endDate').value;
            
            if (startDateValue && endDateValue) {
                zoomToDateRange(startDateValue, endDateValue);
            }
        }
    });

    chart.appear(1000, 100);
});
</script>

<style>
/* Bannière de bienvenue Admin */
.welcome-banner-admin {
    background: var(--color-warning);
    color: var(--color-text-light);
    padding: 2rem;
    border-radius: var(--border-radius);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.welcome-banner-admin .welcome-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.welcome-banner-admin .welcome-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.welcome-banner-admin .welcome-subtitle {
    opacity: 0.9;
    font-size: 1rem;
}

.admin-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    border-radius: var(--border-radius);
    border: 1px solid;
    text-decoration: none;
    transition: all 0.2s ease;
    text-align: center;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.quick-action-btn span {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-radius: var(--border-radius);
    margin-bottom: 0.5rem;
    transition: background-color 0.2s;
}

.activity-item:hover {
    background-color: #f9fafb;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.875rem;
    color: white;
}

.activity-created { background: var(--color-success); }
.activity-updated { background: var(--color-primary); }
.activity-deleted { background: var(--color-error); }
.activity-restored { background: var(--color-warning); }
.activity-default { background: #6b7280; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.75rem;
    color: #6b7280;
}

.activity-action {
    color: #6b7280;
    padding: 0.25rem;
    border-radius: 4px;
    transition: color 0.2s;
}

.activity-action:hover {
    color: var(--color-primary);
}

.guide-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.guide-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.guide-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.guide-description {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
}

#activityChart {
    width: 100%;
    max-width: 100%;
}

.date-picker {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.date-picker:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

#applyDateRange, #resetZoom {
    transition: all 0.2s ease;
}

#applyDateRange:hover, #resetZoom:hover {
    transform: translateY(-1px);
}

/* Responsive design pour les petits écrans */
@media (max-width: 768px) {
    .px-6.py-4.border-b.border-gray-200 {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .flex.items-center.space-x-4 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .date-picker {
        width: 120px;
    }
}
</style>
@endsection
