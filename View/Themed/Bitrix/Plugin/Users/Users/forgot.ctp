<?php
$this->extend('Parents/center');
?>
<div class="users form">
	<h2><?php echo $title_for_layout; ?></h2>
	<?php echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' => 'forgot')));?>
		<?php
			echo $this->Form->input('username', array(
                    'label' => ['text' => __d('croogo', 'Username')],
                    'class' => 'form-control',
                    'div' => ['class' => 'mb-3']
                ));
            echo $this->Form->button(__d('croogo', 'Submit'), ['class' => 'btn btn-outline-primary'])
		?>
	<?php echo $this->Form->end();?>
</div>
