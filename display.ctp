<div id="header">
    <div class="cxomni-logo">
        <?php
        echo $this->Html->link(
            $this->Html->image("cxomni_logo_plain.png", ["alt" => "cx/omni Logo"]),
            "https://cxomni.net",
            ["escape" => false, "target" => "_blank"]
        );
        ?>
    </div>
    <div class="header-title">
        <h2><?php echo !empty($this->fetch('pageTitle')) ? $this->fetch('pageTitle') : 'Test'; ?></h2>
    </div>
    <div class="header-brand-select">
        <?php
            echo $this->Form->input('division', [
                'type' => 'select',
                'options' => ['Cx/omni', 'Eyeglass'],
                'label' => false
            ])
        ?>
    </div>
</div>