<?php
$this->data['header'] = $this->t('{expirycheck:expwarning:access_denied}');
$this->includeAtTemplateBase('includes/header.php');
?>
        <h2><?php echo $this->t('{expirycheck:expwarning:access_denied}'); ?></h2>
        <p>
            <?php
                echo $this->t(
                    '{expirycheck:expwarning:no_access_to}',
                    ['%NETID%' => htmlspecialchars($this->data['netId'])]
                );
            ?>
        </p> 
        <p>
            <?php echo $this->t('{expirycheck:expwarning:expiry_date_text}'); ?>
            <strong><?php echo htmlspecialchars($this->data['expireOnDate']); ?></strong>
        </p>
        <p><?php echo $this->t('{expirycheck:expwarning:contact_home}'); ?></p>
<?php
$this->includeAtTemplateBase('includes/footer.php');
