@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <ul class="pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-prev">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}{{ strpos($paginator->previousPageUrl(), '?') ? '&' : '?' }}{{ http_build_query(request()->except('page')) }}" 
                       class="pagination-link pagination-prev" 
                       rel="prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="pagination-item disabled">
                        <span class="pagination-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active">
                                <span class="pagination-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="pagination-item">
                                <a href="{{ $url }}{{ strpos($url, '?') ? '&' : '?' }}{{ http_build_query(request()->except('page')) }}" 
                                   class="pagination-link">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}{{ strpos($paginator->nextPageUrl(), '?') ? '&' : '?' }}{{ http_build_query(request()->except('page')) }}" 
                       class="pagination-link pagination-next" 
                       rel="next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-next">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif

<style>
/* Styles pour la pagination */
.pagination-container {
    margin-top: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.per-page-label {
    font-size: 0.9rem;
    color: var(--brun-clair);
    font-weight: 500;
}

.per-page-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gris);
    border-radius: var(--border-radius);
    background: white;
    color: var(--brun-fonce);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.per-page-select:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 2px rgba(159, 92, 37, 0.1);
}

.per-page-text {
    font-size: 0.9rem;
    color: var(--brun-clair);
}

.pagination-info {
    font-size: 0.9rem;
    color: var(--brun-fonce);
    text-align: center;
}

.pagination-links {
    display: flex;
    justify-content: center;
}

.pagination-list {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.5rem;
    align-items: center;
    justify-content: center;
}

.pagination-item {
    display: inline-block;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 0.75rem;
    border-radius: 50%;
    background: white;
    color: var(--color-primary-flou);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.pagination-link:hover {
    background: var(--color-accent);
    color: white;
    transform: translateY(-1px);
}

.pagination-item.active .pagination-link {
    background: var(--color-accent);
    color: white;
    min-width: 40px;
    height: 40px;
}

.pagination-item.disabled .pagination-link {
    background: var(--gris);
    color: var(--brun-clair);
    cursor: not-allowed;
    transform: none;
}

.pagination-prev,
.pagination-next {
    font-size: 0.8rem;
}

/* Responsive pour la pagination */
@media (max-width: 768px) {
    .pagination-controls {
        flex-direction: column;
        text-align: center;
    }
    
    .per-page-selector {
        justify-content: center;
    }
    
    .pagination-info {
        order: -1;
    }
    
    .pagination-link {
        min-width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }
}
</style>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}
</script>