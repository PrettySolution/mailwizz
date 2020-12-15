<!DOCTYPE html>
<html dir="<?php echo $this->htmlOrientation;?>">
<head>
    <meta charset="<?php echo Yii::app()->charset;?>">
    <title><?php echo CHtml::encode($pageMetaTitle);?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo CHtml::encode($pageMetaDescription);?>">
    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
    <body class="<?php echo $this->bodyClasses;?>" style="width: <?php echo $attributes['width'];?>; height: <?php echo $attributes['height'];?>;">
    <?php $this->afterOpeningBodyTag;?>
        <div class="container-fluid">
            <div class="row">
                <div id="notify-container">
                    <?php echo Yii::app()->notify->show();?>
                </div>
                <div class="col-lg-12">
                    <?php echo $content;?>
                </div>
            </div>
        </div>
    </body>
</html>