<?php
$rows = $this->Form->create();
foreach ($src_fields as $key => $name){
    $rows .= $this->Html->div('row',
        $this->Html->div('col-3', $name).
        $this->Html->div('col', $this->Form->input($key, [
            'options' => $dst_fields, 'div' => false, 'label' => false, 'empty' => true,]))
    );
}
$rows .= $this->Html->div('row', $this->Html->div('col', $this->Form->submit('Save')));
$rows .= $this->Form->end();
echo $this->Html->div('container', $rows);
?>

<script>
    $(function (){

        function update(){
            console.log('update');
            var selected = new Set();
            $('select').each((i, s) => {
                if ($(s).val() != '') selected.add($(s).val());
            });
            $('select').each((i, s) => {
                $(s).find('option').each((i, o) => {
                    if (selected.has($(o).attr('value')) && $(s).val() != $(o).attr('value')){
                        $(o).attr('hidden', true);
                    } else {
                        $(o).removeAttr('hidden');
                    }
                });
                console.log();
            });
            console.log(selected);
        }
        $('select').each((i, s) => {
            $(s).on('change', update);
        });
        update();
    });
</script>
