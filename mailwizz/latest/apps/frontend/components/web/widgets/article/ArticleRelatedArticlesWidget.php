<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArticleRelatedArticlesWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ArticleRelatedArticlesWidget extends CWidget
{
    public $article;
    
    public $limit = 8;
    
    public $excerptLength = 100;

    public $columns = 4;
    
    public $columnsCssClass = 'col-lg-3';
    
    public function run()
    {
        if (empty($this->article->activeCategories)) {
            return;
        }
        
        $categories = array();
        foreach ($this->article->categories as $category) {
            $categories[] = $category->category_id;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('t.status', Article::STATUS_PUBLISHED);
        $criteria->addCondition('t.article_id != :id');
        $criteria->params[':id'] = $this->article->article_id;
        $criteria->with = array(
            'activeCategories' => array(
                'together'    => true,
                'joinType'    => 'INNER JOIN',
            ),
        );
        $criteria->addInCondition('activeCategories.category_id', $categories);
        $criteria->limit = (int)$this->limit;
        
        $articles = Article::model()->findAll($criteria);
        if (empty($articles)) {
            return;
        }
        
        $columns = array();
        while (!empty($articles)) {
            for ($i = 0; $i < (int)$this->columns; ++$i) {
                if (empty($articles)) {
                    break;
                }
                if (!isset($columns[$i])) {
                    $columns[$i] = array();
                }
                $columns[$i][] = array_shift($articles);
            }
        }

        $this->render('related', compact('columns'));
    }
}