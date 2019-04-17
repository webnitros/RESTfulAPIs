<?php

class ApiInterface extends modRestController
{
    /* @var string $fields */
    public $fields = 'id,pagetitle';
    /* @var boolean $classKeyFilter */
    public $classKeyFilter = true;
    /* @var int $maxLimit */
    public $maxLimit = 100;
    /* @var array $fieldsAlias */
    public $fieldsAlias = array();
    /* @var array $addFields */
    public $addFields = array();
    /* @var array $headers */
    public $headers = array(
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Authorization',
        'Access-Control-Allow-Methods' => 'GET'
    );
    /* @var array $filtersFields */
    public $filtersFields = array();
    public $defaultLimit = 20;

    /** @var miniShop2 $miniShop2 */
    public $miniShop2;
    protected $protected = false;
    protected $addPageUrl = true;

    /** @var string $context */
    protected $context = 'web';

    /* @inheritdoc */
    public function initialize()
    {
        $this->getMinishop();

        $defaultLimit = $this->defaultLimit;
        if ($limit = $this->getProperty('limit')) {
            $defaultLimit = $limit;
        }

        if ($defaultLimit > $this->maxLimit) {
            $defaultLimit = $this->maxLimit;
        }
        $this->defaultLimit = $defaultLimit;

        if ($page = $this->getProperty('page')) {
            $start = false;


            if ($page > 2) {
                $page--;
                $offset = $page * $defaultLimit;
                $start = $offset;
            } else if ($page > 1) {
                $start = $defaultLimit;
            }
            if ($start > 0) {
                $this->setProperty('start', $start);
            }
        }
    }

    /**
     * Инициализация minishop
     */
    public function getMinishop()
    {
        $this->miniShop2 = $this->modx->getService('miniShop2');
        $this->miniShop2->initialize('web');
    }

    /**
     * Распечатка результатов
     */
    protected function printJson()
    {
        // Распечатка результатов
        $print = $this->getProperty('print');
        if ($print) {
            $data = json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            echo '<pre>';
            print_r($data);
            die;
        }
    }

    /**
     * Output a collection of objects as a list.
     *
     * @param array $list
     * @param int|boolean $total
     * @param int $status
     */
    public function collection($list = array(), $total = false, $status = null)
    {
        parent::collection($list, $total, $status);
        $this->printJson();
    }


    protected function process($success = true, $message = '', $object = array(), $status = 200)
    {
        parent::process($success, $message, $object, $status);
        $this->printJson();
    }


    /* @inheritdoc */
    public function setHeaders(array $headers = array(), $merge = false)
    {
        $this->isAutification();
        parent::setHeaders($headers, $merge);
    }

    /**
     * Авторизация пользователя для получения данных
     */
    protected function isAutification()
    {
        $apikey = $this->getProperty('apikey', 'dqdqwdqwdqwd');
        if (!empty($apikey)) {
            /* @var modUserProfile $profile */
            /* @var modUser $user */
            if ($profile = $this->modx->getObject('modUserProfile', array('website' => $apikey))) {
                $this->modx->user = $profile->getOne('User');
                #$user->addSessionContext($this->context);
            }
        }
    }

    /* @inheritdoc */
    public function verifyAuthentication()
    {
        if ($this->modx->user->id > 0) {
            return true;
        }
        return false;
    }

    /**
     * Вернет только разрешенные поля для вывода
     * @param array $array
     * @param null|xPDOObject $object
     * @return array
     */
    protected function allowedFields(array $array, $object = null)
    {
        if (is_null($object)) {
            if ($this->object instanceof xPDOObject) {
                $object = $this->object;
            }
        }

        $data = array();
        if ($object instanceof xPDOObject) {
            switch ($this->classKey) {
                case 'msProduct':
                    $array = $this->handlerModifications($array, $object, true);
                    break;
                default:
                    break;
            }
            $fields = explode(',', $this->fields);
            foreach ($array as $key => $val) {
                if (in_array($key, $fields)) {
                    $data[$key] = $val;
                }
            }
        }

        // Добавление ссылки на страницу
        if ($this->addPageUrl) {
            $data['page_url'] = $this->modx->makeUrl($object->get('id'), $this->context, '', 'full');
        }
        return $data;
    }

    /* @inheritdoc */
    protected function prepareListObject(xPDOObject $object)
    {
        if (count($this->fieldsAlias) > 0) {
            foreach ($this->fieldsAlias as $field => $alias) {
                $object->addFieldAlias($field, $alias);
            }
        }
        if (count($this->addFields) > 0) {
            foreach ($this->addFields as $field => $v) {
                $object->set($field, $v);
            }
        }
        $array = $object->toArray();
        return $this->allowedFields($array, $object);
    }

    /**
     * Проверка доступных параметров для выборки ресурсов
     * @return array|bool
     */
    public function accessParams()
    {
        $where = array();
        $operations = array('', 'IN', '>', '<', '<=', '>=', '!=');
        foreach ($this->filtersFields as $filtersField) {
            $operation = '';
            $tmp = explode(':', $filtersField);
            if (count($tmp) > 1) {
                $field = array_shift($tmp);
                $operation = array_pop($tmp);
            } else {
                $field = array_shift($tmp);
            }

            foreach ($operations as $oper) {
                if (!empty($oper)) {
                    $oper = ':' . $oper;
                }
                if ($value = $this->getProperty($field . $oper)) {
                    if (!empty($value)) {
                        if ($oper == ':IN' and !is_array($value)) {
                            $value = explode(',', $value);
                        }
                        $where[$field . $oper] = $value;

                    }
                }
            }
        }
        if (empty($where)) {
            return false;
        }
        return $where;
    }

    /* @inheritdoc */
    public function prepareListQueryBeforeCount(xPDOQuery $c)
    {
        if ($this->classKeyFilter) {
            $c->where(array(
                'class_key' => $this->classKey,
            ));
        }
        if ($where = $this->accessParams()) {
            $c->where($where);
        }
        return parent::prepareListQueryBeforeCount($c);
    }

    /**
     * @param array $row
     * @param msProduct|xPDOObject $product
     * @param boolean $modifications
     *
     * @return array
     */
    public function handlerModifications($row, $product, $modifications = true)
    {
        if ($modifications) {
            $product->fromArray($row, '', true, true);
            $tmp = $row['price'];
            $row['price'] = $product->getPrice($row);
            $row['weight'] = $product->getWeight($row);
            if ($row['price'] != $tmp) {
                $row['old_price'] = $tmp;
            }
        }
        $options = $this->modx->call('msProductData', 'loadOptions', array(&$this->modx, $row['id']));
        $row = array_merge($row, $options);
        return $row;
    }



    /**
     * Fires after reading the object. Override to provide custom functionality.
     *
     * @param array $objectArray A reference to the outputting array
     * @return boolean|string Either return true/false or a string message

    public function afterRead(array &$objectArray) {
        $afterRead = parent::afterRead($objectArray);
        if ($afterRead !== true && $afterRead !== null) {
            return $this->failure($afterRead === false ? $this->errorMessage : $afterRead);
        }
        // Обработчик для файлов
        $objectArray = $this->allowedFields($objectArray);
        return $this->success('',$objectArray);
    }*/
}