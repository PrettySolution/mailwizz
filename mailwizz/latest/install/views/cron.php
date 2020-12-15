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
<form method="post">
    <div class="box box-primary borderless">
        <div class="box-header">
            <h3 class="box-title">Cron jobs - Please add the following cron jobs to your server</h3>
        </div>
        <div class="box-body">
        <div class="alert alert-info">
            If you run into issues when setting up the cron jobs, please read <a target="_blank" href="https://kb.mailwizz.com/articles/what-cron-jobs-are-why-do-i-need-to-add-them-and-how/"><em>this</em></a> article for solutions.<br />
        </div>

Please note, below timings for running the cron jobs are the recommended ones, but if you feel you need to adjust them, go ahead.<br />    <br />
<pre>
<?php foreach (CronJobDisplayHandler::getCronJobsList() as $cronJobData) { ?>
# <?php echo $cronJobData['description'];?>

<?php echo $cronJobData['cronjob'];?>


<?php } ?>
</pre>
    
    If you have a control box like CPanel, Plesk, Webmin etc, you can easily add the cron jobs to the server cron.<br />
    In case you have shell access to your server, following commands should help you add the crons easily:
    <br /><br />
    
<pre>
# copy the current cron into a new file
crontab -l > mwcron

# add the new entries into the file
<?php foreach (CronJobDisplayHandler::getCronJobsList() as $cronJobData) { ?>
echo "<?php echo $cronJobData['cronjob'];?>" >> mwcron
<?php } ?>

# install the new cron
crontab mwcron

# remove the crontab file since it has been installed and we don't use it anymore.
rm mwcron
</pre>

Or, if you like working with VIM, then you know you can manually add them.<br />
Open the crontab in edit mode (<code>crontab -e</code>) add the cron jobs and save, that's all.
        <div class="clearfix"><!-- --></div>          
        </div>
        <div class="box-footer">
            <div class="pull-right">
                <button class="btn btn-primary btn-flat" value="1" name="next"><?php echo IconHelper::make('fa-arrow-circle-o-right');?> Cron jobs are installed, continue.</button>
            </div>
            <div class="clearfix"><!-- --></div>        
        </div>
    </div>
</form>