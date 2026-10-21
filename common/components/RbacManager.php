<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.06.2019
 * Time: 10:40
 */

namespace common\components;

use yii\base\InvalidArgumentException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Role;
use yii\rbac\Rule;

/**
 * Class RbacManager
 * @package common\components
 */
class RbacManager extends DbManager
{
    public $companyId = 0;
    private $_checkAccessAssignments = [];


    public function add($object, $company_id = 0, $parent_id = 0)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->addItem($object, $company_id, $parent_id);
        } elseif ($object instanceof Rule) {
            return $this->addRule($object);
        }

        throw new InvalidArgumentException('Adding unsupported object type.');
    }

    /**
     * @param $item
     * @param int $company_id
     * @param int $parent_id
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function addItem($item, $company_id = 0, $parent_id = 0)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
                'company_id' => $company_id,
                'parent_id' => $parent_id
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * @param mixed $userId
     * @return bool
     */
    protected function isEmptyUserId($userId)
    {
        return !isset($userId) || $userId === '';
    }

    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }
        $result = [
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'] ?: null,
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
        if ($row['type'] == Item::TYPE_PERMISSION) {
            $result['parent_id'] = ArrayHelper::getValue($row, 'parent_id', 0);
        }
        return new $class($result);
    }

    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $query = (new Query())->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }
}
