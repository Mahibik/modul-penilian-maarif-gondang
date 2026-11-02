@props(['icon', 'label', 'value', 'tone' => 'green', 'note' => null])
<div class="stat tone-{{ $tone }}">
  <div class="stat-top">
    <span class="stat-label">{{ $label }}</span>
    <span class="stat-ico"><x-icon :name="$icon" :size="18" /></span>
  </div>
  <div class="stat-val">{{ $value }}</div>
  @if($note)<div class="stat-note">{{ $note }}</div>@endif
</div>
