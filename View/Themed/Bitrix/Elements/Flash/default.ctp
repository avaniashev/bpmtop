<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
        <i class="bi <?php echo $key == 'auth' ? 'bi-key' : 'bi-info-circle' ?> me-3"></i>
        <strong class="me-auto"><?php echo $key == 'auth' ? __('Auth info') : __('Info')?></strong>
        <small>Just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        <?php echo $message ?>
    </div>
</div>