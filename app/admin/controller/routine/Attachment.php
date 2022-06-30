<?php

namespace app\admin\controller\routine;

use app\common\controller\Backend;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends Backend
{
    protected $model = null;

    protected $quickSearchField = 'name';

    protected $withJoinTable = ['admin', 'user'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new AttachmentModel();
    }
}