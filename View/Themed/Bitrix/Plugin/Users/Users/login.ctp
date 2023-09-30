<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center p-5">
            <div class="users form">
                <h2><?php echo $title_for_layout; ?></h2>
                <?php echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' => 'login')));
                    echo $this->Form->input('username',
                    array(
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label', 'text' => __d('croogo', 'Username')],
                        'div' => ['class' => 'mb-3']));
                    echo $this->Form->input('password', array(
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label', 'text' => __d('croogo', 'Password')],
                        'div' => ['class' => 'mb-3']));
                    echo $this->Form->Button(__d('croogo', 'Log In'), ['class' => 'btn btn-outline-primary']);
                    echo $this->Form->end(); ?>
                <?php
                echo $this->Html->link(__d('users', 'Register'), array(
                    'controller' => 'users', 'action' => 'add',
                ), ['class' => 'me-3']);
                echo $this->Html->link(__d('croogo', 'Forgot password?'), array(
                    'controller' => 'users', 'action' => 'forgot',
                ));
                ?>
            </div>
        </div>
    </div>
</div>

