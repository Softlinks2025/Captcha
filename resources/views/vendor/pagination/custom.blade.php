@if ($paginator->hasPages())
    <div class="pagination">
        <div class="flex items-center justify-center w-100">
            {{-- Only Page Number Buttons (Mobile & Desktop) --}}
            <div class="pagination-links" style="gap: 4px;">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-ellipsis">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        .pagination-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .pagination-link,
        .pagination span {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: transform 0.2s, box-shadow 0.3s, background 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }
        .pagination-link:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        .pagination-current {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        .pagination-ellipsis {
            padding: 8px 12px;
            color: var(--text-color);
            opacity: 0.7;
        }
        @media (max-width: 640px) {
            .pagination-links {
                gap: 2px;
            }
            .pagination-link,
            .pagination span {
                padding: 6px 12px;
                min-width: 36px;
                height: 36px;
                font-size: 0.8rem;
            }
        }
    </style>
@endif 