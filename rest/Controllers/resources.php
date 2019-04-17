<?php

class MyControllerResources extends ApiInterface
{
    public $classKey = 'modDocument';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'DESC';
    public $fields = 'id,parent,pagetitle,longtitle,description,introtext,menuindex,content';
    public $searchFields = array('pagetitle','longtitle','description');
    public $filtersFields = array('id','parent','published','deleted');

    /* @inheritdoc */
    public function initialize(){
        parent::initialize();
        $this->setProperty('published', true);
        $this->setProperty('deleted:!=', true);
    }
}