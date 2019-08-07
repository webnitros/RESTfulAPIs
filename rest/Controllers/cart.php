<?php

class MyControllerCart extends ApiInterface
{
    public $classKey = 'msProduct';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'DESC';
    public $filtersFields = array('id');
    public $defaultLimit = 100;

    /* @inheritdoc */
    public function prepareListQueryBeforeCount(xPDOQuery $c)
    {
        $c->where(array(
            'class_key' => $this->classKey,
        ));
        if ($where = $this->accessParams()) {
            $c->where($where);
        }

        return parent::prepareListQueryBeforeCount($c);
    }


    /* @inheritdoc */
    protected function prepareListObject(xPDOObject $object)
    {
        $row = $object->toArray();
        $row = $this->miniShop2->handlerModifications($row, $object, true, true);
        return $row;
    }


    /* @inheritdoc */
    public function get()
    {
        $pk = $this->getProperty($this->primaryKeyField);
        if (empty($pk)) {
            return $this->getList();
        }
        return $this->read($pk);
    }

    /* @inheritdoc */
    public function post()
    {
        $params = $this->getProperties();

        $ms2_action = $params['ms2_action'];
        $ms2_action = 'order/submit';
        foreach ($params['cart'] as $i => $param) {
            $params['cart'][$i]['options']['iid'] = $param['options']['modification'];

            /*if($object = $this->modx->getObject('msopModification', $param['options']['modification'])){
                $price = $object->get('price');
                if (!empty($price)) {
                    $params['cart'][$i]['options']['price'] = $price;
                    $params['cart'][$i]['options']['cost'] = $price * $params['cart'][$i]['quantity'];
                }
            }*/

        }

        $phone = $params['phone'] = '8' . preg_replace("/[^0-9]/", '', $params['phone']);
        if (strlen($params['phone']) != 11) {
            $params['phone'] = '';
        }

        if (!empty($phone)) {
            $str = substr($phone,1,1);
            if ($str != '9') {
                return $this->failure('Номер телефона указан не верно. Номер должен начинатся с цифры 9', array('phone'), 200);
            }
        }

        #return $this->failure('вфвы', array('phone' => 'Не правильно заполнен номер телефона'), 200);


        $params['metro'] = $params['email'];


        $this->miniShop2->cart->clean();

        foreach ($params['cart'] as $item) {
            $response = $this->miniShop2->cart->add($item['id'], $item['quantity'], $item['options']);
        }
        $this->miniShop2->order->clean();

        unset($params['cart']);
        foreach ($params as $key => $item) {
            $this->miniShop2->order->add($key, $item);
        }

        #$today = date('Y-m-d H:i:s', time());

        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Отправка заказа' .$today . ' инфа', print_r($params, 1));
        $response = $this->miniShop2->handleRequest($ms2_action, @$params);
        if ($response['success']) {

            return $this->success($response['message'], $response['data']);
        }
        return $this->failure($response['message'], $response['data'], 200);
    }

}