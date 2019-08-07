<?php
include MODX_BASE_PATH.'rest/Controllers/products.php';
class MyControllerOrders extends ApiInterface {
    public $classKey = 'msOrder';
    public $classKeyFilter = false;
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';
    public $fields = 'id,user_id,num,createdon,updatedon,cost,cart_cost,delivery_cost,status,delivery,payment,address,context,comment,properties,type,products,Status,Delivery,Payment,Address,show';
    public $defaultLimit = 2;
    public $filtersFields = array('user_id');
    protected $protected = true;

    /* @inheritdoc */
    public function initialize(){
        parent::initialize();
        #$this->setProperty('user_id', 149);
    }

    /* @inheritdoc */
    public function prepareListObject(xPDOObject $object)
    {
        // Из многих объектов
        $criteria = array('id' => $object->get('id'));

        // Из одного объекта
        $new = $this->modx->getObjectGraph('msOrder', '{"Status":{},"Delivery":{},"Payment":{},"Address":{},"Products":{}}', $criteria);

        $products = array();
        foreach ($new->Products as $product) {
            $products[] = $product->toArray();
        }


        $object->set('products', $products);
        $object->set('Status', $object->Status->toArray());
        $object->set('Delivery', $object->Delivery->toArray());
        $object->set('Payment', $object->Payment->toArray());
        $object->set('Address', $object->Address->toArray());
        $object->set('show', (boolean)false);

        return parent::prepareListObject($object);
    }

    /* @inheritdoc */
    public function beforeRead(xPDOObject $object) {
        #$id = $object->get('id');
        #$criteries = false;
        #$this->object->set('products', $this->getProducts($id, $criteries));
        #$this->object->set('categories_children', $this->getCategories($object->get('id')));
     
        return parent::beforeRead($object);
    }
}