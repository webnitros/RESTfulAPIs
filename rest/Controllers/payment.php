<?php
class MyControllerPayment extends ApiInterface {
    public $classKey = 'msPayment';
    public $defaultSortField = 'rank';
    public $defaultLimit = 20;
    public $headers = array(
        'Access-Control-Allow-Methods' => 'GET',
    );
    public $fields = 'id,name,description,price,logo,active';


    /* @inheritdoc */
    protected function prepareListObject(xPDOObject $object)
    {
        $data = $object->toArray();
        $data['value'] = $data['id'];
        $data['text'] = $data['name'];
        return $data;
    }
    
    /* @inheritdoc */
    public function prepareListQueryAfterCount(xPDOQuery $c)
    {
        $c->where(array(
            'active' => true
        ));
        return $c;
    }

    /* @inheritdoc */
    public function prepareListQueryBeforeCount(xPDOQuery $c)
    {
        if ($where = $this->accessParams()) {
            $c->where($where);
        }
        return $c;
    }

}