<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="message success toast-notification" id="flash-<?= uniqid() ?>" onclick="this.remove()">
    <i class="fas fa-check-circle"></i>
    <?= $message ?>
</div>
<script>
    document.querySelectorAll('.toast-notification').forEach(function(element) {
        setTimeout(function() {
            element.classList.add('fade-out');
            setTimeout(function() {
                if (element) element.remove();
            }, 500);
        }, 3000);
    });
</script>
