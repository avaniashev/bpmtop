<html>
<head>
    <?php
        echo $this->Html->css([
            'bootstrap.css',
            '/lib/bootstrap_datepicker/css/bootstrap-datepicker.min.css',
            '/lib/bootstrap_icons/font/bootstrap-icons.css',
            'style.css',
        ]);
        echo $this->Html->script([
            '/lib/jquery-3.6.4.min.js',
            'bootstrap.bundle.js',
            '/lib/bootstrap_datepicker/js/bootstrap-datepicker.min.js'
        ]);
        echo $this->Blocks->get('css');
        echo $this->Blocks->get('script');
    ?>
</head>
<body>
<?php
echo $content_for_layout;
$flash = $this->Flash->render('auth').$this->Flash->render();
if ($flash){
    echo $this->Html->div('toast-container position-fixed bottom-0 end-0 p-3', $flash);
}
echo $this->Html->script('script.js', ['inline' => true]);
?>
</body>
</html>