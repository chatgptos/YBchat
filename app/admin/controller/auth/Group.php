<?php

namespace app\admin\controller\auth;

use app\admin\model\MenuRule;
use ba\Tree;
use app\common\controller\Backend;
use app\admin\model\AdminGroup;
use Exception;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Db;

class Group extends Backend
{
    /**
     * @var AdminGroup
     */
    protected $model = null;

    protected $preExcludeFields = ['createtime', 'updatetime'];

    protected $quickSearchField = 'name';

    /**
     * @var Tree
     */
    protected $tree = null;

    protected $keyword = false;

    /**
     * @var array 远程select初始化传值
     */
    protected $initValue;
    /**
     * @var bool 是否组装Tree
     */
    protected $assembleTree;

    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminGroup();
        $this->tree  = Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get("initValue/a", '');
        $this->keyword   = $this->request->request("quick_search");

        $this->assembleTree = $isTree && !$this->keyword && !$this->initValue;
    }

    public function index()
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getGroups(),
            'remark' => get_route_remark(),
        ]);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if (is_array($data['rules']) && $data['rules']) {
                $rules      = MenuRule::select();
                $superAdmin = true;
                foreach ($rules as $rule) {
                    if (!in_array($rule['id'], $data['rules'])) {
                        $superAdmin = false;
                    }
                }

                if ($superAdmin) {
                    $data['rules'] = '*';
                    unset($data['half_rules']);
                } else {
                    $data['rules']      = implode(',', $data['rules']);
                    $data['half_rules'] = implode(',', $data['half_rules']);
                }
            } else {
                unset($data['rules']);
                unset($data['half_rules']);
            }

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        $validate->scene('add')->check($data);
                    }
                }
                $result = $this->model->save($data);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    public function edit($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if (is_array($data['rules']) && $data['rules']) {
                $rules      = MenuRule::select();
                $superAdmin = true;
                foreach ($rules as $rule) {
                    if (!in_array($rule['id'], $data['rules'])) {
                        $superAdmin = false;
                    }
                }

                if ($superAdmin) {
                    $data['rules']      = '*';
                    $data['half_rules'] = '';
                } else {
                    $data['rules']      = implode(',', $data['rules']);
                    $data['half_rules'] = implode(',', $data['half_rules']);
                }
            } else {
                unset($data['rules']);
                unset($data['half_rules']);
            }

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        $validate->scene('edit')->check($data);
                    }
                }
                $result = $row->save($data);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $rules           = $row->rules ? explode(',', $row->rules) : [];
        $row->half_rules = $row->half_rules ? explode(',', $row->half_rules) : [];
        foreach ($row->half_rules as $half_rule) {
            $ruKey = array_search($half_rule, $rules);
            if ($ruKey) {
                unset($rules[$ruKey]);
            }
        }
        $row->rules = $rules;
        $this->success('', [
            'row' => $row
        ]);
    }

    public function select()
    {
        $data = $this->getGroups();

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'name'));
        }
        $this->success('', [
            'options' => $data
        ]);
    }

    public function getGroups()
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get("initKey/s", $pk);

        $where = [];
        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        $data = $this->model->where($where)->select();
        // 获取第一个权限的名称
        foreach ($data as $datum) {
            if ($datum->rules) {
                if ($datum->rules == '*') {
                    $datum->rules = __('Super administrator');
                } else {
                    $rules = explode(',', $datum->rules);
                    if ($rules) {
                        $rulesFirstTitle = MenuRule::where('id', $rules[0])->value('title');
                        $datum->rules    = count($rules) == 1 ? $rulesFirstTitle : $rulesFirstTitle . '等 ' . count($rules) . ' 项';
                    }
                }
            } else {
                $datum->rules = __('No permission');
            }
        }
        return $this->assembleTree ? $this->tree->assembleChild($data) : $data;
    }

}