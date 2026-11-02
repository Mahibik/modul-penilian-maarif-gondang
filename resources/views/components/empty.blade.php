@props(['title' => 'Belum ada data', 'icon' => 'inbox'])
<div class="empty">
  <span class="empty-ico"><x-icon :name="$icon" :size="22" /></span>
  <b>{{ $title }}</b>
  @if(trim($slot))<p>{{ $slot }}</p>@endif
</div>
