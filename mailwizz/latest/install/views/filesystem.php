<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
?>
<?php if($result > 0) { ?>
<div class="alert alert-success alert-block">
    Congratulations! The file system satisfies all requirements by MailWizz EMA.
</div>
<?php } elseif($result < 0) { ?>
<div class="alert alert-warning alert-block">
    The file system satisfies the minimum requirements by MailWizz EMA.<br />
    Please pay attention to the warnings listed below if your application will use the corresponding features.
</div>
<?php } else { ?>
<div class="alert alert-danger alert-block">
    Unfortunately the file system does not satisfy the requirements by MailWizz EMA.<br />
    Please pay attention to the errors listed below and fix them.
    <hr />
    If you have shell access to this server, following commands will properly chmod all the needed files:<br />
    <strong>chmod +x <?php echo MW_APPS_PATH;?>/console/commands/shell/set-dir-perms</strong><br />
    <strong><?php echo MW_APPS_PATH;?>/console/commands/shell/set-dir-perms</strong>
</div>
<?php } ?>

<form method="post">
    <div class="box box-primary borderless">
        <div class="box-header">
            <h3 class="box-title">File system checks</h3>
        </div>
        <div class="box-body">
            <div class="callout callout-info">
                Please see following <a href="https://kb.mailwizz.com/articles/folders-writable-web-server/" target="_blank">KB article</a> to understand why we use 0777 for directory permissions.
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>Name</th>
                        <th>Result</th>
                        <th>File / Directory</th>
                        <th>Memo</th>
                    </tr>
                    <?php foreach($requirements as $requirement): ?>
                    <tr>
                        <td><?php echo $requirement[0]; ?></td>
                        <td class="<?php echo $requirement[3] ? 'success' : ($requirement[1] ? 'danger' : 'warning'); ?>">
                        <?php echo $requirement[3] ? 'Passed' : ($requirement[1] ? 'Failed' : 'Warning'); ?>
                        </td>
                        <td><?php echo $requirement[2]; ?></td>
                        <td><?php echo $requirement[4]; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="clearfix"><!-- --></div>      
        </div>
        <div class="box-footer">
            <div class="pull-right">
                <button class="btn btn-primary btn-flat" value="<?php echo $result?>" name="result"><?php echo IconHelper::make('fa-arrow-circle-o-right');?> <?php if ($result != 0) { ?> Next <?php } else { ?> Check again <?php }?></button>
            </div>
            <div class="clearfix"><!-- --></div>        
        </div>
    </div>
</form>