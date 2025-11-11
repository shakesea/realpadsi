@if (session('success'))
<div class="flash-alert flash-success">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="flash-alert flash-error">
  {{ session('error') }}
</div>
@endif