<?php 
defined('C5_EXECUTE') or die('Access Denied.');

$form = Core::make('helper/form');
?>

<div class="modify-ip ccm-ui">
    <form class="form-horizontal" method="post">
        <input type="hidden" name="old_ip" value="<?php  echo $entry['old_ip'] ?>"/>
        <input type="hidden" name="token" value="<?php  echo $token->generate('restrict_login::ip.modify') ?>"/>

        <div class="form-group">
            <label class="control-label col-sm-4">
                <?php 
                echo t('Your current IP');
                ?>
            </label>

            <div class="col-sm-8">
                <?php 
                echo $form->text('current_ip', $current_ip, array('readonly' => 'readonly'));
                ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-4">
                <?php  echo t('IP address') ?> *
            </label>

            <div class="col-sm-8">
                <input class='form-control' type="text" name="ip" autofocus placeholder="<?php  echo t('E.g. %s', '143.33.122.17') ?>" value="<?php  echo $entry['ip'] ?>" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-4">
                <?php  echo t('Description') ?>
            </label>

            <div class="col-sm-8">
                <input class='form-control' type="text" name="description" maxlength="150" value="<?php  echo $entry['description'] ?>" />
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-8 col-sm-offset-4">
                <button class="auto-login-modify-ip-submit btn btn-primary">
                    <?php  echo t('Submit') ?>
                </button>
            </div>
        </div>
    </form>
</div>

