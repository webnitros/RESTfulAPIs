<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.07.2017
 * Time: 14:37
 */
class MyControllerMenu extends ApiInterface {
    public $classKey = 'msCategory';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'DESC';
    public $defaultLimit = 7;
    public $fields = 'id,name,submenu,uri';
    public $fieldsAlias = array(
        'pagetitle' => 'name'
    );
    public $addFields = array(
        'submenu' => array()
    );

    /**
     * Get tree menu parent
     * @param int $parentid
     * @param int $limit
     * @return array
     */
    protected function getParent($parentid, $limit = 100) {
        $rows = array();
        $q = $this->modx->newQuery('modResource');
        $q->select('id,pagetitle as name,uri');
        $q->sortby('menuindex', "DESC");
        $q->where(array(
            'isfolder' => true,
            'parent' => $parentid,
            'deleted:!=' => true,
        ));
        $q->limit($limit);
        if ($q->prepare() && $q->stmt->execute()){
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $row['id'];
                $row['id'] = (int)$id;
                $row['uri'] = '/'. $row['uri'];
                $row['submenu'] = $this->getParent($id);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /* @inheritdoc */
    protected function prepareListObject(xPDOObject $object) {
        $data = parent::prepareListObject($object);
        $data['submenu'] = $this->getParent($data['id']);
        $data['isauth'] = $this->modx->user->isAuthenticated();
        return $data;
    }

    /* @inheritdoc */
    public function prepareListQueryAfterCount(xPDOQuery $c)
    {
        $c->where(array(
            'parent' => 17,
            'deleted:!=' => true,
        ));
        return $c;
    }

}