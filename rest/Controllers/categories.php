<?php
class MyControllerCategories extends ApiInterface {
    public $classKey = 'msCategory';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'DESC';
    public $fields = 'id,parent,pagetitle,menuindex';
    public $defaultLimit = 20;
    public $filtersFields = array('parent','published','deleted','class_key');

    /* @inheritdoc */
    public function initialize(){
        parent::initialize();
        $this->setProperty('published', true);
        $this->setProperty('deleted:!=', true);
    }
}