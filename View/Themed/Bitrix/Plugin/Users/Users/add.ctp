<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center p-5">
            <div class="users form">
                <h2><?php echo $title_for_layout; ?></h2>
                <?php echo $this->Form->create('User');?>
                <?php
                $params = ['class' => 'form-control', 'label' => ['class' => 'form-label'], 'div' => ['class' => 'mb-3']];
                echo $this->Form->input('username', $params);
                echo $this->Form->input('password', $params + array('value' => ''));
                echo $this->Form->input('verify_password', $params + array('type' => 'password', 'value' => ''));
                echo $this->Form->input('name', $params);
                echo $this->Form->input('email', $params);
                echo $this->Form->hidden('website');
                echo $this->Form->button('Submit', ['class' => 'btn btn-outline-primary']);
                ?>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>