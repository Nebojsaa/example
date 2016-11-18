<div id="header">
    <div class="header-title">
        <h2><?php echo !empty($this->fetch('pageTitle')) ? $this->fetch('pageTitle') : 'Test'; ?></h2>
    </div>
    <div class="header-brand-select">
        <?php
            echo $this->Form->input('division', [
                'type' => 'select',
                'options' => [],
                'label' => false
            ])
        ?>
    </div>
</div>
