<div class="flash-stack" aria-live="polite" aria-atomic="true">
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
</div>

<style>
  .flash-stack {
    position: fixed;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    width: min(520px, calc(100% - 24px));
    z-index: 1200;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
  }

  .flash-stack .flash-alert {
    pointer-events: auto;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }

  /* Base styles if page-level styles are missing */
  .flash-alert {
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    animation: fadeIn 0.3s ease-in-out;
  }

  .flash-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .flash-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-5px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>