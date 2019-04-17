<?php

class MyControllerProducts extends ApiInterface
{
    public $classKey = 'msProduct';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'DESC';
    public $fields = 'id,parent,pagetitle,longtitle,description,introtext,menuindex,content,article,price,old_price,weight,image,thumb,vendor,vendor.name,made_in,new,popular,favorite,tags,color,size';
    public $searchFields = array('pagetitle','longtitle','description');
    public $filtersFields = array('id','parent','published','deleted');

    /* @inheritdoc */
    public function initialize(){
        parent::initialize();
        $this->setProperty('published', true);
        $this->setProperty('deleted:!=', true);
    }
}