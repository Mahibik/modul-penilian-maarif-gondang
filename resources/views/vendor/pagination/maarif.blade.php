@if ($paginator->hasPages())
<nav class="pager" role="navigation" aria-label="Halaman">
  <span class="muted">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }} · {{ $paginator->total() }} data</span>
  <span class="actions">
    @if ($paginator->onFirstPage())
      <span class="btn btn-ghost btn-sm" aria-disabled="true" style="opacity:.45">Sebelumnya</span>
    @else
      <a class="btn btn-ghost btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
    @endif
    @if ($paginator->hasMorePages())
      <a class="btn btn-ghost btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
    @else
      <span class="btn btn-ghost btn-sm" aria-disabled="true" style="opacity:.45">Berikutnya</span>
    @endif
  </span>
</nav>
@endif
